# Flipkart Platform REST API Documentation

Base URL: `http://localhost/ecommerce_api/api` or `http://localhost/ecomerce/backend/api`  
Authentication: Bearer JWT Token in `Authorization: Bearer <access_token>` header.

---

## 1. Authentication & Profile

### `POST /auth/register`
Create a customer account.
- **Payload**:
  ```json
  {
    "name": "Rahul Sharma",
    "email": "customer@gmail.com",
    "password": "Customer@12345",
    "phone": "9876543210"
  }
  ```
- **Response**: Returns User profile, Access Token, and Refresh Token.

### `POST /auth/login`
Customer login.
- **Payload**:
  ```json
  {
    "email": "customer@gmail.com",
    "password": "Customer@12345"
  }
  ```
- **Response**: JWT access token, refresh token, user details.

### `POST /auth/send-otp`
Generate and simulate SMS OTP for mobile verification.
- **Payload**: `{"phone": "9876543210"}`
- **Test OTP**: `789012`

### `POST /auth/verify-otp`
Verify SMS OTP and authenticate user.
- **Payload**: `{"phone": "9876543210", "otp": "789012"}`

### `POST /auth/refresh`
Rotate expired access token using refresh token.
- **Payload**: `{"refresh_token": "..."}`

### `GET /auth/profile`
Retrieve authenticated user profile and live cart/order counts.
- **Headers**: `Authorization: Bearer <token>`

---

## 2. Catalog & Homepage Feeds

### `GET /home`
Fetches complete Flipkart homepage feed:
- Banners (Hero auto-carousel, promotion strips)
- Parent categories with circular icons
- Flash deals (Discount >= 40%)
- Best sellers & trending products
- Spotlight product (*Celvas Back Cover for Apple iPhone 15*)

### `GET /categories`
Returns hierarchical category tree with parent categories and nested subcategories.

### `GET /categories/{id}`
Returns category detail with child subcategories and sort order.

### `GET /brands`
Returns list of active authorized brands.

### `GET /products`
Catalog listing with multi-facet filters:
- Query parameters:
  - `category_id`: Category or parent ID
  - `brand_id`: Brand ID
  - `min_price` & `max_price`: Price range
  - `min_rating`: Minimum star rating (e.g. 4)
  - `min_discount`: Minimum discount % (e.g. 50)
  - `sort`: `popularity`, `price_low`, `price_high`, `rating`, `newest`, `discount`
  - `page` & `limit`: Pagination parameters

### `GET /products/{id}`
Full product detail page:
- Gallery images
- Color and size variants
- Highlights bullet points
- Technical specifications table
- Seller rating and warranty
- Customer reviews and ratings
- Similar products carousel

---

## 3. Server-Side Search Engine

### `GET /search?q={query}`
Full server-side search across Product titles, descriptions, brand names, and SKUs.
- Debounced and optimized.

### `GET /search/suggestions?q={query}`
Debounced auto-complete suggestions matching products, brands, and categories.

### `GET /search/popular`
Returns trending and frequently searched keywords.

---

## 4. Server-Synchronized Cart & Wishlist

### `GET /cart`
Calculates live MRP, Selling price, Delivery fee (FREE above ₹499, else ₹40), and Savings.

### `POST /cart/items`
Add item to cart with stock validation.
- **Payload**: `{"product_id": 1, "variant_id": 1, "quantity": 2}`

### `PUT /cart/items/{id}`
Update item quantity with live stock limit check.
- **Payload**: `{"quantity": 3}`

### `DELETE /cart/items/{id}`
Remove item from cart.

### `GET /wishlist`
Returns user saved wishlist items.

### `POST /wishlist`
Add item to wishlist: `{"product_id": 1, "variant_id": 1}`.

### `DELETE /wishlist/{id}`
Remove from wishlist.

---

## 5. Addresses & Checkout

### `GET /addresses`
List user delivery addresses (HOME, WORK, OTHER).

### `POST /addresses`
Add new address with pin code, landmark, city, and state.

### `POST /coupons/apply`
Server-side coupon validation (checks minimum order amount, expiry, usage limits).
- **Payload**: `{"code": "WELCOME100", "cart_amount": 998.00}`

### `POST /payments/create`
Initiates Razorpay order. Computes amount in paise and returns `razorpay_order_id`.

### `POST /payments/verify`
Server-side HMAC SHA-256 signature verification and **Atomic MySQL Order Placement Transaction**:
1. Checks inventory availability (`FOR UPDATE`).
2. Deducts product stock.
3. Inserts `orders`, `order_items`, `order_addresses`, `payments`.
4. Initializes 6-milestone tracking history (`order_status_history`).
5. Clears user cart.
6. Returns Order Number and Expected Delivery Date.

---

## 6. Orders, Tracking & Reviews

### `GET /orders`
List user orders with status filtering (ALL, CONFIRMED, PACKED, SHIPPED, DELIVERED, CANCELLED).

### `GET /orders/{id}`
Full order detail with 6 Flipkart visual milestones:
1. `Order Placed`
2. `Order Confirmed`
3. `Packed by Seller`
4. `Shipped / In Transit`
5. `Out for Delivery`
6. `Delivered`

### `POST /orders/{id}/cancel`
Cancel order and automatically restock variant inventory.

### `POST /returns`
Submit return request with reason and photos.

### `POST /reviews`
Submit verified purchaser rating (1-5 stars), review title, and comment. Automatically recalculates product average rating.

---

## 7. Seller Portal API

- `POST /seller/login`: Seller portal login.
- `GET /seller/dashboard`: KPI metrics (Total sales, today's sales, pending orders, low stock).
- `GET /seller/products`: Seller's catalog.
- `POST /seller/products`: Create new product + variants + images + inventory.
- `GET /seller/inventory`: Inventory stock list.
- `PUT /seller/inventory/{id}`: Adjust stock quantity.
- `GET /seller/orders`: Orders containing seller's items.
- `PUT /seller/orders/{id}/status`: Transition status (`CONFIRMED` -> `PACKED` -> `SHIPPED` -> `DELIVERED`).

---

## 8. Admin Control Center API

- `POST /admin/login`: Administrator login.
- `GET /admin/dashboard`: Global revenue, total users, total sellers, sales overview chart.
- `GET /admin/users` & `PUT /admin/users/{id}`: User activation / deactivation.
- `GET /admin/sellers` & `PUT /admin/sellers/{id}/status`: Seller approval / suspension.
- `GET /admin/orders` & `PUT /admin/orders/{id}/status`: Order status oversight.
- `GET /admin/coupons` & `POST /admin/coupons`: Create and manage promo coupons.
- `GET /admin/reports`: Sales revenue and order analytics.
- `GET /admin/logs`: Audit trail of all administrative actions.
