import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../core/constants/app_colors.dart';
import '../core/responsive/responsive_layout.dart';
import '../providers/cart_provider.dart';

class SearchAppBar extends ConsumerWidget implements PreferredSizeWidget {
  final bool showBackButton;
  final String? initialQuery;

  const SearchAppBar({
    super.key,
    this.showBackButton = false,
    this.initialQuery,
  });

  @override
  Size get preferredSize => const Size.fromHeight(115);

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final cartState = ref.watch(cartProvider);
    final isDesktop = ResponsiveLayout.isDesktop(context);

    return Container(
      color: AppColors.primary,
      padding: EdgeInsets.only(
        top: MediaQuery.of(context).padding.top + 4,
        left: isDesktop ? 48 : 12,
        right: isDesktop ? 48 : 12,
        bottom: 6,
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          // Top Row: Brand, Location, Cart, Account
          Row(
            children: [
              if (showBackButton)
                IconButton(
                  icon: const Icon(Icons.arrow_back, color: Colors.white),
                  onPressed: () => context.pop(),
                  padding: EdgeInsets.zero,
                  constraints: const BoxConstraints(),
                ),
              if (showBackButton) const SizedBox(width: 8),
              // Brand Logo / Title
              GestureDetector(
                onTap: () => context.go('/'),
                child: Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                      decoration: BoxDecoration(
                        color: AppColors.secondary,
                        borderRadius: BorderRadius.circular(4),
                      ),
                      child: const Text(
                        'FK',
                        style: TextStyle(
                          color: AppColors.primary,
                          fontWeight: FontWeight.w900,
                          fontSize: 16,
                          fontStyle: FontStyle.italic,
                        ),
                      ),
                    ),
                    const SizedBox(width: 6),
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'Flipkart',
                          style: TextStyle(
                            color: Colors.white,
                            fontSize: 17,
                            fontWeight: FontWeight.bold,
                            letterSpacing: 0.2,
                          ),
                        ),
                        Row(
                          children: const [
                            Text(
                              'Explore ',
                              style: TextStyle(
                                color: Colors.white70,
                                fontSize: 9,
                                fontStyle: FontStyle.italic,
                              ),
                            ),
                            Text(
                              'Plus✦',
                              style: TextStyle(
                                color: AppColors.secondary,
                                fontSize: 9,
                                fontWeight: FontWeight.bold,
                                fontStyle: FontStyle.italic,
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ],
                ),
              ),
              const Spacer(),
              // Location Pill (Deliver to Bengaluru 560034)
              if (isDesktop)
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: Colors.white.withOpacity(0.15),
                    borderRadius: BorderRadius.circular(16),
                  ),
                  child: Row(
                    children: const [
                      Icon(Icons.location_on, color: AppColors.secondary, size: 14),
                      SizedBox(width: 4),
                      Text(
                        'Deliver to 560034',
                        style: TextStyle(color: Colors.white, fontSize: 11),
                      ),
                    ],
                  ),
                ),
              if (isDesktop) const SizedBox(width: 16),
              // Cart Button with live Badge
              Stack(
                clipBehavior: Clip.none,
                children: [
                  IconButton(
                    icon: const Icon(Icons.shopping_cart_outlined, color: Colors.white, size: 24),
                    onPressed: () => context.push('/cart'),
                  ),
                  if (cartState.badgeCount > 0)
                    Positioned(
                      top: 4,
                      right: 4,
                      child: Container(
                        padding: const EdgeInsets.all(4),
                        decoration: const BoxDecoration(
                          color: AppColors.accentOrange,
                          shape: BoxShape.circle,
                        ),
                        constraints: const BoxConstraints(minWidth: 16, minHeight: 16),
                        child: Center(
                          child: Text(
                            '${cartState.badgeCount}',
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 10,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ),
                      ),
                    ),
                ],
              ),
            ],
          ),
          const SizedBox(height: 6),
          // Search Input Box
          GestureDetector(
            onTap: () {
              if (ModalRoute.of(context)?.settings.name != '/search') {
                context.push('/search');
              }
            },
            child: Container(
              height: 40,
              padding: const EdgeInsets.symmetric(horizontal: 12),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(4),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withOpacity(0.05),
                    blurRadius: 4,
                    offset: const Offset(0, 2),
                  ),
                ],
              ),
              child: Row(
                children: [
                  const Icon(Icons.search, color: AppColors.textSecondary, size: 20),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      initialQuery ?? 'Search for Products, Brands and More',
                      style: TextStyle(
                        color: initialQuery != null ? AppColors.textPrimary : AppColors.textMuted,
                        fontSize: 13,
                      ),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                  const Icon(Icons.mic_none, color: AppColors.textSecondary, size: 18),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}
