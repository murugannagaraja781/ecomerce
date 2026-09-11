import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../screens/main_navigation_shell.dart';
import '../screens/home/home_screen.dart';
import '../screens/home/search_screen.dart';
import '../screens/categories/categories_screen.dart';
import '../screens/cart/cart_screen.dart';
import '../screens/orders/orders_screen.dart';
import '../screens/orders/order_detail_screen.dart';
import '../screens/account/account_screen.dart';
import '../screens/products/product_list_screen.dart';
import '../screens/product_detail/product_detail_screen.dart';
import '../screens/checkout/checkout_screen.dart';
import '../screens/wishlist/wishlist_screen.dart';
import '../screens/auth/login_screen.dart';
import '../screens/auth/register_screen.dart';
import '../screens/seller/seller_dashboard_screen.dart';
import '../screens/admin/admin_dashboard_screen.dart';

final GlobalKey<NavigatorState> _rootNavigatorKey = GlobalKey<NavigatorState>();

final appRouter = GoRouter(
  navigatorKey: _rootNavigatorKey,
  initialLocation: '/',
  routes: [
    // Shell Route for 5 Bottom Navigation Branches
    StatefulShellRoute.indexedStack(
      builder: (context, state, navigationShell) {
        return MainNavigationShell(navigationShell: navigationShell);
      },
      branches: [
        // 1. Home Branch
        StatefulShellBranch(
          routes: [
            GoRoute(
              path: '/',
              builder: (context, state) => const HomeScreen(),
            ),
          ],
        ),
        // 2. Categories Branch
        StatefulShellBranch(
          routes: [
            GoRoute(
              path: '/categories',
              builder: (context, state) => const CategoriesScreen(),
            ),
          ],
        ),
        // 3. Cart Branch
        StatefulShellBranch(
          routes: [
            GoRoute(
              path: '/cart',
              builder: (context, state) => const CartScreen(),
            ),
          ],
        ),
        // 4. Orders Branch
        StatefulShellBranch(
          routes: [
            GoRoute(
              path: '/orders',
              builder: (context, state) => const OrdersScreen(),
            ),
          ],
        ),
        // 5. Account Branch
        StatefulShellBranch(
          routes: [
            GoRoute(
              path: '/account',
              builder: (context, state) => const AccountScreen(),
            ),
          ],
        ),
      ],
    ),

    // Push routes (outside bottom bar)
    GoRoute(
      path: '/search',
      parentNavigatorKey: _rootNavigatorKey,
      builder: (context, state) => const SearchScreen(),
    ),
    GoRoute(
      path: '/products',
      parentNavigatorKey: _rootNavigatorKey,
      builder: (context, state) {
        final catId = int.tryParse(state.uri.queryParameters['category_id'] ?? '');
        final catTitle = state.uri.queryParameters['title'];
        final q = state.uri.queryParameters['q'];
        return ProductListScreen(
          categoryId: catId,
          categoryTitle: catTitle,
          searchQuery: q,
        );
      },
    ),
    GoRoute(
      path: '/products/:id',
      parentNavigatorKey: _rootNavigatorKey,
      builder: (context, state) {
        final id = state.pathParameters['id']!;
        return ProductDetailScreen(productId: id);
      },
    ),
    GoRoute(
      path: '/orders/:id',
      parentNavigatorKey: _rootNavigatorKey,
      builder: (context, state) {
        final id = state.pathParameters['id']!;
        return OrderDetailScreen(orderId: id);
      },
    ),
    GoRoute(
      path: '/wishlist',
      parentNavigatorKey: _rootNavigatorKey,
      builder: (context, state) => const WishlistScreen(),
    ),
    GoRoute(
      path: '/checkout',
      parentNavigatorKey: _rootNavigatorKey,
      builder: (context, state) => const CheckoutScreen(),
    ),
    GoRoute(
      path: '/login',
      parentNavigatorKey: _rootNavigatorKey,
      builder: (context, state) => const LoginScreen(),
    ),
    GoRoute(
      path: '/register',
      parentNavigatorKey: _rootNavigatorKey,
      builder: (context, state) => const RegisterScreen(),
    ),
    GoRoute(
      path: '/seller',
      parentNavigatorKey: _rootNavigatorKey,
      builder: (context, state) => const SellerDashboardScreen(),
    ),
    GoRoute(
      path: '/admin',
      parentNavigatorKey: _rootNavigatorKey,
      builder: (context, state) => const AdminDashboardScreen(),
    ),
  ],
);
