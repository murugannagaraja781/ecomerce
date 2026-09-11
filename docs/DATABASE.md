# Database Architecture & Schema Specification

**Database Name**: `flipkartdb`  
**RDBMS**: MySQL 8.x / MariaDB (InnoDB Engine)  
**Encoding**: `utf8mb4` with `utf8mb4_unicode_ci` collation  
**phpMyAdmin URL**: `http://localhost/phpmyadmin/index.php?route=/database/structure&db=flipkartdb`

---

## 1. Core Entity Relationship Overview

```
                      ┌───────────────┐
                      │     roles     │
                      └───────┬───────┘
                              │ 1:N
                      ┌───────▼───────┐
                      │     users     │◄─────────────────┐
                      └──┬─────────┬──┘                  │
                         │         │                     │
                1:1 ┌────▼───┐  1:N│                     │
                    │sellers │     │                     │
                    └────┬───┘     │                     │
                         │         │                     │
       ┌─────────────────┼─────────┼────────────────┐    │
       │                 │         │                │    │
┌──────▼─────┐    ┌──────▼──────┐  │         ┌──────▼────┴──┐
│ categories │    │  products   │  │         │  addresses   │
└──────┬─────┘    └──────┬──────┘  │         └──────┬───────┘
       │                 │         │                │
       │ 1:N             │ 1:N     │ 1:N            │ 1:1 snapshot
┌──────▼───────┐  ┌──────▼──────┐  │         ┌──────▼────────┐
│ subcategories│  │  variants   │  │         │order_addresses│
└──────────────┘  └──────┬──────┘  │         └───────────────┘
                         │         │
                         │ 1:1     │
                  ┌──────▼─────────┴──┐
                  │    order_items    │
                  └──────┬────────────┘
                         │ N:1
                  ┌──────▼──────┐
                  │   orders    │
                  └──────┬──────┘
                         │ 1:N
                  ┌──────▼──────────────┐
                  │order_status_history │
                  └─────────────────────┘
```

---

## 2. Table Summary (42 Tables)

| # | Table Name | Purpose |
|---|---|---|
| 1 | `roles` | System roles: SUPER_ADMIN, ADMIN, SELLER, CUSTOMER, SUPPORT_AGENT |
| 2 | `permissions` | Granular capability permissions |
| 3 | `role_permissions` | Mapping between roles and permissions |
| 4 | `users` | User accounts with bcrypt password hashes |
| 5 | `user_tokens` | JWT refresh tokens with device audit and expiration |
| 6 | `sellers` | Merchant store profiles, GSTIN, PAN, commission, ratings |
| 7 | `seller_documents` | Seller onboarding verification documents |
| 8 | `categories` | Hierarchical category system (parent & subcategory support) |
| 9 | `brands` | Authorized manufacturer brands with logos |
| 10 | `products` | Master product catalog, pricing, highlights, specs, ratings |
| 11 | `product_variants` | Product SKUs (Color, Size, RAM, Storage, Stock, Price) |
| 12 | `product_images` | High-res gallery images with primary image flags |
| 13 | `product_attributes` | Flexible product attributes |
| 14 | `seller_products` | Multi-seller pricing and stock assignments |
| 15 | `inventory` | Live stock tracking with low-stock thresholds |
| 16 | `inventory_transactions`| Full audit log of stock movements (Sales, Restocks, Adjustments) |
| 17 | `addresses` | User delivery addresses with HOME / WORK / OTHER tags |
| 18 | `carts` | Server-synchronized cart headers |
| 19 | `cart_items` | Active shopping cart items with variant and quantity |
| 20 | `wishlists` | Customer wishlists |
| 21 | `wishlist_items` | Saved wishlist items |
| 22 | `saved_for_later` | Items deferred from shopping cart |
| 23 | `coupons` | Admin-controlled discount codes (Percentage & Fixed) |
| 24 | `coupon_usage` | Per-user coupon redemption history |
| 25 | `orders` | Master orders table with totals, status, delivery date |
| 26 | `order_items` | Individual line items in an order |
| 27 | `order_addresses` | Immutable snapshot of delivery address at checkout |
| 28 | `order_status_history` | 6-milestone tracking history with timestamps |
| 29 | `payments` | Razorpay payment records, payment ID, order ID, signature |
| 30 | `refunds` | Refund records linked to orders and payments |
| 31 | `returns` | Customer return requests with reason and photos |
| 32 | `return_items` | Line items being returned |
| 33 | `reviews` | Verified purchaser product ratings and reviews |
| 34 | `review_images` | Review photos uploaded by customers |
| 35 | `review_reports` | Inappropriate review reports for moderation |
| 36 | `notifications` | In-app user notifications |
| 37 | `notification_devices` | FCM push notification registration tokens |
| 38 | `banners` | Promotional carousel banners and sale strips |
| 39 | `search_history` | Popular and user search history |
| 40 | `recently_viewed` | Recently viewed products per user |
| 41 | `admin_logs` | Audit trail of administrative actions |
| 42 | `settings` | Platform global configuration key-value store |

---

## 3. Demo Dataset Highlights

- **Categories**: 12 top-level categories, 36 subcategories (48 total).
- **Brands**: 25 brands (Apple, Samsung, OnePlus, Celvas, boAt, Noise, Sony, Puma, Nike, Levi's, Philips, LG, Asus, etc.).
- **Products**: 105 realistic products.
  - **Sample Product**: *Celvas Back Cover for Apple iPhone 15* with MagSafe, 3 color variants, 4 high-res gallery images, 5 highlights, technical specifications, and verified purchaser reviews.
- **Demo Users**:
  - Super Admin: `admin@flipkart.local` (Password: `Admin@12345`)
  - Seller: `seller@celvas.in` (Password: `Seller@12345`) - Celvas Official Store
  - Customer: `customer@gmail.com` (Password: `Customer@12345`) - Rahul Sharma
  - Customer: `priya.patel@gmail.com` (Password: `Customer@12345`) - Priya Patel
- **Coupons**: `WELCOME100` (₹100 off), `FLIPDEAL20` (20% off), `FREESHIP` (Free delivery), `BIGFEST10` (10% off).
