# Flipkart Platform REST API Documentation

**Base URL**: `http://localhost/ecommerce_api/api` (or `http://localhost/ecomerce/backend/api`)  
**Authentication**: Bearer JWT Token in `Authorization: Bearer <access_token>` header.  
**Content-Type**: `application/json` (except `/upload` which requires `multipart/form-data`)

---

## 1. Authentication & Profile Management

### `POST /auth/register`
Register a new customer account.
- **Request Body**:
  ```json
  {
    "name": "Rahul Sharma",
    "email": "customer@gmail.com",
    "password": "Customer@12345",
    "phone": "9876543210"
  }
  ```
- **Validation**: Email uniqueness, strong password (min 8 chars), 10-digit mobile number.
- **Response** (HTTP 201):
  ```json
  {
    "success": true,
    "message": "Registration successful",
    "data": {
      "user": { "id": 4, "name": "Rahul Sharma", "email": "customer@gmail.com", "role": "CUSTOMER" },
      "tokens": { "access_token": "...", "refresh_token": "..." }
    }
  }
  ```

### `POST /auth/login`
Authenticate existing customer.
- **Request Body**:
  ```json
  {
    "email": "customer@gmail.com",
    "password": "Customer@12345"
  }
  ```
- **Response** (HTTP 200): Returns user profile and JWT tokens.

### `POST /auth/send-otp`
Generate a cryptographically secure 6-digit numeric OTP via `random_int(100000, 999999)` with 10-minute expiry.
- **Request Body**: `{"phone": "9876543210"}`
- **Response** (HTTP 200): Dispatches OTP via SMS gateway (simulated log in development).

### `POST /auth/verify-otp`
Verify SMS OTP and issue authenticated JWT tokens.
- **Request Body**: `{"phone": "9876543210", "otp": "492018"}`

### `POST /auth/refresh`
Issue fresh access token using long-lived refresh token.
- **Request Body**: `{"refresh_token": "..."}`

### `GET /auth/profile`
Retrieve authenticated user profile.
- **Headers**: `Authorization: Bearer <token>`

---

## 2. Catalog, Search & Categories

### `GET /home`
Aggregated Flipkart homepage feed containing promotional hero banners, category strip, flash deals, and bestsellers.

### `GET /categories`
Hierarchical category tree with subcategories and icons.

### `GET /products`
Product catalog with multi-facet filters.
- **Query Parameters**:
  - `category_id`: Filter by category ID
  - `brand_id`: Filter by brand ID
  - `min_price` & `max_price`: Numerical price bounds
  - `min_rating`: Minimum average rating
  - `sort`: `popularity`, `price_low`, `price_high`, `rating`, `newest`, `discount`
  - `page`: Page index (default: 1)
  - `limit`: Items per page (default: 20, max: 100)

### `GET /products/{id}`
Deep product details including image gallery, color/size variants, specifications, warranty, reviews, and inventory status.

### `GET /search?q={query}`
Parameterized server-side search querying titles, descriptions, brand names, and SKUs.

---

## 3. Cart, Wishlist & Addresses

### `GET /cart`
Retrieve customer cart items with real-time stock validation and subtotal/discount breakdown.
- **Headers**: `Authorization: Bearer <token>`

### `POST /cart/items`
Add product variant to cart.
- **Request Body**: `{"variant_id": 1, "quantity": 2}`

### `PUT /cart/items/{id}`
Update cart item quantity.
- **Request Body**: `{"quantity": 3}`

### `DELETE /cart/items/{id}`
Remove line item from cart.

### `GET /addresses`
Fetch customer saved delivery addresses.

### `POST /addresses`
Create a new delivery address.
- **Request Body**:
  ```json
  {
    "full_name": "Rahul Sharma",
    "phone": "9876543210",
    "pincode": "560034",
    "address_line1": "Flat 402, Green Glen Layout",
    "city": "Bengaluru",
    "state": "Karnataka",
    "type": "HOME",
    "is_default": 1
  }
  ```

---

## 4. Checkout, Payments & Webhooks

### `POST /coupons/apply`
Validate and preview discount coupon.
- **Request Body**: `{"code": "FLIPDEAL20", "cart_amount": 1996.00}`

### `POST /payments/create`
Initialize checkout and generate Razorpay payment order.
- **Request Body**: `{"address_id": 1, "coupon_code": "FLIPDEAL20"}`
- **Response**: Returns `razorpay_order_id`, `amount`, and `key_id`.

### `POST /payments/verify`
Execute atomic order placement with cryptographic HMAC-SHA256 signature verification.
- **Request Body (Online Payment)**:
  ```json
  {
    "address_id": 1,
    "payment_method": "RAZORPAY",
    "razorpay_order_id": "order_rzp_...",
    "razorpay_payment_id": "pay_rzp_...",
    "razorpay_signature": "hmac_sha256_hex_hash...",
    "coupon_code": "FLIPDEAL20"
  }
  ```
- **Request Body (Cash On Delivery)**:
  ```json
  {
    "address_id": 1,
    "payment_method": "COD",
    "coupon_code": "FREESHIP"
  }
  ```
- **Process**:
  - Validates `hash_hmac('sha256', "{$order_id}|{$payment_id}", RAZORPAY_SECRET)`.
  - Executes MySQL transaction with `SELECT ... FOR UPDATE` row locks.
  - Decrements variant stock and inserts immutable record in `inventory_transactions`.
  - Empties customer cart and commits.

### `POST /payments/webhook`
Asynchronous payment capture / failure callback from Razorpay.
- **Headers**: `X-Razorpay-Signature: <hmac_sha256_signature>`
- **Request Body**: Raw JSON webhook event payload from Razorpay.

---

## 5. Media Uploads

### `POST /upload`
Secure multipart image upload endpoint.
- **Headers**: `Authorization: Bearer <token>`, `Content-Type: multipart/form-data`
- **Form Data**: `image: <file>`
- **Validation**: Max 5MB size, MIME verified via `finfo_file`, image verified via `getimagesize`.
- **Response**: `{"success": true, "data": {"url": "http://localhost/.../uploads/..."}}`

---

## 6. Orders, Tracking & Returns

### `GET /orders`
Fetch customer order history with pagination (`page`, `limit`).

### `GET /orders/{id}`
Fetch single order details including 6-milestone tracking timeline:
- Order Placed
- Order Confirmed
- Packed by Seller
- Shipped / In Transit
- Out for Delivery
- Delivered

### `POST /returns`
Initiate return / refund request.
- **Request Body**: `{"order_id": 123, "reason": "Defective item", "comments": "..."}`

### `POST /reviews`
Submit product review.
- **Request Body**: `{"product_id": 1, "rating": 5, "title": "Great quality", "comment": "..."}`

---

## 7. Seller Hub APIs

### `POST /seller/login`
Merchant login (`seller@celvas.in`).

### `GET /seller/dashboard`
Live KPIs: Total Revenue, Total Orders, Total SKUs, Seller Rating.

### `GET /seller/inventory`
Live stock and SKU management.

### `PUT /seller/inventory/{id}`
Adjust stock level with automated audit logging to `inventory_transactions`.
- **Request Body**: `{"quantity": 50, "reason": "STOCK_ADJUSTMENT"}`

### `GET /seller/orders`
Orders containing items sold by the authenticated seller.

### `PUT /seller/orders/{id}/status`
Update order item status (`PACKED`, `SHIPPED`). Scoped strictly to the seller's items (IDOR protected).

### `GET /seller/returns`
Return requests pending seller acknowledgement.

---

## 8. Admin Control Center APIs

### `POST /admin/login`
Platform administrator login (`admin@flipkart.local`).

### `GET /admin/dashboard`
Aggregated platform metrics: Gross Merchandise Value (GMV), Total Registered Users, Total Orders, Total Products.

### `GET /admin/orders?page=1&limit=20`
Platform-wide order inspection with pagination.

### `GET /admin/users?page=1&limit=20`
Registered user directory.

### `GET /admin/returns`
Platform-wide return requests.

### `PUT /admin/returns/{id}/status`
Approve or reject customer return requests.

### `GET /admin/refunds`
Inspect issued refunds.
