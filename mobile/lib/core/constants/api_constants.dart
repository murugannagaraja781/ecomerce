import 'package:flutter/foundation.dart';

class ApiConstants {
  static String get baseUrl {
    if (kIsWeb) {
      return 'http://localhost/ecommerce_api/api';
    }
    // Android emulator alias for host machine
    if (defaultTargetPlatform == TargetPlatform.android) {
      return 'http://10.0.2.2/ecommerce_api/api';
    }
    return 'http://localhost/ecommerce_api/api';
  }

  // Auth
  static const String login = '/auth/login';
  static const String register = '/auth/register';
  static const String sendOtp = '/auth/send-otp';
  static const String verifyOtp = '/auth/verify-otp';
  static const String refreshToken = '/auth/refresh';
  static const String logout = '/auth/logout';
  static const String profile = '/auth/profile';

  // Catalog
  static const String homeFeed = '/home';
  static const String categories = '/categories';
  static const String brands = '/brands';
  static const String banners = '/banners';
  static const String products = '/products';

  // Search
  static const String search = '/search';
  static const String suggestions = '/search/suggestions';
  static const String popular = '/search/popular';

  // Cart & Wishlist
  static const String cart = '/cart';
  static const String cartItems = '/cart/items';
  static const String wishlist = '/wishlist';

  // Addresses
  static const String addresses = '/addresses';

  // Checkout & Payment
  static const String applyCoupon = '/coupons/apply';
  static const String paymentCreate = '/payments/create';
  static const String paymentVerify = '/payments/verify';

  // Orders
  static const String orders = '/orders';
  static const String reviews = '/reviews';

  // Seller
  static const String sellerLogin = '/seller/login';
  static const String sellerDashboard = '/seller/dashboard';
  static const String sellerProducts = '/seller/products';
  static const String sellerInventory = '/seller/inventory';
  static const String sellerOrders = '/seller/orders';

  // Admin
  static const String adminLogin = '/admin/login';
  static const String adminDashboard = '/admin/dashboard';
  static const String adminUsers = '/admin/users';
  static const String adminSellers = '/admin/sellers';
  static const String adminCategories = '/admin/categories';
  static const String adminOrders = '/admin/orders';
  static const String adminPayments = '/admin/payments';
  static const String adminCoupons = '/admin/coupons';
  static const String adminReports = '/admin/reports';
}
