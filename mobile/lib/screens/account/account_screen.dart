import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../core/constants/app_colors.dart';
import '../../providers/auth_provider.dart';

class AccountScreen extends ConsumerWidget {
  const AccountScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final authState = ref.watch(authStateProvider);

    return Scaffold(
      appBar: AppBar(title: const Text('My Account')),
      body: SingleChildScrollView(
        child: Column(
          children: [
            // User Header
            Container(
              color: Colors.white,
              padding: const EdgeInsets.all(20),
              child: Row(
                children: [
                  CircleAvatar(
                    radius: 30,
                    backgroundColor: AppColors.primary.withOpacity(0.1),
                    child: Text(
                      authState.isAuthenticated
                          ? authState.user!.name.substring(0, 1).toUpperCase()
                          : '?',
                      style: const TextStyle(fontSize: 24, fontWeight: FontWeight.bold, color: AppColors.primary),
                    ),
                  ),
                  const SizedBox(width: 16),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          authState.isAuthenticated ? authState.user!.name : 'Welcome to Flipkart',
                          style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          authState.isAuthenticated ? authState.user!.email : 'Sign in for the best experience',
                          style: const TextStyle(fontSize: 12, color: AppColors.textSecondary),
                        ),
                      ],
                    ),
                  ),
                  if (!authState.isAuthenticated)
                    ElevatedButton(
                      onPressed: () => context.push('/login'),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppColors.primary,
                        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                      ),
                      child: const Text('Login'),
                    ),
                ],
              ),
            ),
            const SizedBox(height: 10),

            // Account Action Menu
            Container(
              color: Colors.white,
              child: Column(
                children: [
                  _menuItem(
                    icon: Icons.inventory_2_outlined,
                    title: 'My Orders',
                    subtitle: 'Track, return or buy again',
                    onTap: () => context.push('/orders'),
                  ),
                  _menuItem(
                    icon: Icons.favorite_border,
                    title: 'My Wishlist',
                    subtitle: 'Your saved products',
                    onTap: () => context.push('/wishlist'),
                  ),
                  _menuItem(
                    icon: Icons.local_offer_outlined,
                    title: 'Coupons & Offers',
                    subtitle: 'Special discounts for you',
                    onTap: () {},
                  ),
                  _menuItem(
                    icon: Icons.location_on_outlined,
                    title: 'Saved Addresses',
                    subtitle: 'Manage delivery addresses',
                    onTap: () {},
                  ),
                ],
              ),
            ),
            const SizedBox(height: 10),

            // Portal Switches: Seller & Admin
            Container(
              color: Colors.white,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Padding(
                    padding: EdgeInsets.fromLTRB(16, 16, 16, 8),
                    child: Text('PORTALS & MANAGEMENT', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: AppColors.textSecondary)),
                  ),
                  _menuItem(
                    icon: Icons.storefront_outlined,
                    title: 'Seller Dashboard',
                    subtitle: 'Manage products, inventory & merchant orders',
                    onTap: () => context.push('/seller'),
                    trailingColor: AppColors.primary,
                  ),
                  _menuItem(
                    icon: Icons.admin_panel_settings_outlined,
                    title: 'Admin Control Center',
                    subtitle: 'System metrics, catalog, users & reports',
                    onTap: () => context.push('/admin'),
                    trailingColor: AppColors.primary,
                  ),
                ],
              ),
            ),
            const SizedBox(height: 10),

            // Logout Option
            if (authState.isAuthenticated)
              Container(
                color: Colors.white,
                child: ListTile(
                  leading: const Icon(Icons.logout, color: AppColors.error),
                  title: const Text('Logout', style: TextStyle(color: AppColors.error, fontWeight: FontWeight.bold)),
                  onTap: () async {
                    await ref.read(authStateProvider.notifier).logout();
                    if (context.mounted) {
                      ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(content: Text('Logged out successfully')),
                      );
                    }
                  },
                ),
              ),
            const SizedBox(height: 30),
          ],
        ),
      ),
    );
  }

  Widget _menuItem({
    required IconData icon,
    required String title,
    required String subtitle,
    required VoidCallback onTap,
    Color? trailingColor,
  }) {
    return ListTile(
      leading: Icon(icon, color: AppColors.textPrimary),
      title: Text(title, style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 14)),
      subtitle: Text(subtitle, style: const TextStyle(fontSize: 12, color: AppColors.textSecondary)),
      trailing: Icon(Icons.chevron_right, color: trailingColor ?? Colors.grey.shade400),
      onTap: onTap,
    );
  }
}
