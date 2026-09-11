import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../core/constants/app_colors.dart';
import '../../core/utils/currency_formatter.dart';
import '../../repositories/seller_admin_repository.dart';

class SellerDashboardScreen extends ConsumerStatefulWidget {
  const SellerDashboardScreen({super.key});

  @override
  ConsumerState<SellerDashboardScreen> createState() => _SellerDashboardScreenState();
}

class _SellerDashboardScreenState extends ConsumerState<SellerDashboardScreen> with SingleTickerProviderStateMixin {
  final SellerAdminRepository _sellerRepo = SellerAdminRepository();
  late TabController _tabController;

  Map<String, dynamic>? _dashboardData;
  List<dynamic> _products = [];
  List<dynamic> _inventory = [];
  List<dynamic> _orders = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 4, vsync: this);
    _loadAllSellerData();
  }

  Future<void> _loadAllSellerData() async {
    setState(() => _isLoading = true);
    try {
      final dash = await _sellerRepo.getSellerDashboard();
      final prods = await _sellerRepo.getSellerProducts();
      final inv = await _sellerRepo.getSellerInventory();
      final ords = await _sellerRepo.getSellerOrders();

      setState(() {
        _dashboardData = dash;
        _products = prods;
        _inventory = inv;
        _orders = ords;
        _isLoading = false;
      });
    } catch (_) {
      setState(() => _isLoading = false);
    }
  }

  void _showAddProductDialog() {
    final titleController = TextEditingController();
    final mrpController = TextEditingController(text: '999');
    final priceController = TextEditingController(text: '499');
    final stockController = TextEditingController(text: '50');
    final imgController = TextEditingController(text: 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=600');

    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Add New Product to Store'),
        content: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              TextField(controller: titleController, decoration: const InputDecoration(labelText: 'Product Title *')),
              const SizedBox(height: 8),
              Row(
                children: [
                  Expanded(child: TextField(controller: mrpController, keyboardType: TextInputType.number, decoration: const InputDecoration(labelText: 'MRP (₹) *'))),
                  const SizedBox(width: 8),
                  Expanded(child: TextField(controller: priceController, keyboardType: TextInputType.number, decoration: const InputDecoration(labelText: 'Selling Price (₹) *'))),
                ],
              ),
              const SizedBox(height: 8),
              TextField(controller: stockController, keyboardType: TextInputType.number, decoration: const InputDecoration(labelText: 'Initial Stock *')),
              const SizedBox(height: 8),
              TextField(controller: imgController, decoration: const InputDecoration(labelText: 'Image URL')),
            ],
          ),
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context), child: const Text('Cancel')),
          ElevatedButton(
            onPressed: () async {
              if (titleController.text.isEmpty) return;
              Navigator.pop(context);
              try {
                await _sellerRepo.createSellerProduct({
                  'title': titleController.text,
                  'category_id': 15, // Mobile cases & covers
                  'brand_id': 1, // Celvas
                  'base_mrp': double.tryParse(mrpController.text) ?? 999,
                  'base_price': double.tryParse(priceController.text) ?? 499,
                  'initial_stock': int.tryParse(stockController.text) ?? 50,
                  'image_url': imgController.text,
                });
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(content: Text('Product published to catalog!'), backgroundColor: AppColors.successGreen),
                );
                _loadAllSellerData();
              } catch (e) {
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(content: Text('Failed: $e'), backgroundColor: AppColors.error),
                );
              }
            },
            child: const Text('Publish Product'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return Scaffold(
        appBar: AppBar(title: const Text('Seller Hub')),
        body: const Center(child: CircularProgressIndicator(color: AppColors.primary)),
      );
    }

    final kpis = _dashboardData?['kpis'] ?? {};
    final storeName = _dashboardData?['seller']?['store_name'] ?? 'Seller Store';

    return Scaffold(
      appBar: AppBar(
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(storeName, style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
            const Text('Seller Central & Merchant Operations', style: TextStyle(fontSize: 11, color: Colors.white70)),
          ],
        ),
        actions: [
          IconButton(icon: const Icon(Icons.refresh), onPressed: _loadAllSellerData),
          IconButton(icon: const Icon(Icons.home_outlined), onPressed: () => context.go('/')),
        ],
        bottom: TabBar(
          controller: _tabController,
          indicatorColor: AppColors.secondary,
          labelColor: Colors.white,
          unselectedLabelColor: Colors.white70,
          tabs: const [
            Tab(text: 'Overview'),
            Tab(text: 'Products'),
            Tab(text: 'Inventory'),
            Tab(text: 'Orders'),
          ],
        ),
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: _showAddProductDialog,
        backgroundColor: AppColors.primary,
        icon: const Icon(Icons.add, color: Colors.white),
        label: const Text('Add Product', style: TextStyle(color: Colors.white)),
      ),
      body: TabBarView(
        controller: _tabController,
        children: [
          // 1. Overview Tab
          _buildOverviewTab(kpis),

          // 2. Products Tab
          _buildProductsTab(),

          // 3. Inventory Tab
          _buildInventoryTab(),

          // 4. Orders Tab
          _buildOrdersTab(),
        ],
      ),
    );
  }

  Widget _buildOverviewTab(Map<String, dynamic> kpis) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text('PERFORMANCE METRICS', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: AppColors.textSecondary)),
          const SizedBox(height: 12),
          GridView.count(
            crossAxisCount: 2,
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            crossAxisSpacing: 10,
            mainAxisSpacing: 10,
            childAspectRatio: 1.6,
            children: [
              _kpiCard('Gross Revenue', CurrencyFormatter.format(kpis['total_revenue'] ?? 0), Icons.currency_rupee, Colors.blue),
              _kpiCard('Total Orders', '${kpis['total_orders'] ?? 0}', Icons.shopping_bag_outlined, Colors.green),
              _kpiCard('Pending Orders', '${kpis['pending_orders'] ?? 0}', Icons.pending_actions, Colors.orange),
              _kpiCard('Low Stock Alerts', '${kpis['low_stock'] ?? 0}', Icons.warning_amber, Colors.red),
            ],
          ),
          const SizedBox(height: 20),
          const Text('RECENT ORDERS REQUIRING ACTION', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: AppColors.textSecondary)),
          const SizedBox(height: 10),
          ..._orders.take(3).map((o) => Card(
                color: Colors.white,
                margin: const EdgeInsets.only(bottom: 8),
                child: ListTile(
                  title: Text(o['product_title'] ?? '', maxLines: 1, overflow: TextOverflow.ellipsis, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                  subtitle: Text('Qty: ${o['quantity']} • ${CurrencyFormatter.format(o['total_price'])} • Status: ${o['status']}', style: const TextStyle(fontSize: 12)),
                  trailing: ElevatedButton(
                    onPressed: () => _updateOrderStatusDialog(o['order_id']),
                    style: ElevatedButton.styleFrom(backgroundColor: AppColors.primary, padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4)),
                    child: const Text('Update', style: TextStyle(fontSize: 11)),
                  ),
                ),
              )),
        ],
      ),
    );
  }

  Widget _kpiCard(String label, String value, IconData icon, Color color) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: AppColors.border),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Row(
            children: [
              Icon(icon, size: 18, color: color),
              const SizedBox(width: 6),
              Text(label, style: const TextStyle(fontSize: 11, color: AppColors.textSecondary)),
            ],
          ),
          const SizedBox(height: 6),
          Text(value, style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: AppColors.textPrimary)),
        ],
      ),
    );
  }

  Widget _buildProductsTab() {
    return ListView.separated(
      padding: const EdgeInsets.all(12),
      itemCount: _products.length,
      separatorBuilder: (_, __) => const SizedBox(height: 8),
      itemBuilder: (context, index) {
        final p = _products[index];
        return Card(
          color: Colors.white,
          child: ListTile(
            title: Text(p['title'] ?? '', maxLines: 1, overflow: TextOverflow.ellipsis, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
            subtitle: Text('Price: ${CurrencyFormatter.format(p['base_price'])}  •  Stock: ${p['total_stock'] ?? 50}', style: const TextStyle(fontSize: 12)),
            trailing: Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
              decoration: BoxDecoration(color: AppColors.successGreen.withOpacity(0.1), borderRadius: BorderRadius.circular(4)),
              child: const Text('ACTIVE', style: TextStyle(color: AppColors.successGreen, fontSize: 10, fontWeight: FontWeight.bold)),
            ),
          ),
        );
      },
    );
  }

  Widget _buildInventoryTab() {
    return ListView.separated(
      padding: const EdgeInsets.all(12),
      itemCount: _inventory.length,
      separatorBuilder: (_, __) => const SizedBox(height: 8),
      itemBuilder: (context, index) {
        final inv = _inventory[index];
        final qty = int.tryParse(inv['quantity']?.toString() ?? '0') ?? 0;
        return Card(
          color: Colors.white,
          child: ListTile(
            title: Text(inv['product_title'] ?? '', maxLines: 1, overflow: TextOverflow.ellipsis, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
            subtitle: Text('SKU: ${inv['sku'] ?? 'GENERIC'}  •  Current Quantity: $qty', style: const TextStyle(fontSize: 12)),
            trailing: IconButton(
              icon: const Icon(Icons.edit, color: AppColors.primary),
              onPressed: () => _updateStockDialog(inv['id'], qty),
            ),
          ),
        );
      },
    );
  }

  void _updateStockDialog(int id, int currentQty) {
    final c = TextEditingController(text: '$currentQty');
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Adjust Stock Quantity'),
        content: TextField(controller: c, keyboardType: TextInputType.number, decoration: const InputDecoration(labelText: 'New Stock Level')),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context), child: const Text('Cancel')),
          ElevatedButton(
            onPressed: () async {
              Navigator.pop(context);
              final newStock = int.tryParse(c.text) ?? currentQty;
              await _sellerRepo.updateStock(id, newStock);
              _loadAllSellerData();
            },
            child: const Text('Save'),
          ),
        ],
      ),
    );
  }

  Widget _buildOrdersTab() {
    return ListView.separated(
      padding: const EdgeInsets.all(12),
      itemCount: _orders.length,
      separatorBuilder: (_, __) => const SizedBox(height: 8),
      itemBuilder: (context, index) {
        final o = _orders[index];
        return Card(
          color: Colors.white,
          child: ListTile(
            title: Text('Order #${o['order_number']}', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
            subtitle: Text('${o['product_title']}\nStatus: ${o['status']} • Customer: ${o['customer_name']}', style: const TextStyle(fontSize: 12)),
            trailing: ElevatedButton(
              onPressed: () => _updateOrderStatusDialog(o['order_id']),
              style: ElevatedButton.styleFrom(backgroundColor: AppColors.primary, padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6)),
              child: const Text('Status', style: TextStyle(fontSize: 11)),
            ),
          ),
        );
      },
    );
  }

  void _updateOrderStatusDialog(dynamic orderId) {
    showDialog(
      context: context,
      builder: (context) => SimpleDialog(
        title: const Text('Select Next Status'),
        children: ['CONFIRMED', 'PACKED', 'SHIPPED', 'DELIVERED'].map((st) {
          return SimpleDialogOption(
            onPressed: () async {
              Navigator.pop(context);
              await _sellerRepo.updateSellerOrderStatus(int.parse(orderId.toString()), st);
              _loadAllSellerData();
            },
            child: Text(st, style: const TextStyle(fontWeight: FontWeight.bold)),
          );
        }).toList(),
      ),
    );
  }
}
