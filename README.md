# 🛒 Complete Flipkart-Style E-Commerce Platform

A production-ready Indian e-commerce shopping platform inspired by Flipkart's shopping experience, built with Flutter (Mobile & Responsive Web), a modular PHP 8.x REST API, and MySQL (`flipkartdb`).

---

## 🌟 Platform Highlights

- **Multi-Platform Flutter Client**: Responsive across Mobile (Android/iOS), Tablet, and Desktop Web.
- **Flipkart Brand Aesthetics**: Distinctive Flipkart blue (`#2874F0`), yellow (`#FFE500`), orange (`#FB641B`), and green (`#388E3C`) design language with auto-scrolling hero banners, category icons, rating badges, and price breakdown cards.
- **Server-Side Search Engine**: Debounced auto-complete suggestions, keyword matching, and multi-facet filtering (Category, Brand, Price range, Minimum rating, Discount percentage).
- **Server-Synchronized Cart & Wishlist**: Real-time stock validation, automated delivery charge calculation (Free delivery above ₹499), and coupon discount engine (`WELCOME100`, `FLIPDEAL20`).
- **Razorpay Payment Integration**: Server-side HMAC SHA-256 signature verification and **Atomic MySQL Order Placement Transaction** (`BEGIN TRANSACTION` -> stock check `FOR UPDATE` -> inventory deduction -> orders & payment creation -> `COMMIT` / `ROLLBACK`).
- **6-Milestone Order Tracking**: Visual order progress timeline (Order Placed -> Confirmed -> Packed by Seller -> Shipped -> Out for Delivery -> Delivered).
- **Seller Portal**: Merchant dashboard with sales KPIs, product catalog management, stock adjustments, and order fulfillment.
- **Admin Control Center**: Comprehensive oversight of platform revenue, user management, seller verifications, order management, coupons, and audit logs.
- **Featured Sample Product**: *Celvas Back Cover for Apple iPhone 15* (ID #1) with military-grade drop protection, MagSafe compatibility, 3 color variants, 4 high-res gallery images, specifications, and verified buyer reviews.

---

## 📂 Project Architecture

```
ecommerce_platform/
├── mobile/                   # Flutter application (Android, iOS, Web)
│   ├── lib/
│   │   ├── core/            # Constants, Theme, Network (Dio), Storage, Utils, Responsive
│   │   ├── models/           # Domain models (User, Product, Cart, Order, Address, etc.)
│   │   ├── repositories/     # API repositories with error handling
│   │   ├── providers/        # Riverpod state management
│   │   ├── screens/          # Home, Products, Detail, Cart, Checkout, Orders, Seller, Admin
│   │   ├── widgets/          # Reusable Flipkart UI widgets (ProductCard, SearchAppBar, etc.)
│   │   └── routes/           # GoRouter route declarations
│   └── pubspec.yaml
├── backend/                  # Modular PHP 8.x REST API
│   ├── config/               # database.php (PDO singleton), environment.php, config.php
│   ├── controllers/          # Auth, Catalog, Search, Cart, Address, Checkout, Order, Seller, Admin
│   ├── middleware/           # CorsMiddleware, AuthMiddleware, RoleMiddleware
│   ├── helpers/              # Response.php, JwtHelper.php, Validator.php, Logger.php
│   ├── routes/               # Router.php, api.php
│   └── index.php             # Front controller with CORS and URL rewriting
├── database/                 # Database schema & migrations
│   ├── schema.sql            # 42 relational tables with foreign keys and indexes
│   ├── seed.sql              # 105 products, 220 variants, 48 categories, 25 brands, demo users
│   └── migrations/           # Versioned migrations and PHP migration runner
└── docs/                     # Full technical documentation
    ├── API.md                # Comprehensive REST API reference
    ├── DATABASE.md           # Database architecture and entity diagrams
    └── SETUP.md              # Installation, local setup, and test runner guide
```

---

## 🚀 Quick Start

### 1. Database & Backend
MySQL database `flipkartdb` is active on `localhost:3306` with 42 tables and 105 products.
The API is accessible at:
- `http://localhost/ecommerce_api/api`
- Test health: `http://localhost/ecommerce_api/api/health`

### 2. Run Automated Test Suite
```bash
# Run comprehensive end-to-end platform test
php c:\xampp\htdocs\ecomerce\tests\test_e2e_backend.php
```

### 3. Run Flutter Application
```bash
cd c:\xampp\htdocs\ecomerce\mobile
flutter run -d chrome
```

---

## 🔑 Demo Credentials

- **Customer**: `customer@gmail.com` / `Customer@12345`
- **Seller**: `seller@celvas.in` / `Seller@12345`
- **Admin**: `admin@flipkart.local` / `Admin@12345`
