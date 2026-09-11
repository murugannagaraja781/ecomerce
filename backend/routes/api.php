<?php
/**
 * Master API Route Registrations
 * @var Router $router
 */

require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/CatalogController.php';
require_once __DIR__ . '/../controllers/SearchController.php';
require_once __DIR__ . '/../controllers/CartController.php';
require_once __DIR__ . '/../controllers/AddressController.php';
require_once __DIR__ . '/../controllers/CheckoutController.php';
require_once __DIR__ . '/../controllers/OrderController.php';
require_once __DIR__ . '/../controllers/SellerController.php';
require_once __DIR__ . '/../controllers/AdminController.php';

// ----------------------------------------------------
// 1. Authentication & Profile
// ----------------------------------------------------
$router->post('/api/auth/register', [AuthController::class, 'register']);
$router->post('/api/auth/login', [AuthController::class, 'login']);
$router->post('/api/auth/send-otp', [AuthController::class, 'sendOtp']);
$router->post('/api/auth/verify-otp', [AuthController::class, 'verifyOtp']);
$router->post('/api/auth/refresh', [AuthController::class, 'refreshToken']);
$router->post('/api/auth/logout', [AuthController::class, 'logout']);
$router->get('/api/auth/profile', [AuthController::class, 'getProfile']);
$router->put('/api/auth/profile', [AuthController::class, 'updateProfile']);

// ----------------------------------------------------
// 2. Catalog & Homepage Feeds
// ----------------------------------------------------
$router->get('/api/home', [CatalogController::class, 'getHomeFeed']);
$router->get('/api/categories', [CatalogController::class, 'getCategories']);
$router->get('/api/categories/{id}', [CatalogController::class, 'getCategoryById']);
$router->get('/api/brands', [CatalogController::class, 'getBrands']);
$router->get('/api/banners', [CatalogController::class, 'getBanners']);
$router->get('/api/products', [CatalogController::class, 'getProducts']);
$router->get('/api/products/{id}', [CatalogController::class, 'getProductById']);

// ----------------------------------------------------
// 3. Search & Auto-complete Suggestions
// ----------------------------------------------------
$router->get('/api/search', [SearchController::class, 'search']);
$router->get('/api/search/suggestions', [SearchController::class, 'suggestions']);
$router->get('/api/search/popular', [SearchController::class, 'popular']);

// ----------------------------------------------------
// 4. Cart, Wishlist & Saved For Later
// ----------------------------------------------------
$router->get('/api/cart', [CartController::class, 'getCart']);
$router->post('/api/cart/items', [CartController::class, 'addItem']);
$router->put('/api/cart/items/{id}', [CartController::class, 'updateItem']);
$router->delete('/api/cart/items/{id}', [CartController::class, 'deleteItem']);
$router->post('/api/cart/save-for-later/{id}', [CartController::class, 'saveForLater']);

$router->get('/api/wishlist', [CartController::class, 'getWishlist']);
$router->post('/api/wishlist', [CartController::class, 'addToWishlist']);
$router->delete('/api/wishlist/{id}', [CartController::class, 'removeFromWishlist']);

// ----------------------------------------------------
// 5. Customer Addresses
// ----------------------------------------------------
$router->get('/api/addresses', [AddressController::class, 'getAddresses']);
$router->post('/api/addresses', [AddressController::class, 'createAddress']);
$router->put('/api/addresses/{id}', [AddressController::class, 'updateAddress']);
$router->delete('/api/addresses/{id}', [AddressController::class, 'deleteAddress']);
$router->put('/api/addresses/{id}/default', [AddressController::class, 'setDefault']);

// ----------------------------------------------------
// 6. Checkout, Coupons & Razorpay Payments
// ----------------------------------------------------
$router->post('/api/coupons/apply', [CheckoutController::class, 'applyCoupon']);
$router->post('/api/payments/create', [CheckoutController::class, 'createRazorpayOrder']);
$router->post('/api/payments/verify', [CheckoutController::class, 'verifyPaymentAndPlaceOrder']);

// ----------------------------------------------------
// 7. Orders, Tracking, Returns & Reviews
// ----------------------------------------------------
$router->get('/api/orders', [OrderController::class, 'getOrders']);
$router->get('/api/orders/{id}', [OrderController::class, 'getOrderById']);
$router->post('/api/orders/{id}/cancel', [OrderController::class, 'cancelOrder']);
$router->post('/api/returns', [OrderController::class, 'createReturn']);
$router->post('/api/reviews', [OrderController::class, 'addReview']);
$router->get('/api/products/{id}/reviews', [OrderController::class, 'getProductReviews']);

// ----------------------------------------------------
// 8. Seller Portal APIs
// ----------------------------------------------------
$router->post('/api/seller/login', [AuthController::class, 'sellerLogin']);
$router->get('/api/seller/dashboard', [SellerController::class, 'getDashboard']);
$router->get('/api/seller/products', [SellerController::class, 'getProducts']);
$router->post('/api/seller/products', [SellerController::class, 'createProduct']);
$router->get('/api/seller/inventory', [SellerController::class, 'getInventory']);
$router->put('/api/seller/inventory/{id}', [SellerController::class, 'updateInventory']);
$router->get('/api/seller/orders', [SellerController::class, 'getOrders']);
$router->put('/api/seller/orders/{id}/status', [SellerController::class, 'updateOrderStatus']);

// ----------------------------------------------------
// 9. Admin Control Center APIs
// ----------------------------------------------------
$router->post('/api/admin/login', [AuthController::class, 'adminLogin']);
$router->get('/api/admin/dashboard', [AdminController::class, 'getDashboard']);
$router->get('/api/admin/users', [AdminController::class, 'getUsers']);
$router->put('/api/admin/users/{id}', [AdminController::class, 'updateUser']);
$router->get('/api/admin/sellers', [AdminController::class, 'getSellers']);
$router->put('/api/admin/sellers/{id}/status', [AdminController::class, 'updateSellerStatus']);
$router->get('/api/admin/categories', [AdminController::class, 'getCategories']);
$router->post('/api/admin/categories', [AdminController::class, 'createCategory']);
$router->get('/api/admin/orders', [AdminController::class, 'getOrders']);
$router->put('/api/admin/orders/{id}/status', [AdminController::class, 'updateOrderStatus']);
$router->get('/api/admin/payments', [AdminController::class, 'getPayments']);
$router->get('/api/admin/coupons', [AdminController::class, 'getCoupons']);
$router->post('/api/admin/coupons', [AdminController::class, 'createCoupon']);
$router->get('/api/admin/reports', [AdminController::class, 'getReports']);
$router->get('/api/admin/logs', [AdminController::class, 'getLogs']);
