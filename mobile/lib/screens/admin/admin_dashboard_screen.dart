import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../core/constants/app_colors.dart';
import '../../core/utils/currency_formatter.dart';
import '../../repositories/seller_admin_repository.dart';

class AdminDashboardScreen extends ConsumerStatefulWidget {
  const AdminDashboardScreen({super.key});

  @override
  ConsumerState<AdminDashboardScreen> createState() => _AdminDashboardScreenState();
}

class _AdminDashboardScreenState extends ConsumerState<AdminDashboardScreen> with SingleTickerProviderStateMixin {
  final SellerAdminRepository _adminRepo = SellerAdminRepository();
  late TabController _tabController;

  Map<String, dynamic>? _dashboard;
  List<dynamic> _users = [];
  List<dynamic> _sellers = [];
  List<dynamic> _orders = [];
  List<dynamic> _coupons = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 5, vsync: this);
    _loadAdminData();
  }

  Future<void> _loadAdminData() async {
    setState(() => _isLoading = true);
    try {
      final dash = await _adminRepo.getAdminDashboard();
      final users = await _adminRepo.getAdminUsers();
      final sellers = await _adminRepo.getAdminSellers();
      final ords = await _adminRepo.getAdminOrders();
      final coups = await _adminRepo.getAdminCoupons();

      setState(() {
        _dashboard = dash;
        _users = users;
        _sellers = sellers;
        _orders = ords;
        _coupons = coups;
        _isLoading = false;
      });
    } catch (_) {
      setState(() => _isLoading = false);
    }
  }

  void _showCreateCouponDialog() {
    final codeController = TextEditingController();
    final discController = TextEditingController(text: '100');
    final minController = TextEditingController(text: '499');

    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Create Discount Coupon'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            TextField(controller: codeController, textCapitalization: TextCapitalization.characters, decoration: const InputDecoration(labelText: 'Coupon Code (e.g. FLASH200)')),
            const SizedBox(height: 8),
            TextField(controller: discController, keyboardType: TextInputType.number, decoration: const InputDecoration(labelText: 'Discount Amount (₹)')),
            const SizedBox(height: 8),
            TextField(controller: minController, keyboardType: TextInputType.number, decoration: const InputDecoration(labelText: 'Minimum Order (₹)')),
          ],
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context), child: const Text('Cancel')),
          ElevatedButton(
            onPressed: () async {
              if (codeController.text.isEmpty) return;
              Navigator.pop(context);
              try {
                await _adminRepo.createAdminCoupon({
                  'code': codeController.text.toUpperCase(),
                  'discount_type': 'FIXED',
                  'discount_value': double.tryParse(discController.text) ?? 100,
                  'min_order_amount': double.tryParse(minController.text) ?? 499,
                  'start_date': DateTime.now().toIso8601String(),
                  'expiry_date': DateTime.now().add(const Duration(days: 365)).toIso8601String(),
                });
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(content: Text('Coupon published!'), backgroundColor: AppColors.successGreen),
                );
                _loadAdminData();
              } catch (e) {
                ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Failed: $e'), backgroundColor: AppColors.error));
              }
            },
            child: const Text('Create'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return Scaffold(
        appBar: AppBar(title: const Text('Admin Operations')),
        body: const Center(child: CircularProgressIndicator(color: AppColors.primary)),
      );
    }

    final cards = _dashboard?['cards'] ?? {};

    return Scaffold(
      appBar: AppBar(
        title: const Text('Flipkart Admin Control Center'),
        actions: [
          IconButton(icon: const Icon(Icons.refresh), onPressed: _loadAdminData),
          IconButton(icon: const Icon(Icons.home_outlined), onPressed: () => context.go('/')),
        ],
        bottom: TabBar(
          controller: _tabController,
          indicatorColor: AppColors.secondary,
          isScrollable: true,
          labelColor: Colors.white,
          unselectedLabelColor: Colors.white70,
          tabs: const [
            Tab(text: 'Overview'),
            Tab(text: 'Users'),
            Tab(text: 'Sellers'),
            Tab(text: 'Orders'),
            Tab(text: 'Coupons'),
          ],
        ),
      ),
      body: TabBarView(
        controller: _tabController,
        children: [
          // 1. Overview
          _buildOverviewTab(cards),

          // 2. Users Management
          _buildUsersTab(),

          // 3. Sellers Management
          _buildSellersTab(),

          // 4. Orders Management
          _buildOrdersTab(),

          // 5. Coupons Management
          _buildCouponsTab(),
        ],
      ),
    );
  }

  Widget _buildOverviewTab(Map<String, dynamic> cards) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text('PLATFORM PERFORMANCE', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: AppColors.textSecondary)),
          const SizedBox(height: 12),
          GridView.count(
            crossAxisCount: 2,
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            crossAxisSpacing: 10,
            mainAxisSpacing: 10,
            childAspectRatio: 1.6,
            children: [
              _metricCard('Total Platform Revenue', CurrencyFormatter.format(cards['total_revenue'] ?? 0), Icons.payments_outlined, Colors.blue),
              _metricCard('Total Platform Orders', '${cards['total_orders'] ?? 0}', Icons.shopping_bag_outlined, Colors.green),
              _metricCard('Registered Users', '${cards['total_users'] ?? 0}', Icons.people_outline, Colors.deepPurple),
              _metricCard('Verified Sellers', '${cards['total_sellers'] ?? 0}', Icons.store_outlined, Colors.teal),
              _metricCard('Active Catalog Items', '${cards['total_products'] ?? 0}', Icons.inventory_2_outlined, Colors.indigo),
              _metricCard('Low Stock Alerts', '${cards['low_stock_count'] ?? 0}', Icons.warning_amber, Colors.red),
            ],
          ),
          const SizedBox(height: 20),
          const Text('RECENT ORDERS OVERVIEW', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: AppColors.textSecondary)),
          const SizedBox(height: 8),
          ..._orders.take(4).map((o) => Card(
                color: Colors.white,
                margin: const EdgeInsets.only(bottom: 8),
                child: ListTile(
                  title: Text('Order #${o['order_number']}', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                  subtitle: Text('Customer: ${o['customer_name']} • Total: ${CurrencyFormatter.format(o['total_payable'])}', style: const TextStyle(fontSize: 12)),
                  trailing: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                    decoration: BoxDecoration(color: AppColors.primary.withOpacity(0.1), borderRadius: BorderRadius.circular(4)),
                    child: Text(o['status'] ?? '', style: const TextStyle(color: AppColors.primary, fontWeight: FontWeight.bold, fontSize: 11)),
                  ),
                ),
              )),
        ],
      ),
    );
  }

  Widget _metricCard(String label, String value, IconData icon, Color color) {
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
              Expanded(child: Text(label, style: const TextStyle(fontSize: 11, color: AppColors.textSecondary), maxLines: 1, overflow: TextOverflow.ellipsis)),
            ],
          ),
          const SizedBox(height: 6),
          Text(value, style: const TextStyle(fontSize: 17, fontWeight: FontWeight.bold, color: AppColors.textPrimary)),
        ],
      ),
    );
  }

  Widget _buildUsersTab() {
    return ListView.separated(
      padding: const EdgeInsets.all(12),
      itemCount: _users.length,
      separatorBuilder: (_, __) => const SizedBox(height: 8),
      itemBuilder: (context, index) {
        final u = _users[index];
        final isActive = u['is_active'] == 1;
        return Card(
          color: Colors.white,
          child: ListTile(
            title: Text(u['name'] ?? '', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
            subtitle: Text('${u['email']}  •  Role: ${u['role_name']}', style: const TextStyle(fontSize: 12)),
            trailing: Switch(
              value: isActive,
              activeColor: AppColors.successGreen,
              onChanged: (val) async {
                await _adminRepo.updateUserStatus(u['id'], val);
                _loadAdminData();
              },
            ),
          ),
        );
      },
    );
  }

  Widget _buildSellersTab() {
    return ListView.separated(
      padding: const EdgeInsets.all(12),
      itemCount: _sellers.length,
      separatorBuilder: (_, __) => const SizedBox(height: 8),
      itemBuilder: (context, index) {
        final s = _sellers[index];
        return Card(
          color: Colors.white,
          child: ListTile(
            title: Text(s['store_name'] ?? '', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
            subtitle: Text('Owner: ${s['owner_name']} • Status: ${s['status']}', style: const TextStyle(fontSize: 12)),
            trailing: PopupMenuButton<String>(
              onSelected: (status) async {
                await _adminRepo.updateSellerVerification(s['id'], status);
                _loadAdminData();
              },
              itemBuilder: (context) => [
                const PopupMenuItem(value: 'APPROVED', child: Text('Approve Seller')),
                const PopupMenuItem(value: 'SUSPENDED', child: Text('Suspend Seller')),
                const PopupMenuItem(value: 'REJECTED', child: Text('Reject Seller')),
              ],
            ),
          ),
        );
      },
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
            subtitle: Text('Amount: ${CurrencyFormatter.format(o['total_payable'])} • Customer: ${o['customer_name']}', style: const TextStyle(fontSize: 12)),
            trailing: Text(o['status'] ?? '', style: const TextStyle(color: AppColors.primary, fontWeight: FontWeight.bold, fontSize: 12)),
          ),
        );
      },
    );
  }

  Widget _buildCouponsTab() {
    return Scaffold(
      floatingActionButton: FloatingActionButton.extended(
        onPressed: _showCreateCouponDialog,
        backgroundColor: AppColors.primary,
        icon: const Icon(Icons.add, color: Colors.white),
        label: const Text('Add Coupon', style: TextStyle(color: Colors.white)),
      ),
      body: ListView.separated(
        padding: const EdgeInsets.all(12),
        itemCount: _coupons.length,
        separatorBuilder: (_, __) => const SizedBox(height: 8),
        itemBuilder: (context, index) {
          final c = _coupons[index];
          return Card(
            color: Colors.white,
            child: ListTile(
              leading: const Icon(Icons.local_offer, color: AppColors.successGreen),
              title: Text(c['code'] ?? '', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
              subtitle: Text('${c['description']}\nDiscount: ₹${c['discount_value']} • Min Order: ₹${c['min_order_amount']}', style: const TextStyle(fontSize: 12)),
            ),
          );
        },
      ),
    );
  }
}
