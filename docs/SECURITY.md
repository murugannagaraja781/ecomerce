# Flipkart Platform - Security Architecture & Hardening Guide

## 1. Threat Model & Security Posture
The platform adopts a Zero-Trust, Defense-in-Depth architectural pattern protecting against OWASP Top 10 web vulnerabilities, unauthorized privilege escalation, cross-seller data tampering (IDOR), financial manipulation, and automated credential stuffing.

---

## 2. Authentication & Session Management
- **JWT Standard**: Custom HS256 algorithm with cryptographic HMAC-SHA256 signature verification (`hash_equals()` constant-time comparison).
- **Token Expiry**: Short-lived Access Tokens (1 hour / 3600s), paired with cryptographically random refresh tokens (30 days / 2,592,000s).
- **OTP Security**: Cryptographically secure 6-digit numeric OTP generation via `random_int(100000, 999999)` with 10-minute expiry timestamps validated at the database level (`otp_expires_at >= NOW()`).
- **No Backdoors**: Zero hardcoded bypasses or static OTP fallbacks.

---

## 3. Authorization & Access Control (RBAC)
- **Role Hierarchy**: Strict three-tier separation (`CUSTOMER`, `SELLER`, `ADMIN`).
- **Customer Restrictions**: Forbidden (HTTP 403) from invoking Seller Hub or Admin Control Center APIs.
- **Seller Isolation & IDOR Defense**:
  - All seller database queries enforce `seller_id = ?` scoping.
  - Multi-seller order updates restrict status modifications strictly to line items belonging to the authenticated merchant. Cross-seller order tampering triggers HTTP 403.
- **Admin Boundaries**: Only users authenticated as `ADMIN` with valid active credentials can access platform GMV, user management, and payout authorizations.

---

## 4. Financial & Payment Integrity
- **Double-Ledger Checkout**: Client-submitted totals are ignored; prices, coupon eligibility, shipping tiers, and discounts are strictly recalculated server-side from `flipkartdb` database tables.
- **Razorpay Signature Verification**: Online payments require strict HMAC-SHA256 signature verification over `{$razorpayOrderId}|{$razorpayPaymentId}` with `hash_equals()`.
- **Atomic MySQL Transactions**: Order placement uses `START TRANSACTION` / `COMMIT` / `ROLLBACK` with `SELECT ... FOR UPDATE` row locks to prevent stock race conditions.
- **Inventory Audit Trail**: Every inventory modification logs an immutable record in `inventory_transactions` with previous and new stock quantities.

---

## 5. Input Validation & Injection Defenses
- **SQL Injection Prevention**: 100% of dynamic database queries use PDO prepared statements with parameterized inputs. Emulated prepares are disabled (`PDO::ATTR_EMULATE_PREPARES => false`).
- **Cross-Site Scripting (XSS)**: Inputs sanitized via `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')` and strip tags on user reviews.
- **Strict CORS Origin Whitelist**: Reflected origins eliminated; only explicitly whitelisted origins (`localhost`, `127.0.0.1`, production domains) receive credentials headers.

---

## 6. Secure Media Upload Pipeline
- **MIME Inspection**: Verified via `finfo_file(..., FILEINFO_MIME_TYPE)` strictly against allowed types (`image/jpeg`, `image/png`, `image/webp`).
- **Image Integrity Check**: Validated via `getimagesize()` to block polyglot script payloads.
- **Randomized Naming**: Files saved using `bin2hex(random_bytes(16))` + safe extension.
- **Execution Prevention**: Uploads folder protected with `.htaccess` disabling PHP engine execution:
  ```apache
  RemoveHandler .php .phtml .php3 .php4 .php5 .php7 .php8 .phps .cgi .pl .py
  <FilesMatch "(?i)\.(php|phtml|php3|php4|php5|php7|php8|phps|cgi|pl|py|sh|bat)$">
      Order Deny,Allow
      Deny from all
  </FilesMatch>
  ```

---

## 7. Security Test Suite Results
All 20 negative security test cases verified passing (`tests/test_security_negative.php`):
- Expired / Malformed / Tampered JWT: **HTTP 401 PASS**
- Role Escalation (Customer -> Admin/Seller, Seller -> Admin): **HTTP 403 PASS**
- IDOR Cross-Seller Tampering: **HTTP 403 PASS**
- Negative / Zero / Overrun Stock Quantities: **HTTP 422 PASS**
- Price Tampering: **HTTP 400 Enforced PASS**
- Fake / Expired / Below-Min Coupons: **HTTP 400 PASS**
- Razorpay Signature Tampering: **HTTP 400 PASS**
- SQL Injection in Search: **Safe Execution PASS**
- XSS in Reviews: **Sanitized PASS**
- Path Traversal & PHP Upload Blocks: **HTTP 422 PASS**
