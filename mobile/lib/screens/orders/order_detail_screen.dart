import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../../core/constants/app_colors.dart';
import '../../core/utils/currency_formatter.dart';
import '../../models/order_model.dart';
import '../../repositories/checkout_repository.dart';

class OrderDetailScreen extends StatefulWidget {
  final dynamic orderId;

  const OrderDetailScreen({super.key, required this.orderId});

  @override
  State<OrderDetailScreen> createState() => _OrderDetailScreenState();
}

class _OrderDetailScreenState extends State<OrderDetailScreen> {
  final CheckoutRepository _checkoutRepo = CheckoutRepository();
  OrderModel? _order;
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _fetchDetail();
  }

  Future<void> _fetchDetail() async {
    try {
      final o = await _checkoutRepo.getOrderById(widget.orderId);
      setState(() {
        _order = o;
        _isLoading = false;
      });
    } catch (_) {
      setState(() => _isLoading = false);
    }
  }

  Future<void> _cancelOrder() async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Cancel Order?'),
        content: const Text('Are you sure you want to cancel this order? If paid online, your refund will be initiated immediately.'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('NO')),
          ElevatedButton(
            onPressed: () => Navigator.pop(context, true),
            style: ElevatedButton.styleFrom(backgroundColor: AppColors.error),
            child: const Text('YES, CANCEL'),
          ),
        ],
      ),
    );

    if (confirm == true) {
      try {
        await _checkoutRepo.cancelOrder(_order!.id, 'Customer requested cancellation');
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Order has been cancelled.'), backgroundColor: AppColors.successGreen),
        );
        _fetchDetail();
      } catch (e) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Cancellation failed: $e'), backgroundColor: AppColors.error),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return Scaffold(
        appBar: AppBar(title: const Text('Order Details')),
        body: const Center(child: CircularProgressIndicator(color: AppColors.primary)),
      );
    }

    if (_order == null) {
      return Scaffold(
        appBar: AppBar(title: const Text('Order Details')),
        body: const Center(child: Text('Order not found')),
      );
    }

    final order = _order!;

    return Scaffold(
      appBar: AppBar(title: Text('Order: ${order.orderNumber}')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(12),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // 1. Order Status & Summary Card
            Container(
              color: Colors.white,
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text('Order #${order.orderNumber}', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                        decoration: BoxDecoration(
                          color: order.status == 'CANCELLED' ? AppColors.error.withOpacity(0.1) : AppColors.successGreen.withOpacity(0.1),
                          borderRadius: BorderRadius.circular(4),
                        ),
                        child: Text(
                          order.status.replaceAll('_', ' '),
                          style: TextStyle(
                            color: order.status == 'CANCELLED' ? AppColors.error : AppColors.successGreen,
                            fontWeight: FontWeight.bold,
                            fontSize: 11,
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 6),
                  Text('Placed on: ${order.createdAt}', style: const TextStyle(color: AppColors.textSecondary, fontSize: 12)),
                  if (order.expectedDeliveryDate != null && order.status != 'CANCELLED') ...[
                    const SizedBox(height: 4),
                    Text(
                      'Expected Delivery: ${order.expectedDeliveryDate}',
                      style: const TextStyle(fontWeight: FontWeight.bold, color: AppColors.successGreen, fontSize: 13),
                    ),
                  ],
                ],
              ),
            ),
            const SizedBox(height: 10),

            // 2. 6-Milestone Visual Tracking Timeline
            Container(
              color: Colors.white,
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text('ORDER TRACKING TIMELINE', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12, color: AppColors.textSecondary)),
                  const Divider(height: 16),
                  ...order.milestones.asMap().entries.map((entry) {
                    final idx = entry.key;
                    final m = entry.value;
                    final isLast = idx == order.milestones.length - 1;
                    return Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Column(
                          children: [
                            Container(
                              width: 22,
                              height: 22,
                              decoration: BoxDecoration(
                                shape: BoxShape.circle,
                                color: m.completed ? AppColors.successGreen : Colors.grey.shade300,
                              ),
                              child: Center(
                                child: Icon(
                                  m.completed ? Icons.check : Icons.circle,
                                  size: 14,
                                  color: Colors.white,
                                ),
                              ),
                            ),
                            if (!isLast)
                              Container(
                                width: 2,
                                height: 36,
                                color: m.completed ? AppColors.successGreen : Colors.grey.shade300,
                              ),
                          ],
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                m.title,
                                style: TextStyle(
                                  fontSize: 13,
                                  fontWeight: m.completed ? FontWeight.bold : FontWeight.normal,
                                  color: m.completed ? AppColors.textPrimary : AppColors.textMuted,
                                ),
                              ),
                              if (m.date != null)
                                Text(m.date!, style: const TextStyle(fontSize: 11, color: AppColors.textSecondary)),
                              if (m.notes != null)
                                Text(m.notes!, style: const TextStyle(fontSize: 11, color: AppColors.textMuted, fontStyle: FontStyle.italic)),
                              const SizedBox(height: 16),
                            ],
                          ),
                        ),
                      ],
                    );
                  }),
                ],
              ),
            ),
            const SizedBox(height: 10),

            // 3. Ordered Items List
            Container(
              color: Colors.white,
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text('ITEMS IN THIS ORDER', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12, color: AppColors.textSecondary)),
                  const Divider(height: 16),
                  ...order.items.map((it) => Padding(
                        padding: const EdgeInsets.only(bottom: 12),
                        child: Row(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            ClipRRect(
                              borderRadius: BorderRadius.circular(4),
                              child: it.imageUrl != null
                                  ? CachedNetworkImage(
                                      imageUrl: it.imageUrl!,
                                      width: 60,
                                      height: 60,
                                      fit: BoxFit.contain,
                                    )
                                  : Container(width: 60, height: 60, color: Colors.grey.shade200),
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(it.productTitle, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w500)),
                                  if (it.variantTitle != null)
                                    Text(it.variantTitle!, style: const TextStyle(fontSize: 11, color: AppColors.textSecondary)),
                                  const SizedBox(height: 4),
                                  Text('${CurrencyFormatter.format(it.price)} x ${it.quantity} = ${CurrencyFormatter.format(it.totalPrice)}',
                                      style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
                                ],
                              ),
                            ),
                          ],
                        ),
                      )),
                ],
              ),
            ),
            const SizedBox(height: 10),

            // 4. Delivery Address Snapshot
            if (order.deliveryAddress != null)
              Container(
                color: Colors.white,
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text('SHIPPING ADDRESS', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12, color: AppColors.textSecondary)),
                    const Divider(height: 16),
                    Text(order.deliveryAddress!['full_name'] ?? '', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                    const SizedBox(height: 4),
                    Text(
                      '${order.deliveryAddress!['address_line1']}, ${order.deliveryAddress!['city']}, ${order.deliveryAddress!['state']} - ${order.deliveryAddress!['pincode']}',
                      style: const TextStyle(fontSize: 13, height: 1.3),
                    ),
                    const SizedBox(height: 4),
                    Text('Phone: ${order.deliveryAddress!['phone']}', style: const TextStyle(fontSize: 12, color: AppColors.textSecondary)),
                  ],
                ),
              ),
            const SizedBox(height: 10),

            // 5. Action Buttons (Cancel Order)
            if (!['DELIVERED', 'CANCELLED', 'SHIPPED'].contains(order.status))
              SizedBox(
                width: double.infinity,
                child: OutlinedButton(
                  onPressed: _cancelOrder,
                  style: OutlinedButton.styleFrom(
                    foregroundColor: AppColors.error,
                    side: const BorderSide(color: AppColors.error),
                    padding: const EdgeInsets.symmetric(vertical: 12),
                  ),
                  child: const Text('CANCEL ORDER', style: TextStyle(fontWeight: FontWeight.bold)),
                ),
              ),
            const SizedBox(height: 30),
          ],
        ),
      ),
    );
  }
}
