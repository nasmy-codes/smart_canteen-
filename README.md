# SmartCanteen — Online Canteen Management System

Pure **HTML + CSS + PHP** (no frameworks, no JS libraries). Built on raw PDO/MySQL.

## Features implemented

**Customer side**
- User Registration (`pages/register.php`)
- User Login / Logout with hashed passwords + sessions (`pages/login.php`, `pages/logout.php`)
- View Food Menu, grouped by category, pulled live from the database (`pages/menu.php`)
- Add to Cart / update quantity / remove, stored in the PHP session (`pages/cart.php`, `pages/add_to_cart.php`)
- Place Orders — writes a real order + order_items rows in one DB transaction (`pages/place_order.php`)
- Order confirmation screen with a generated Order ID (`pages/order_confirmation.php`)
- View Orders — list + search by Order ID + full order details (`pages/my_orders.php`, `pages/order_details.php`)

**Admin side** (`/admin`, protected by role-based session check)
- Admin Login — same login form, redirects by role
- Dashboard with live stats (menu items, total orders, pending orders, today's sales)
- Manage Food Items — add / edit / delete / mark unavailable (`admin/manage_items.php`)
- Manage Orders — view all orders, update status (Pending → Preparing → Ready for Pickup → Completed / Cancelled) (`admin/manage_orders.php`)
- Sales Reports — date-range revenue chart + top-selling items (`admin/sales_report.php`)

## Folder structure

```
smart-canteen/
├── index.php               Home page
├── css/                     style.css (shared), home.css, menu.css, cart.css, admin.css
├── images/                  food photos
├── includes/                db.php, auth.php, header.php, footer.php, admin_sidebar.php
├── pages/                   login, register, logout, menu, cart, checkout, orders
├── admin/                   dashboard, manage_items, manage_orders, sales_report
└── sql/schema.sql           database schema + sample data
```

## Setup (XAMPP / WAMP / MAMP)

1. Copy the `smart-canteen` folder into your server's web root
   (e.g. `C:\xampp\htdocs\smart-canteen`).
2. Start Apache and MySQL from your XAMPP/WAMP control panel.
3. Open **phpMyAdmin**, click *Import*, and import `sql/schema.sql`.
   This creates the `smart_canteen` database with all 4 tables and some sample menu items.
4. If your MySQL username/password differ from the XAMPP defaults, edit
   `includes/db.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'smart_canteen');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```
5. Visit `http://localhost/smart-canteen/` in your browser.

## Demo accounts (from the sample data)

| Role     | Username | Password    |
|----------|----------|-------------|
| Admin    | admin    | admin123    |
| Customer | customer | customer123 |

Or just click **Register** to create your own customer account.

## Notes / design decisions

- Passwords are stored with PHP's `password_hash()` / verified with `password_verify()` — never in plain text.
- All SQL uses PDO **prepared statements** — no string-concatenated queries, no SQL injection risk.
- All dynamic output is passed through the `h()` helper (`htmlspecialchars`) to prevent XSS.
- The cart lives in `$_SESSION['cart']` (an array of `food_item_id => quantity`); prices are always re-read from the database at checkout so nothing can be tampered with from the browser.
- Order placement runs inside a database transaction (`beginTransaction` / `commit` / `rollBack`) so an order and its line items are always written together or not at all.
- Order codes look like `ORD20260007` — generated from the year + the order's auto-increment ID, so they're always unique.
- Admin pages are protected by `require_admin()`, which checks `$_SESSION['role']`; customer-only pages use `require_login()`.
