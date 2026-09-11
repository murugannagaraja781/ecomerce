# Flipkart E-Commerce Platform Setup & Execution Guide

This guide details how to run the full stack: XAMPP Apache, PHP 8.x REST API, MySQL `flipkartdb`, and the Flutter frontend across Mobile, Customer Web, Seller Portal, and Admin Control Center.

---

## 1. Prerequisites

- **XAMPP**: Apache & MySQL active.
- **PHP**: PHP 8.0+ with `pdo_mysql`, `openssl`, `curl`, `mbstring`, `json`.
- **MySQL**: Running on `localhost:3306`.
- **Flutter SDK**: Flutter 3.44+ with Dart 3.12+.

---

## 2. Database Setup

The database `flipkartdb` is already created and seeded. If you ever need to re-import the schema or seeds from scratch:

```bash
# Import schema (42 tables)
cmd.exe /c "C:\xampp\mysql\bin\mysql.exe -u root flipkartdb < c:\xampp\htdocs\ecomerce\database\schema.sql"

# Import demo data (105 products, variants, brands, categories, users)
cmd.exe /c "C:\xampp\mysql\bin\mysql.exe -u root flipkartdb < c:\xampp\htdocs\ecomerce\database\seed.sql"
```

To verify via phpMyAdmin:  
[http://localhost/phpmyadmin/index.php?route=/database/structure&db=flipkartdb](http://localhost/phpmyadmin/index.php?route=/database/structure&db=flipkartdb)

---

## 3. Backend REST API

The PHP REST API runs under Apache via XAMPP.

- **Primary API URL**: `http://localhost/ecommerce_api/api`
- **Alternative Path**: `http://localhost/ecomerce/backend/api`
- **Healthcheck Test**:
  Open in browser or test via curl:
  ```bash
  curl http://localhost/ecommerce_api/api/health
  ```
  Returns:
  ```json
  {
    "success": true,
    "message": "API is running smoothly",
    "data": {
      "status": "healthy",
      "database": {
        "status": "connected",
        "database": "flipkartdb",
        "tables_count": 42
      }
    }
  }
  ```

---

## 4. Running Backend Test Suites

We have built automated CLI test suites:

1. **Authentication Tests**:
   ```bash
   php c:\xampp\htdocs\ecomerce\tests\test_auth.php
   ```
2. **Catalog & Search Tests**:
   ```bash
   php c:\xampp\htdocs\ecomerce\tests\test_catalog.php
   ```
3. **Complete End-to-End Platform Test**:
   (Tests login, cart, coupons, Razorpay order, signature verification, atomic order placement, inventory deduction, 6-milestone tracking, seller order update, and admin dashboard)
   ```bash
   php c:\xampp\htdocs\ecomerce\tests\test_e2e_backend.php
   ```

---

## 5. Running the Flutter Frontends

The Flutter application code lives in `mobile/`. It supports:
- **Android / iOS**
- **Customer Web**
- **Seller Portal** (`/seller`)
- **Admin Control Center** (`/admin`)

### Run on Chrome (Web)
```bash
cd c:\xampp\htdocs\ecomerce\mobile
flutter run -d chrome
```

### Run on Edge (Web)
```bash
cd c:\xampp\htdocs\ecomerce\mobile
flutter run -d edge
```

### Run on Android Emulator
```bash
cd c:\xampp\htdocs\ecomerce\mobile
flutter run -d android
```

### Run on Windows Desktop
```bash
cd c:\xampp\htdocs\ecomerce\mobile
flutter run -d windows
```

---

## 6. Pre-configured Demo Accounts

| Role | Email | Password | Details |
|---|---|---|---|
| **Super Admin** | `admin@flipkart.local` | `Admin@12345` | Full administrative control |
| **Seller** | `seller@celvas.in` | `Seller@12345` | Celvas Official Store |
| **Customer** | `customer@gmail.com` | `Customer@12345` | Rahul Sharma (Bengaluru) |
| **Customer 2** | `priya.patel@gmail.com` | `Customer@12345` | Priya Patel (Mumbai) |

---

## 7. Sample Test Product

Search for or open:
- **Product ID**: `1`
- **Product Slug**: `celvas-back-cover-apple-iphone-15`
- **Title**: `Celvas Back Cover for Apple iPhone 15`
- Features: 3 color variants (Midnight Black, Ocean Blue, Frost Clear), military-grade drop protection highlights, MagSafe wireless charging compatibility, 4 gallery photos, seller rating (4.85), verified purchaser reviews.
