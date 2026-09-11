import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../../core/constants/app_colors.dart';
import '../../core/utils/currency_formatter.dart';
import '../../models/order_model.dart';
import '../../repositories/checkout_repository.dart';

class OrdersScreen extends ConsumerStatefulWidget {
  const OrdersScreen({super.key});

  @override
  ConsumerState<OrdersScreen> createState() => _OrdersScreenState();
}

class _OrdersScreenState extends ConsumerState<OrdersScreen> {
  final CheckoutRepository _checkoutRepo = CheckoutRepository();
  List<OrderModel> _orders = [];
  bool _isLoading = true;
  String _selectedTab = 'ALL';

  @override
  void initState() {
    super.initState();
    _fetchOrders();
  }

  Future<void> _fetchOrders() async {
    setState(() => _isLoading = true);
    try {
      final list = await _checkoutRepo.getOrders(status: _selectedTab == 'ALL' ? null : _selectedTab);
      setState(() {
        _orders = list;
        _isLoading = false;
      });
    } catch (_) {
      setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('My Orders')),
      body: Column(
        children: [
          // Filter Tabs
          Container(
            color: Colors.white,
            height: 48,
            child: ListView(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.symmetric(horizontal: 12),
              children: [
                _tabChip('All Orders', 'ALL'),
                _tabChip('Confirmed', 'CONFIRMED'),
                _tabChip('Packed', 'PACKED'),
                _tabChip('Shipped', 'SHIPPED'),
                _tabChip('Delivered', 'DELIVERED'),
                _tabChip('Cancelled', 'CANCELLED'),
              ],
            ),
          ),
          const Divider(height: 1, color: AppColors.divider),
          // Orders List
          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator(color: AppColors.primary))
                : _orders.isEmpty
                    ? Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            const Icon(Icons.inventory_2_outlined, size: 64, color: Colors.grey),
                            const SizedBox(height: 12),
                            const Text('No orders found in this tab', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
                            const SizedBox(height: 8),
                            ElevatedButton(
                              onPressed: () => context.go('/'),
                              child: const Text('Start Shopping'),
                            ),
                          ],
                        ),
                      )
                    : ListView.separated(
                        padding: const EdgeInsets.all(8),
                        itemCount: _orders.length,
                        separatorBuilder: (_, __) => const SizedBox(height: 8),
                        itemBuilder: (context, index) {
                          final o = _orders[index];
                          return _buildOrderCard(o);
                        },
                      ),
          ),
        ],
      ),
    );
  }

  Widget _tabChip(String label, String tab) {
    final isSelected = _selectedTab == tab;
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 8),
      child: ChoiceChip(
        label: Text(label, style: TextStyle(fontSize: 12, color: isSelected ? Colors.white : AppColors.textPrimary)),
        selected: isSelected,
        selectedColor: AppColors.primary,
        onSelected: (val) {
          if (val) {
            setState(() => _selectedTab = tab);
            _fetchOrders();
          }
        },
      ),
    );
  }

  Widget _buildOrderCard(OrderModel order) {
    Color statusColor;
    switch (order.status) {
      case 'DELIVERED':
        statusColor = AppColors.successGreen;
        break;
      case 'CANCELLED':
        statusColor = AppColors.error;
        break;
      case 'SHIPPED':
      case 'OUT_FOR_DELIVERY':
        statusColor = AppColors.primary;
        break;
      default:
        statusColor = AppColors.warning;
    }

    return Card(
      color: Colors.white,
      child: InkWell(
        onTap: () => context.push('/orders/${order.id}'),
        child: Padding(
          padding: const EdgeInsets.all(14),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Order Number & Date
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text('Order: ${order.orderNumber}', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                  Text(
                    order.status.replaceAll('_', ' '),
                    style: TextStyle(color: statusColor, fontWeight: FontWeight.bold, fontSize: 12),
                  ),
                ],
              ),
              const Divider(height: 16),
              // Item Preview
              Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  ClipRRect(
                    borderRadius: BorderRadius.circular(4),
                    child: order.sampleProductImage != null
                        ? CachedNetworkImage(
                            imageUrl: order.sampleProductImage!,
                            width: 60,
                            height: 60,
                            fit: BoxFit.cover,
                          )
                        : Container(
                            width: 60,
                            height: 60,
                            color: Colors.grey.shade200,
                            child: const Icon(Icons.image, color: Colors.grey),
                          ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          order.sampleProductTitle ?? 'Product Item',
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                          style: const TextStyle(fontWeight: FontWeight.w500, fontSize: 13),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          'Total: ${CurrencyFormatter.format(order.totalPayable)} (${order.paymentMethod})',
                          style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold),
                        ),
                        if (order.expectedDeliveryDate != null) ...[
                          const SizedBox(height: 4),
                          Text(
                            'Delivery expected by ${order.expectedDeliveryDate}',
                            style: const TextStyle(fontSize: 11, color: AppColors.successGreen),
                          ),
                        ],
                      ],
                    ),
                  ),
                  const Icon(Icons.chevron_right, color: AppColors.textSecondary),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }
}
