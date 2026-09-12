# Flipkart Platform - Production Deployment & Operations Guide

## 1. Production Architecture Overview
- **Web Server**: Apache 2.4 / Nginx with PHP 8.2+ FPM.
- **Database**: MySQL 8.0+ / MariaDB 10.6+ InnoDB with row-level locks.
- **Frontend SPA**: Static HTML5 + Vanilla JS + CSS3 hosted via CDN or reverse-proxied.
- **Mobile Client**: Flutter 3.x (Dart 3.x) targeting Android (minSdk 21) and iOS (12.0+).

---

## 2. Server Prerequisites
- PHP 8.1 or PHP 8.2 with extensions:
  - `pdo_mysql`
  - `curl`
  - `fileinfo`
  - `mbstring`
  - `openssl`
  - `json`
- MySQL 8.0+ with `innodb_file_per_table=ON`, `innodb_buffer_pool_size=1G+`
- HTTPS/TLS termination via Let's Encrypt / Cloudflare SSL.

---

## 3. Database Initialization & Migration
```bash
# 1. Create production database
mysql -u root -p -e "CREATE DATABASE flipkartdb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 2. Import core schema and seed data
mysql -u root -p flipkartdb < database/schema.sql
mysql -u root -p flipkartdb < database/seeds.sql
```

---

## 4. Environment Configuration
Copy `.env.example` to `.env` in the `backend/` directory:
```bash
cp backend/.env.example backend/.env
```
Ensure production values for:
```ini
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api.yourdomain.com

DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=flipkartdb
DB_USER=flipkart_prod_user
DB_PASS=StrongProductionPasswordHere

JWT_SECRET=Generate64HexCharactersCryptographicKeyHere
JWT_REFRESH_SECRET=GenerateDifferent64HexCharactersKeyHere

RAZORPAY_KEY_ID=rzp_live_YourKeyHere
RAZORPAY_KEY_SECRET=YourRazorpayLiveSecretHere
RAZORPAY_WEBHOOK_SECRET=YourWebhookSecretHere

CORS_ALLOWED_ORIGINS=https://flipkart.yourdomain.com,https://seller.yourdomain.com,https://admin.yourdomain.com
```

---

## 5. Directory Permissions & Security
```bash
# Ensure web server can read files and write to uploads/logs
chmod -R 755 backend/
chmod -R 775 backend/uploads/
chmod 600 backend/.env

# Verify uploads folder execution block is in place
cat backend/uploads/.htaccess
```

---

## 6. Verification Commands
```bash
# Run Authentication Test Suite
php tests/test_auth.php

# Run Catalog Test Suite
php tests/test_catalog.php

# Run Platform End-to-End Test Suite
php tests/test_e2e_backend.php

# Run 20-Point Security Negative Test Suite
php tests/test_security_negative.php
```
