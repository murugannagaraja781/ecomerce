# 🛒 Complete E-Commerce Platform — Master Functionality Checklist

> **Project Name:** Flipkart / EzMart Turnkey E-Commerce Ecosystem  
> **Backend Architecture:** Native PHP 8+ (MVC / REST API) + MySQL (`flipkartdb`) + PDO  
> **Frontend Architecture:** Modern Vanilla SPA (Web, Seller Portal, Super Admin Dashboard, Mobile Responsive)  
> **Current CI/CD Status:** 100% Passed across all 6 Automated Test Suites  

---

## 📑 Table of Contents
1. [Module 1: Authentication & Role Management](#1-authentication--role-management)
2. [Module 2: Customer Storefront & Product Browsing](#2-customer-storefront--product-browsing)
3. [Module 3: Cart, Wishlist & SuperCoins Rewards](#3-cart-wishlist--supercoins-rewards)
4. [Module 4: Multi-Step Checkout & Address Management](#4-multi-step-checkout--address-management)
5. [Module 5: Multi-Gateway Payment Processing (Top 5 Indian Gateways)](#5-multi-gateway-payment-processing-top-5-indian-gateways)
6. [Module 6: Multi-Provider SMS & OTP Carrier Infrastructure](#6-multi-provider-sms--otp-carrier-infrastructure)
7. [Module 7: Real-Time Notifications & Push Alerts (FCM)](#7-real-time-notifications--push-alerts-fcm)
8. [Module 8: Order Lifecycle, Tracking & Status Workflow](#8-order-lifecycle-tracking--status-workflow)
9. [Module 9: Returns, Pickups & Instant Refund Workflow](#9-returns-pickups--instant-refund-workflow)
10. [Module 10: Ratings, Customer Reviews & Verified Badges](#10-ratings-customer-reviews--verified-badges)
11. [Module 11: Seller Portal & Multi-Vendor Management](#11-seller-portal--multi-vendor-management)
12. [Module 12: Super Admin & Store Management Control Center](#12-super-admin--store-management-control-center)
13. [Module 13: Live .env Configuration & Gateway Switcher](#13-live-env-configuration--gateway-switcher)
14. [Module 14: White-Label Store Branding & Turnkey SaaS Theme](#14-white-label-store-branding--turnkey-saas-theme)
15. [Module 15: Security, Rate Limiting, Audit Trails & Negative Edge Cases](#15-security-rate-limiting-audit-trails--negative-edge-cases)
16. [Module 16: Automated Testing & CI/CD Verification Suite](#16-automated-testing--cicd-verification-suite)

---

## 1. Authentication & Role Management

| # | Functionality | Description | Route / UI Path | Backend API / Method | DB Table |
|---|---|---|---|---|---|
| 1.1 | **Customer Login via Mobile OTP** | Enter 10-digit phone number, dispatch OTP (via active SMS provider), auto-verify demo OTP (`1369`) or live OTP, and return JWT Access & Refresh Tokens. | `#/login` | `POST /api/auth/send-otp`<br>`POST /api/auth/verify-otp` | `users`, `otps` |
| 1.2 | **Customer Email & Password Login** | Login with registered email and password hash using `password_verify` (Bcrypt). | `#/login` | `POST /api/auth/login` | `users` |
| 1.3 | **New User Auto-Registration** | If phone number does not exist during OTP verification, automatically register as a new `CUSTOMER` with initial 100 SuperCoins. | `#/login` | `POST /api/auth/verify-otp` | `users`, `user_roles` |
| 1.4 | **Super Admin & Admin Authentication** | Dedicated credential verification for Super Admin and Store Admin roles with elevated JWT privileges. | `#/admin` | `POST /api/admin/login` | `users`, `roles` |
| 1.5 | **Seller Portal Authentication** | Dedicated login workflow for registered multi-vendor sellers with GSTIN/store credentials. | `seller_web/` | `POST /api/seller/login` | `users`, `sellers` |
| 1.6 | **JWT Token Refresh & Auto-Healing** | Issuing short-lived access tokens (1h) and long-lived refresh tokens (30d) with auto-renewal middleware. | Global Header / Client | `POST /api/auth/refresh-token` | In-Memory / JWT Payload |
| 1.7 | **User Profile Edit & Avatar Management** | Super Admin / Customer profile modal to update Name, Email, Mobile, and view Role Badges. | Profile Modal Popup | `PUT /api/admin/users/{id}`<br>`PUT /api/user/profile` | `users` |
| 1.8 | **Role-Based Access Control (RBAC)** | Strict middleware guarding routes (`SUPER_ADMIN`, `ADMIN`, `SELLER`, `CUSTOMER`). | Middleware Guard | `backend/middleware/AuthMiddleware.php` | `roles`, `user_roles` |
| 1.9 | **One-Click Logout & Session Purge** | Clears local tokens (`fk_token`, `fk_admin_token`, `fk_seller_token`), resets application state, and re-renders clean guest UI. | Header Profile Pill ➔ `🚪 Logout` | Client State Reset | Browser LocalStorage |

---

## 2. Customer Storefront & Product Browsing

| # | Functionality | Description | Route / UI Path | Backend API / Method | DB Table |
|---|---|---|---|---|---|
| 2.1 | **Dynamic Homepage Catalog & Carousels** | Featured categories, trending deals, 10-Minute Minutes delivery banner, and deal banners. | `#/` | `GET /api/catalog/home`<br>`GET /api/products` | `products`, `categories` |
| 2.2 | **Multi-Category Hierarchical Navigation** | Browse categories (Mobiles, Electronics, Fashion, Home, Beauty, Grocery, Travel) with nested subcategories. | `#/category/{slug}` | `GET /api/categories` | `categories` |
| 2.3 | **Instant Real-Time Search with Typeahead** | Live search bar with instant substring and keyword matching across titles, brands, and descriptions. | Search Header Input | `GET /api/products?search={q}` | `products` |
| 2.4 | **Faceted Filtering & Sorting** | Filter by Price Range (Min/Max slider), Brand checkboxes, Minimum Rating (4★ & above), Discount (30%+, 50%+), and Sort by (Price: Low to High, High to Low, Newest, Popularity). | `#/category/{slug}` | `GET /api/products?sort={}&filter={}` | `products`, `brands` |
| 2.5 | **Product Detail Page (PDP) Rich View** | High-res image gallery, title, brand, rating summary, price discount pill, bank offers, EMI options, delivery pin check, and specs table. | `#/product/{id}` | `GET /api/products/{id}` | `products`, `product_variants`, `reviews` |
| 2.6 | **Multi-Variant Switcher (Color/Storage/RAM)** | Switch variants dynamically updating SKU, live stock count, price, and thumbnail previews. | `#/product/{id}` | `GET /api/products/{id}/variants` | `product_variants` |
| 2.7 | **Pincode Delivery Estimator** | Enter 6-digit Indian postal code to check estimated delivery timeline (Next-Day, 2-Day, Standard) and COD eligibility. | Product Page Pincode Box | Client calculation + `/api/pincodes` | `pincodes` / In-Memory |

---

## 3. Cart, Wishlist & SuperCoins Rewards

| # | Functionality | Description | Route / UI Path | Backend API / Method | DB Table |
|---|---|---|---|---|---|
| 3.1 | **Dynamic Slide-Over Shopping Cart** | Real-time drawer showing cart items, quantity +/- controls, item removal, price breakdown, and saved amounts. | Header 🛒 Button | `GET /api/cart`<br>`POST /api/cart/add`<br>`PUT /api/cart/update` | `carts`, `cart_items` |
| 3.2 | **One-Click Buy Now Flow** | Bypasses general cart to initiate an immediate single-item checkout sequence. | `⚡ Buy Now` Button | `POST /api/cart/buy-now` | `carts`, `orders` |
| 3.3 | **Customer Wishlist Management** | Add/Remove heart toggle on products with dedicated Wishlist view and "Move to Cart" button. | `#/wishlist` | `GET /api/wishlist`<br>`POST /api/wishlist/toggle` | `wishlists` |
| 3.4 | **Coupon Code Engine** | Apply promo codes (e.g. `WELCOME100`, `FLIPKART50`) with instant validation and discount deduction. | Cart / Checkout Drawer | `POST /api/coupons/apply` | `coupons`, `coupon_redemptions` |
| 3.5 | **SuperCoins Loyalty Rewards Program** | Earn 4 SuperCoins per ₹100 spent. Redeem coins for instant cart cash discounts (1 Coin = ₹1). | `#/supercoin` | `GET /api/supercoins/balance`<br>`POST /api/supercoins/redeem` | `user_supercoins`, `supercoin_ledger` |

---

## 4. Multi-Step Checkout & Address Management

| # | Functionality | Description | Route / UI Path | Backend API / Method | DB Table |
|---|---|---|---|---|---|
| 4.1 | **Delivery Address Book** | Add, edit, delete, and mark default shipping addresses with full validation (Name, Phone, Pincode, City, State). | `#/checkout` | `GET /api/addresses`<br>`POST /api/addresses`<br>`PUT /api/addresses/{id}` | `addresses` |
| 4.2 | **Order Summary Review** | Live order recalculation (Items Total, Delivery Charges / Free Delivery threshold, Packaging Fee, SuperCoins discount, Total Payable). | Checkout Step 2 | `POST /api/orders/preview` | In-Memory / `order_items` |
| 4.3 | **Stock & Inventory Concurrency Locking** | Checks real-time quantity before payment to prevent overselling on flash deals. | Checkout Pre-validation | `backend/services/InventoryService.php` | `product_variants` |

---

## 5. Multi-Gateway Payment Processing (Top 5 Indian Gateways)

| # | Functionality | Description | Route / UI Path | Backend API / Method | DB Table |
|---|---|---|---|---|---|
| 5.1 | **Razorpay PG Integration** | UPI, Credit/Debit Cards, NetBanking, and Razorpay Standard Checkout SDK popup. | Checkout Payment Step | `PaymentService::createOrder('razorpay')`<br>`POST /api/payments/razorpay/create-order` | `payments`, `orders` |
| 5.2 | **Cashfree Payments Integration** | High-conversion drop-in checkout, seamless payment session IDs, and instant refunds. | Checkout Payment Step | `PaymentService::createOrder('cashfree')` | `payments` |
| 5.3 | **PhonePe PG Direct UPI Integration** | PhonePe UPI Intent, QR Code generation, and SHA256 base64 payload checksum verification. | Checkout Payment Step | `PaymentService::createOrder('phonepe')` | `payments` |
| 5.4 | **Paytm All-In-One SDK Integration** | Paytm Wallet, UPI, and Paytm NetBanking via Transaction Tokens. | Checkout Payment Step | `PaymentService::createOrder('paytm')` | `payments` |
| 5.5 | **PayU India Enterprise Integration** | PayU Hosted Checkout with SHA512 hash sequence generation and webhook callbacks. | Checkout Payment Step | `PaymentService::createOrder('payu')` | `payments` |
| 5.6 | **Cash on Delivery (COD)** | Place orders with COD fee, OTP confirmation, and "Pay on Delivery" badge. | Checkout Payment Step | `POST /api/orders/place-cod` | `payments`, `orders` |
| 5.7 | **Payment Signature & Webhook Verification** | Validates SHA256/HMAC webhook signatures to prevent client-side payment spoofing. | Webhook Listeners | `POST /api/webhooks/payment/{gateway}` | `payments`, `payment_logs` |

---

## 6. Multi-Provider SMS & OTP Carrier Infrastructure

| # | Functionality | Description | Route / UI Path | Backend API / Method | DB Table |
|---|---|---|---|---|---|
| 6.1 | **Fast2SMS Indian Gateway** | Ultra-fast DLT-free Quick OTP SMS delivery with direct HTTP REST dispatch. | Login / Admin Settings | `SmsService::sendOtp('fast2sms')` | `sms_logs` |
| 6.2 | **MSG91 Enterprise SMS Route** | Enterprise transactional route with template ID support and carrier fallbacks. | Login / Admin Settings | `SmsService::sendOtp('msg91')` | `sms_logs` |
| 6.3 | **Twilio Programmable SMS** | Global international SMS delivery with Account SID and Auth Token auth. | Login / Admin Settings | `SmsService::sendOtp('twilio')` | `sms_logs` |
| 6.4 | **Textlocal India Gateway** | Direct Indian telecom operator route with DLT compliance headers. | Login / Admin Settings | `SmsService::sendOtp('textlocal')` | `sms_logs` |
| 6.5 | **Local Development SMS Simulator** | Zero-latency local simulation writing OTP to backend logs for testing without consuming API credits. | Login / Admin Settings | `SmsService::sendOtp('local')` | `sms_logs` |
| 6.6 | **Live SMS Carrier Test Tool** | Super Admin UI tool to dispatch real test OTP to any mobile number with instant carrier status. | `#/admin/settings` | `POST /api/admin/settings/test-sms` | `sms_logs` |

---

## 7. Real-Time Notifications & Push Alerts (FCM)

| # | Functionality | Description | Route / UI Path | Backend API / Method | DB Table |
|---|---|---|---|---|---|
| 7.1 | **Firebase Cloud Messaging (FCM)** | Real-time push notifications for Order Placed, Shipped, Out-for-Delivery, and Flash Sale alerts. | Client Push Listener | `NotificationService::sendPush()` | `notifications` |
| 7.2 | **Interactive FCM Push Tester** | Super Admin UI button to trigger a live test push broadcast to verify Firebase credentials. | `#/admin/settings` | `POST /api/admin/settings/test-fcm` | `notifications`, `logs/fcm.log` |
| 7.3 | **In-App Toast Notification Center** | Animated toast notifications for actions (Added to Cart, Wishlist toggled, Config saved). | Global UI | `showToast(msg)` in `customer_web/app.js` | In-Memory Client State |

---

## 8. Order Lifecycle, Tracking & Status Workflow

| # | Functionality | Description | Route / UI Path | Backend API / Method | DB Table |
|---|---|---|---|---|---|
| 8.1 | **Customer Order History & Receipt View** | List all past orders with status chips (`PLACED`, `CONFIRMED`, `SHIPPED`, `OUT_FOR_DELIVERY`, `DELIVERED`, `CANCELLED`). | `#/orders` | `GET /api/orders` | `orders`, `order_items` |
| 8.2 | **Visual Order Tracking Timeline** | Step-by-step progress tracker with timestamps, courier name, tracking AWB number, and estimated arrival. | `#/orders/{id}` | `GET /api/orders/{id}` | `orders`, `order_status_history` |
| 8.3 | **Customer Self-Service Order Cancellation** | Cancel order before shipment with reason selection and automatic inventory restocking. | `#/orders/{id}` | `POST /api/orders/{id}/cancel` | `orders`, `order_items`, `products` |
| 8.4 | **Admin Order Status Transitioning** | Super Admin / Store Admin dropdown to advance order statuses with automatic notification dispatch. | `#/admin/orders` | `PUT /api/admin/orders/{id}/status` | `orders`, `order_status_history` |

---

## 9. Returns, Pickups & Instant Refund Workflow

| # | Functionality | Description | Route / UI Path | Backend API / Method | DB Table |
|---|---|---|---|---|---|
| 9.1 | **Customer Return Request Portal** | Request 7-day return/replacement on delivered products with reason (Damaged, Wrong item, Quality issue). | `#/orders/{id}` ➔ Return | `POST /api/returns` | `returns`, `order_items` |
| 9.2 | **Admin Return Request Management** | Super Admin panel to Approve, Reject, Schedule Pickup, or Mark Picked-Up. | `#/admin/returns` | `GET /api/admin/returns`<br>`PUT /api/admin/returns/{id}/status` | `returns` |
| 9.3 | **Automated Gateway Refund Processing** | Trigger automatic refund API via Razorpay/Cashfree back to customer's source account upon pickup. | `#/admin/refunds` | `POST /api/admin/refunds` | `refunds`, `payments` |

---

## 10. Ratings, Customer Reviews & Verified Badges

| # | Functionality | Description | Route / UI Path | Backend API / Method | DB Table |
|---|---|---|---|---|---|
| 10.1 | **Customer 5-Star Rating & Review Form** | Submit star ratings, review title, detailed feedback, and review photos. | `#/product/{id}` ➔ Write Review | `POST /api/reviews` | `reviews` |
| 10.2 | **Verified Purchase Badge Verification** | System checks if the reviewer purchased and received the product before displaying the `✓ Verified Purchase` badge. | Product Page Reviews | `OrderController::addReview()` | `reviews`, `orders` |
| 10.3 | **Dynamic Aggregate Product Rating Engine** | Automatically recalculates average rating (e.g. 4.6 ★) and total rating count in `products` table. | Automatic DB Trigger / Service | `OrderController::recalculateRating()` | `products` |

---

## 11. Seller Portal & Multi-Vendor Management

| # | Functionality | Description | Route / UI Path | Backend API / Method | DB Table |
|---|---|---|---|---|---|
| 11.1 | **Seller Dashboard & Sales Analytics** | Real-time KPIs for Total Sales, Pending Orders, Low Stock Alerts, and Net Payouts. | `seller_web/` | `GET /api/seller/dashboard` | `sellers`, `orders`, `products` |
| 11.2 | **Seller Product Catalog & Multi-Variant Listing** | Add products, upload images, specify categories, MRP, Selling Price, and SKU variants. | `seller_web/#/products` | `GET /api/seller/products`<br>`POST /api/seller/products` | `products`, `product_variants` |
| 11.3 | **Seller Stock & Inventory Control** | Update inventory quantities per variant with live out-of-stock badges. | `seller_web/#/inventory` | `PUT /api/seller/inventory/{id}` | `product_variants` |
| 11.4 | **Seller Order Fulfillment & Shipping Labels** | View orders assigned to seller's products and mark them `READY_FOR_PICKUP` or `SHIPPED`. | `seller_web/#/orders` | `PUT /api/seller/orders/{id}/status` | `orders`, `order_items` |
| 11.5 | **Admin Seller Approval & Verification** | Super Admin approves new seller registrations and manages commission percentages. | `#/admin/sellers` | `GET /api/admin/sellers`<br>`PUT /api/admin/sellers/{id}/status` | `sellers` |

---

## 12. Super Admin & Store Management Control Center

| # | Functionality | Description | Route / UI Path | Backend API / Method | DB Table |
|---|---|---|---|---|---|
| 12.1 | **Super Admin Master KPI Dashboard** | Live telemetry: Total Revenue, Gross Orders, Registered Customers, Active Sellers, and Low Stock Alerts. | `#/admin` (Overview) | `GET /api/admin/dashboard` | `orders`, `users`, `products` |
| 12.2 | **Interactive "+ Add Product" Modal** | Add new products with Title, Brand, Category, Base Price, MRP, Stock, Images, and Description directly to store catalog. | Header `+ Add Product` / Overview Bar | `POST /api/admin/products` | `products`, `product_variants` |
| 12.3 | **Full Catalog Management & Price Editor** | View complete 100+ product catalog, edit prices, update stock, and toggle active/inactive status. | `#/admin/catalog` | `GET /api/admin/products`<br>`PUT /api/admin/products/{id}` | `products` |
| 12.4 | **Customer User Management & Role Promotion** | View registered users, phone numbers, role tags, and promote users to `ADMIN` or `SELLER`. | `#/admin/users` | `GET /api/admin/users`<br>`PUT /api/admin/users/{id}` | `users`, `roles` |
| 12.5 | **Admin Quick Actions Bar** | Instant action pills: `+ Add New Product`, `📦 View Catalog`, `📑 Recent Orders`, `⚙️ .env & Gateways`. | `#/admin` (Overview) | UI Fast Navigation | Client Routing |

---

## 13. Live .env Configuration & Gateway Switcher

| # | Functionality | Description | Route / UI Path | Backend API / Method | DB Table |
|---|---|---|---|---|---|
| 13.1 | **Active Payment Gateway Switcher** | Switch the platform's default payment processor between Razorpay, Cashfree, PhonePe, Paytm, and PayU. | `#/admin/settings` (Section 1) | `PUT /api/admin/settings` | `backend/.env` |
| 13.2 | **Active SMS / OTP Provider Switcher** | Switch default SMS gateway between Fast2SMS, MSG91, Twilio, Textlocal, and Local Dev. | `#/admin/settings` (Section 2) | `PUT /api/admin/settings` | `backend/.env` |
| 13.3 | **Dynamic API Key & Credential Editor** | Form fields to update API Keys, Merchant IDs, Salt Keys, Webhook Secrets, and Production/Sandbox environments. | `#/admin/settings` | `PUT /api/admin/settings` | `backend/.env` |
| 13.4 | **Direct Raw .env Code Editor** | Dark-themed Monaco/Consolas code editor to inspect and edit `backend/.env` raw text with atomic disk writes. | `#/admin/settings` (Section 4) | `GET /api/admin/settings/env`<br>`PUT /api/admin/settings/env` | `backend/.env` |
| 13.5 | **Interactive Gateway Connectivity Testers** | One-click test buttons (`⚡ Test Razorpay`, `💳 Test Cashfree`, `📱 Test PhonePe`, `💰 Test Paytm`, `🛡️ Test PayU`) to verify live credentials. | `#/admin/settings` | `POST /api/admin/settings/test-payment` | `payments` |

---

## 14. White-Label Store Branding & Turnkey SaaS Theme

| # | Functionality | Description | Route / UI Path | Backend API / Method | DB Table |
|---|---|---|---|---|---|
| 14.1 | **Live Brand Customizer** | Modify Store Name (Site Name), Subtitle / Tagline, Logo URL, Favicon URL, Support Phone, Support Email, and Copyright. | `#/admin/settings` (Section 0) | `PUT /api/admin/settings/branding` | `backend/config/branding.json` / State |
| 14.2 | **Theme Color Customizer & Color Pickers** | Interactive color pickers to dynamically change Primary Brand Color and Secondary Accent Color across all UI components. | `#/admin/settings` (Section 0) | `PUT /api/admin/settings/branding` | CSS Variables / State |
| 14.3 | **One-Click Brand Presets** | Instant theme switcher presets: **Flipkart Blue**, **Amazon Orange**, **Myntra Pink**, **Modern Purple**, **Emerald Green**. | `#/admin/settings` (Section 0) | Client Preset Dispatcher | CSS Variables / State |

---

## 15. Security, Rate Limiting, Audit Trails & Negative Edge Cases

| # | Functionality | Description | Route / UI Path | Backend API / Method | DB Table |
|---|---|---|---|---|---|
| 15.1 | **SQL Injection & XSS Defense** | Strict PDO Prepared Statements on 100% of queries, payload sanitization, and HTML entity escaping. | Backend Core | `backend/config/database.php` | MySQL Security Layer |
| 15.2 | **Brute-Force & Rate Limiting Guard** | IP & Phone based throttle (max 5 failed OTP / login attempts per 15 minutes) returning HTTP `429 Too Many Requests`. | Login Endpoints | `backend/middleware/RateLimitMiddleware.php` | `login_attempts` |
| 15.3 | **Admin Audit Trail Logging** | Logs every critical administrative action (Price change, .env update, Order cancel, Refund processed, User edit) with Admin ID and timestamp. | `#/admin/logs` | `GET /api/admin/logs` | `admin_logs` |
| 15.4 | **Negative Security & Malformed Input Suite** | Automated validation rejecting invalid tokens, tampered amounts, expired OTPs, and negative pricing. | Security Tests | `tests/test_security_negative.php` | Security Layer |

---

## 16. Automated Testing & CI/CD Verification Suite

All tests can be executed via terminal: `C:\xampp\php\php.exe tests/run_all_tests.php`

| Test Suite | Test Script Path | Description | Result |
|---|---|---|---|
| **1. Auth & Token Suite** | [`tests/test_auth.php`](file:///c:/xampp/htdocs/ecomerce/tests/test_auth.php) | Tests OTP dispatch, verification, JWT signing, password login, RBAC guards. | **PASS (100%)** |
| **2. Catalog & Facet Suite** | [`tests/test_catalog.php`](file:///c:/xampp/htdocs/ecomerce/tests/test_catalog.php) | Tests product indexing, category hierarchies, facet filters, search sorting. | **PASS (100%)** |
| **3. Platform E2E Flow Suite** | [`tests/test_e2e_backend.php`](file:///c:/xampp/htdocs/ecomerce/tests/test_e2e_backend.php) | Tests complete checkout lifecycle: Cart ➔ Coupon ➔ Order ➔ Payment ➔ Delivery. | **PASS (100%)** |
| **4. Negative Security Suite** | [`tests/test_security_negative.php`](file:///c:/xampp/htdocs/ecomerce/tests/test_security_negative.php) | Tests SQL injection vectors, tampered payloads, expired tokens, rate limits. | **PASS (100%)** |
| **5. Property & Fuzzing Suite** | [`tests/test_property_based.php`](file:///c:/xampp/htdocs/ecomerce/tests/test_property_based.php) | Tests random edge case inputs, extreme price bounds, unicode strings. | **PASS (100%)** |
| **6. Super Admin & Gateway Suite** | [`tests/test_env_and_gateways.php`](file:///c:/xampp/htdocs/ecomerce/tests/test_env_and_gateways.php) | Tests atomic `.env` read/write, 5 Payment Gateways, 5 SMS Providers, FCM. | **PASS (100%)** |
| **7. Live Add Product E2E** | [`tests/test_live_add_product_e2e.php`](file:///c:/xampp/htdocs/ecomerce/tests/test_live_add_product_e2e.php) | Tests Super Admin live product creation, MySQL insertion, catalog visibility. | **PASS (100%)** |
| **8. Live .env API E2E** | [`tests/test_superadmin_env_e2e.php`](file:///c:/xampp/htdocs/ecomerce/tests/test_superadmin_env_e2e.php) | Tests HTTP API endpoints for Super Admin live `.env` and gateway updates. | **PASS (100%)** |

---

## 🎯 Quick Verification URLs & Credentials

* **Customer Web Store:** [http://localhost/ecomerce/customer_web/#/](http://localhost/ecomerce/customer_web/#/)
* **Super Admin Dashboard:** [http://localhost/ecomerce/customer_web/#/admin](http://localhost/ecomerce/customer_web/#/admin)
* **Super Admin .env Settings:** [http://localhost/ecomerce/customer_web/#/admin/settings](http://localhost/ecomerce/customer_web/#/admin/settings)
* **Seller Portal:** [http://localhost/ecomerce/seller_web/](http://localhost/ecomerce/seller_web/)

### Default Login Accounts:
* **Super Admin:** `admin@flipkart.local` / `Admin@12345` (Phone: `9876543210`, Demo OTP: `1369`)
* **Store Admin:** `ops@flipkart.local` / `Admin@12345` (Phone: `9000000001`, Demo OTP: `1369`)
* **Customer:** `customer@gmail.com` / `Customer@123` (Phone: `8000000001`, Demo OTP: `1369`)
* **Seller:** `seller@flipkart.local` / `Seller@12345` (Phone: `9111111111`, Demo OTP: `1369`)
