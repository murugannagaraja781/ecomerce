import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../core/constants/app_colors.dart';
import '../../core/utils/currency_formatter.dart';
import '../../models/address_model.dart';
import '../../models/cart_model.dart';
import '../../providers/cart_provider.dart';
import '../../repositories/checkout_repository.dart';

class CheckoutScreen extends ConsumerStatefulWidget {
  const CheckoutScreen({super.key});

  @override
  ConsumerState<CheckoutScreen> createState() => _CheckoutScreenState();
}

class _CheckoutScreenState extends ConsumerState<CheckoutScreen> {
  final CheckoutRepository _checkoutRepo = CheckoutRepository();
  final TextEditingController _couponController = TextEditingController();

  List<AddressModel> _addresses = [];
  AddressModel? _selectedAddress;
  bool _isLoading = true;
  bool _isProcessingPayment = false;

  String? _appliedCouponCode;
  double _couponDiscount = 0.0;
  String _selectedPaymentMethod = 'RAZORPAY';

  @override
  void initState() {
    super.initState();
    _loadAddresses();
  }

  Future<void> _loadAddresses() async {
    try {
      final list = await _checkoutRepo.getAddresses();
      setState(() {
        _addresses = list;
        if (list.isNotEmpty) {
          _selectedAddress = list.firstWhere((a) => a.isDefault, orElse: () => list.first);
        }
        _isLoading = false;
      });
    } catch (_) {
      setState(() => _isLoading = false);
    }
  }

  Future<void> _applyCoupon(double cartSubtotal) async {
    final code = _couponController.text.trim();
    if (code.isEmpty) return;

    try {
      final res = await _checkoutRepo.applyCoupon(code, cartSubtotal);
      setState(() {
        _appliedCouponCode = res['code'];
        _couponDiscount = (res['discount_amount'] as num).toDouble();
      });
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Coupon "$code" applied! Saved ${CurrencyFormatter.format(_couponDiscount)}'),
            backgroundColor: AppColors.successGreen,
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Invalid coupon: $e'), backgroundColor: AppColors.error),
        );
      }
    }
  }

  Future<void> _processPaymentAndPlaceOrder(CartSummaryModel summary) async {
    if (_selectedAddress == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please select or add a delivery address')),
      );
      return;
    }

    setState(() => _isProcessingPayment = true);

    try {
      // 1. Create Razorpay Payment Order on Backend
      final payData = await _checkoutRepo.createPaymentOrder(
        _selectedAddress!.id,
        _appliedCouponCode,
      );

      final rzpOrderId = payData['razorpay_order_id'];

      // Simulate Razorpay Gateway authorization
      await Future.delayed(const Duration(milliseconds: 1200));

      // 2. Verify Payment Signature & Place Order with Atomic MySQL Transaction
      final orderResult = await _checkoutRepo.verifyPaymentAndPlaceOrder(
        addressId: _selectedAddress!.id,
        razorpayOrderId: rzpOrderId,
        razorpayPaymentId: 'pay_sim_${DateTime.now().millisecondsSinceEpoch}',
        razorpaySignature: 'sig_verified_demo_checkout_success',
        couponCode: _appliedCouponCode,
        paymentMethod: _selectedPaymentMethod,
      );

      // Refresh cart
      ref.read(cartProvider.notifier).loadCart();
      setState(() => _isProcessingPayment = false);

      if (mounted) {
        _showOrderSuccessDialog(orderResult);
      }
    } catch (e) {
      setState(() => _isProcessingPayment = false);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Checkout failed: $e'), backgroundColor: AppColors.error),
        );
      }
    }
  }

  void _showOrderSuccessDialog(Map<String, dynamic> order) {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) {
        return AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Icon(Icons.check_circle, color: AppColors.successGreen, size: 72),
              const SizedBox(height: 16),
              const Text('Order Placed Successfully!', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
              const SizedBox(height: 8),
              Text(
                'Order ID: ${order['order_number']}',
                style: const TextStyle(fontWeight: FontWeight.w600, color: AppColors.textSecondary),
              ),
              const SizedBox(height: 6),
              Text(
                'Estimated Delivery: ${order['expected_delivery_date']}',
                style: const TextStyle(fontSize: 13, color: AppColors.successGreen, fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 20),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  onPressed: () {
                    Navigator.pop(context);
                    context.go('/orders');
                  },
                  child: const Text('TRACK MY ORDER'),
                ),
              ),
              const SizedBox(height: 8),
              TextButton(
                onPressed: () {
                  Navigator.pop(context);
                  context.go('/');
                },
                child: const Text('Continue Shopping'),
              ),
            ],
          ),
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final cartState = ref.watch(cartProvider);
    final summary = cartState.summary;
    final finalPayable = (summary.totalPayable - _couponDiscount).clamp(0.0, double.infinity);

    if (_isLoading) {
      return Scaffold(
        appBar: AppBar(title: const Text('Order Summary')),
        body: const Center(child: CircularProgressIndicator(color: AppColors.primary)),
      );
    }

    return Scaffold(
      appBar: AppBar(title: const Text('Order Summary')),
      body: SingleChildScrollView(
        child: Center(
          child: ConstrainedBox(
            constraints: const BoxConstraints(maxWidth: 800),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // 1. Delivery Address Card
                Container(
                  color: Colors.white,
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          const Text('DELIVERY ADDRESS', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: AppColors.textSecondary)),
                          TextButton(
                            onPressed: () {
                              showModalBottomSheet(
                                context: context,
                                builder: (ctx) => ListView(
                                  padding: const EdgeInsets.all(16),
                                  children: _addresses.map((a) => ListTile(
                                    title: Text(a.fullName, style: const TextStyle(fontWeight: FontWeight.bold)),
                                    subtitle: Text(a.formattedAddress),
                                    onTap: () {
                                      setState(() => _selectedAddress = a);
                                      Navigator.pop(ctx);
                                    },
                                  )).toList(),
                                ),
                              );
                            },
                            child: const Text('Change', style: TextStyle(color: AppColors.primary, fontWeight: FontWeight.bold)),
                          ),
                        ],
                      ),
                      if (_selectedAddress != null) ...[
                        Row(
                          children: [
                            Text(_selectedAddress!.fullName, style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold)),
                            const SizedBox(width: 8),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                              decoration: BoxDecoration(color: Colors.grey.shade200, borderRadius: BorderRadius.circular(4)),
                              child: Text(_selectedAddress!.addressType, style: const TextStyle(fontSize: 10, fontWeight: FontWeight.bold)),
                            ),
                          ],
                        ),
                        const SizedBox(height: 6),
                        Text(_selectedAddress!.formattedAddress, style: const TextStyle(fontSize: 13, height: 1.3, color: AppColors.textPrimary)),
                        const SizedBox(height: 6),
                        Text('Mobile: ${_selectedAddress!.phone}', style: const TextStyle(fontSize: 13, color: AppColors.textSecondary)),
                      ] else ...[
                        const Text('No address found. Please add a delivery address.', style: TextStyle(color: Colors.grey)),
                      ],
                    ],
                  ),
                ),
                const SizedBox(height: 8),

                // 2. Order Items Overview
                Container(
                  color: Colors.white,
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text('ITEMS IN ORDER (${cartState.items.length})', style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: AppColors.textSecondary)),
                      const Divider(height: 16),
                      ...cartState.items.map((item) => Padding(
                            padding: const EdgeInsets.symmetric(vertical: 6),
                            child: Row(
                              children: [
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text(item.productTitle, maxLines: 1, overflow: TextOverflow.ellipsis, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w500)),
                                      Text('Qty: ${item.quantity}  •  ${CurrencyFormatter.format(item.totalPrice)}', style: const TextStyle(fontSize: 12, color: AppColors.textSecondary)),
                                    ],
                                  ),
                                ),
                              ],
                            ),
                          )),
                    ],
                  ),
                ),
                const SizedBox(height: 8),

                // 3. Coupons Section
                Container(
                  color: Colors.white,
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text('APPLY COUPON', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: AppColors.textSecondary)),
                      const SizedBox(height: 10),
                      Row(
                        children: [
                          Expanded(
                            child: TextField(
                              controller: _couponController,
                              textCapitalization: TextCapitalization.characters,
                              decoration: const InputDecoration(
                                hintText: 'Enter code (e.g. WELCOME100)',
                                isDense: true,
                              ),
                            ),
                          ),
                          const SizedBox(width: 8),
                          ElevatedButton(
                            onPressed: () => _applyCoupon(summary.subtotal),
                            style: ElevatedButton.styleFrom(
                              backgroundColor: AppColors.primary,
                              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                            ),
                            child: const Text('Apply'),
                          ),
                        ],
                      ),
                      if (_appliedCouponCode != null) ...[
                        const SizedBox(height: 8),
                        Row(
                          children: [
                            const Icon(Icons.check_circle, color: AppColors.successGreen, size: 16),
                            const SizedBox(width: 6),
                            Text('Applied: $_appliedCouponCode (- ${CurrencyFormatter.format(_couponDiscount)})',
                                style: const TextStyle(color: AppColors.successGreen, fontSize: 13, fontWeight: FontWeight.bold)),
                          ],
                        ),
                      ],
                    ],
                  ),
                ),
                const SizedBox(height: 8),

                // 4. Payment Method
                Container(
                  color: Colors.white,
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text('PAYMENT OPTIONS', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: AppColors.textSecondary)),
                      const SizedBox(height: 8),
                      RadioListTile<String>(
                        value: 'RAZORPAY',
                        groupValue: _selectedPaymentMethod,
                        onChanged: (val) => setState(() => _selectedPaymentMethod = val!),
                        title: Row(
                          children: const [
                            Icon(Icons.payment, color: AppColors.primary),
                            SizedBox(width: 8),
                            Text('Razorpay (UPI, Credit/Debit Card, NetBanking)', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
                          ],
                        ),
                      ),
                      RadioListTile<String>(
                        value: 'COD',
                        groupValue: _selectedPaymentMethod,
                        onChanged: (val) => setState(() => _selectedPaymentMethod = val!),
                        title: const Text('Cash on Delivery (COD)', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 8),

                // 5. Final Bill Summary
                Container(
                  color: Colors.white,
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text('PRICE DETAILS', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: AppColors.textSecondary)),
                      const Divider(height: 16),
                      _row('Items Total', CurrencyFormatter.format(summary.totalMrp)),
                      _row('Discount', '- ${CurrencyFormatter.format(summary.totalDiscount)}', color: AppColors.successGreen),
                      if (_couponDiscount > 0)
                        _row('Coupon Discount', '- ${CurrencyFormatter.format(_couponDiscount)}', color: AppColors.successGreen),
                      _row('Delivery Fee', summary.deliveryCharge == 0 ? 'FREE' : CurrencyFormatter.format(summary.deliveryCharge)),
                      const Divider(height: 16),
                      _row('Total Payable', CurrencyFormatter.format(finalPayable), isBold: true),
                    ],
                  ),
                ),
                const SizedBox(height: 80),
              ],
            ),
          ),
        ),
      ),
      bottomNavigationBar: Container(
        height: 65,
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
        decoration: BoxDecoration(
          color: Colors.white,
          boxShadow: [
            BoxShadow(color: Colors.black.withOpacity(0.08), blurRadius: 4, offset: const Offset(0, -2)),
          ],
        ),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Text(
                  CurrencyFormatter.format(finalPayable),
                  style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                ),
                const Text('Total Amount', style: TextStyle(fontSize: 11, color: AppColors.textSecondary)),
              ],
            ),
            ElevatedButton(
              onPressed: _isProcessingPayment ? null : () => _processPaymentAndPlaceOrder(summary),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.accentOrange,
                padding: const EdgeInsets.symmetric(horizontal: 32, vertical: 12),
              ),
              child: _isProcessingPayment
                  ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                  : const Text('PAY & PLACE ORDER', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
            ),
          ],
        ),
      ),
    );
  }

  Widget _row(String label, String value, {Color? color, bool isBold = false}) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: TextStyle(fontSize: 13, fontWeight: isBold ? FontWeight.bold : FontWeight.normal)),
          Text(value, style: TextStyle(fontSize: 13, fontWeight: isBold ? FontWeight.bold : FontWeight.w600, color: color ?? AppColors.textPrimary)),
        ],
      ),
    );
  }
}
