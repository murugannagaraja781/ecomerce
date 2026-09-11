import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../../core/constants/app_colors.dart';
import '../../core/responsive/responsive_layout.dart';
import '../../core/utils/currency_formatter.dart';
import '../../models/cart_model.dart';
import '../../providers/cart_provider.dart';

class CartScreen extends ConsumerStatefulWidget {
  const CartScreen({super.key});

  @override
  ConsumerState<CartScreen> createState() => _CartScreenState();
}

class _CartScreenState extends ConsumerState<CartScreen> {
  @override
  void initState() {
    super.initState();
    Future.microtask(() => ref.read(cartProvider.notifier).loadCart());
  }

  @override
  Widget build(BuildContext context) {
    final cartState = ref.watch(cartProvider);
    final isDesktop = ResponsiveLayout.isDesktop(context);

    if (cartState.isLoading && cartState.items.isEmpty) {
      return Scaffold(
        appBar: AppBar(title: const Text('My Cart')),
        body: const Center(child: CircularProgressIndicator(color: AppColors.primary)),
      );
    }

    if (cartState.items.isEmpty) {
      return Scaffold(
        appBar: AppBar(title: const Text('My Cart')),
        body: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Icon(Icons.shopping_cart_outlined, size: 80, color: Colors.grey),
              const SizedBox(height: 16),
              const Text('Your Cart is empty!', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
              const SizedBox(height: 6),
              const Text('Explore our latest products and add them to your cart.', style: TextStyle(color: Colors.grey)),
              const SizedBox(height: 20),
              ElevatedButton(
                onPressed: () => context.go('/'),
                child: const Text('Shop Now'),
              ),
            ],
          ),
        ),
      );
    }

    return Scaffold(
      appBar: AppBar(
        title: Text('My Cart (${cartState.summary.totalItems})'),
      ),
      body: SingleChildScrollView(
        child: Center(
          child: ConstrainedBox(
            constraints: BoxConstraints(maxWidth: isDesktop ? 900 : double.infinity),
            child: Column(
              children: [
                // Delivery to Pincode Strip
                Container(
                  color: Colors.white,
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                  child: Row(
                    children: const [
                      Icon(Icons.location_on, color: AppColors.primary, size: 18),
                      SizedBox(width: 8),
                      Text('Deliver to Bengaluru - 560034', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold)),
                      Spacer(),
                      Text('Change', style: TextStyle(color: AppColors.primary, fontSize: 13, fontWeight: FontWeight.bold)),
                    ],
                  ),
                ),
                const SizedBox(height: 8),

                // Cart Items List
                ListView.separated(
                  shrinkWrap: true,
                  physics: const NeverScrollableScrollPhysics(),
                  itemCount: cartState.items.length,
                  separatorBuilder: (_, __) => const SizedBox(height: 8),
                  itemBuilder: (context, index) {
                    final item = cartState.items[index];
                    return _buildCartItemCard(item);
                  },
                ),
                const SizedBox(height: 8),

                // Price Details Card
                _buildPriceSummary(cartState.summary),
                const SizedBox(height: 80), // For bottom bar
              ],
            ),
          ),
        ),
      ),
      bottomNavigationBar: _buildBottomCheckoutBar(cartState.summary),
    );
  }

  Widget _buildCartItemCard(CartItemModel item) {
    return Container(
      color: Colors.white,
      padding: const EdgeInsets.all(14),
      child: Column(
        children: [
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Product Image
              ClipRRect(
                borderRadius: BorderRadius.circular(4),
                child: item.imageUrl != null
                    ? CachedNetworkImage(
                        imageUrl: item.imageUrl!,
                        width: 75,
                        height: 75,
                        fit: BoxFit.contain,
                      )
                    : const Icon(Icons.image, size: 75, color: Colors.grey),
              ),
              const SizedBox(width: 12),
              // Details
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      item.productTitle,
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w500, height: 1.25),
                    ),
                    if (item.variantTitle != null) ...[
                      const SizedBox(height: 4),
                      Text(item.variantTitle!, style: const TextStyle(fontSize: 12, color: AppColors.textSecondary)),
                    ],
                    if (item.sellerName != null) ...[
                      const SizedBox(height: 2),
                      Text('Seller: ${item.sellerName}', style: const TextStyle(fontSize: 11, color: AppColors.textMuted)),
                    ],
                    const SizedBox(height: 8),
                    // Pricing
                    Row(
                      children: [
                        Text(
                          CurrencyFormatter.format(item.totalPrice),
                          style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                        ),
                        const SizedBox(width: 8),
                        if (item.totalMrp > item.totalPrice)
                          Text(
                            CurrencyFormatter.format(item.totalMrp),
                            style: const TextStyle(fontSize: 12, color: AppColors.textMuted, decoration: TextDecoration.lineThrough),
                          ),
                      ],
                    ),
                  ],
                ),
              ),
            ],
          ),
          const Divider(height: 16),
          // Quantity and Action Buttons
          Row(
            children: [
              // Quantity Stepper
              Container(
                decoration: BoxDecoration(
                  border: Border.all(color: AppColors.border),
                  borderRadius: BorderRadius.circular(4),
                ),
                child: Row(
                  children: [
                    IconButton(
                      icon: const Icon(Icons.remove, size: 16),
                      onPressed: () {
                        if (item.quantity > 1) {
                          ref.read(cartProvider.notifier).updateQuantity(item.itemId, item.quantity - 1);
                        } else {
                          ref.read(cartProvider.notifier).removeItem(item.itemId);
                        }
                      },
                      padding: const EdgeInsets.all(4),
                      constraints: const BoxConstraints(),
                    ),
                    Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 10),
                      child: Text('${item.quantity}', style: const TextStyle(fontWeight: FontWeight.bold)),
                    ),
                    IconButton(
                      icon: const Icon(Icons.add, size: 16),
                      onPressed: () {
                        ref.read(cartProvider.notifier).updateQuantity(item.itemId, item.quantity + 1);
                      },
                      padding: const EdgeInsets.all(4),
                      constraints: const BoxConstraints(),
                    ),
                  ],
                ),
              ),
              const Spacer(),
              TextButton.icon(
                onPressed: () => ref.read(cartProvider.notifier).removeItem(item.itemId),
                icon: const Icon(Icons.delete_outline, size: 16, color: AppColors.textSecondary),
                label: const Text('REMOVE', style: TextStyle(color: AppColors.textSecondary, fontSize: 12)),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildPriceSummary(CartSummaryModel summary) {
    return Container(
      color: Colors.white,
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text('PRICE DETAILS', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: AppColors.textSecondary)),
          const Divider(height: 16),
          _summaryRow('Price (${summary.totalItems} items)', CurrencyFormatter.format(summary.totalMrp)),
          _summaryRow('Discount', '- ${CurrencyFormatter.format(summary.totalDiscount)}', textColor: AppColors.successGreen),
          _summaryRow('Delivery Charges', summary.deliveryCharge == 0 ? 'FREE' : CurrencyFormatter.format(summary.deliveryCharge),
              textColor: summary.deliveryCharge == 0 ? AppColors.successGreen : AppColors.textPrimary),
          const Divider(height: 20),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text('Total Amount', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
              Text(CurrencyFormatter.format(summary.totalPayable), style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
            ],
          ),
          const SizedBox(height: 10),
          Container(
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: const Color(0xFFE8F5E9),
              borderRadius: BorderRadius.circular(4),
            ),
            child: Row(
              children: [
                const Icon(Icons.savings, color: AppColors.successGreen, size: 16),
                const SizedBox(width: 8),
                Text(
                  'You will save ${CurrencyFormatter.format(summary.savings)} on this order',
                  style: const TextStyle(color: AppColors.successGreen, fontSize: 12, fontWeight: FontWeight.bold),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _summaryRow(String label, String value, {Color? textColor}) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: const TextStyle(fontSize: 13, color: AppColors.textPrimary)),
          Text(value, style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: textColor ?? AppColors.textPrimary)),
        ],
      ),
    );
  }

  Widget _buildBottomCheckoutBar(CartSummaryModel summary) {
    return Container(
      height: 65,
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
      decoration: BoxDecoration(
        color: Colors.white,
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.08),
            blurRadius: 4,
            offset: const Offset(0, -2),
          ),
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
                CurrencyFormatter.format(summary.totalPayable),
                style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
              ),
              const Text('View price details', style: TextStyle(fontSize: 11, color: AppColors.primary)),
            ],
          ),
          ElevatedButton(
            onPressed: summary.totalItems == 0 ? null : () => context.push('/checkout'),
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.accentOrange,
              padding: const EdgeInsets.symmetric(horizontal: 32, vertical: 12),
            ),
            child: const Text('PLACE ORDER', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
          ),
        ],
      ),
    );
  }
}
