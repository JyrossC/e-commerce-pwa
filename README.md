# 🛍 NovaMart — E-Commerce Progressive Web Application

> A fully featured, secure, and responsive PHP + MySQL PWA e-commerce platform built by **Jaiross Steven**.

---

## 🚀 Quick Start (XAMPP)

### Prerequisites
- [XAMPP](https://www.apachefriends.org/) with PHP 8.0+ and MySQL 8.0+
- [Composer](https://getcomposer.org/) (for PDF report generation)

---

### Step 1 — Copy Files

Place the entire `ecommerce-pwa/` folder inside your XAMPP `htdocs` directory:

```
C:\xampp\htdocs\ecommerce-pwa\
```

---

### Step 2 — Import the Database

1. Start **XAMPP** (start Apache + MySQL)
2. Open **phpMyAdmin** → `http://localhost/phpmyadmin`
3. Create a new database named: **`novamart_db`**
4. Click **Import** → select `database/schema.sql` → click **Go**
5. Click **Import** again → select `database/seed.sql` → click **Go**

---

### Step 3 — Enable Apache mod_rewrite

In XAMPP, `mod_rewrite` is usually already enabled. If the page returns HTTP 500:

1. Open `C:\xampp\apache\conf\httpd.conf`
2. Find `#LoadModule rewrite_module modules/mod_rewrite.so`
3. Remove the `#` and save
4. Also find `AllowOverride None` under the `htdocs` directory section and change to `AllowOverride All`
5. Restart Apache

---

### Step 4 — Install PHP Dependencies (for PDF generation)

Open **Command Prompt** in the project folder:

```powershell
cd C:\xampp\htdocs\ecommerce-pwa
composer install
```

This installs **mPDF** for automated PDF report generation.

---

### Step 5 — Open the Application

| Page | URL |
|------|-----|
| 🛒 **Shop** (main entry) | http://localhost/ecommerce-pwa/ |
| 📊 **Admin Dashboard** | http://localhost/ecommerce-pwa/pages/admin/dashboard.php |
| 📄 **View Report** | http://localhost/ecommerce-pwa/report/report.php |
| 📥 **Download PDF** | http://localhost/ecommerce-pwa/report/report.php?generate=1 |

---

## 🔑 Credentials

| Role | Email | Password |
|------|-------|----------|
| 👑 Admin | `admin@novamart.com` | `Admin@1234` |
| 🧑 Customer | `demo@novamart.com` | `Demo@1234` |

---

## 📁 Key Files

| File | Purpose |
|------|---------|
| `config/config.php` | Database credentials, session config |
| `config/database.php` | PDO singleton class |
| `database/schema.sql` | Full MySQL schema (7 tables) |
| `database/seed.sql` | Demo data (users, products, orders) |
| `includes/auth.php` | Session auth helpers |
| `includes/csrf.php` | CSRF token management |
| `sw.js` | PWA Service Worker |
| `manifest.json` | PWA Web App Manifest |
| `report/report.php` | Generates Jaiross-Steven.pdf |

---

## 🔒 Security Features

- ✅ **SQL Injection** — all queries use PDO prepared statements
- ✅ **XSS** — all output encoded with `htmlspecialchars()`
- ✅ **CSRF** — synchronizer token pattern on every form/AJAX request
- ✅ **Passwords** — bcrypt hashed with cost 12 (`password_hash`)
- ✅ **Session Fixation** — `session_regenerate_id(true)` on login
- ✅ **HttpOnly Cookies** — JS cannot access session cookie
- ✅ **Directory blocking** — `.htaccess` blocks access to `config/`, `includes/`, `database/`

---

## 📦 Features

| Feature | Status |
|---------|--------|
| Product browsing with search, filters, sort | ✅ |
| Product detail with reviews | ✅ |
| Shopping cart | ✅ |
| Checkout and order placement | ✅ |
| My Orders page | ✅ |
| Wishlist | ✅ |
| User profile | ✅ |
| Admin dashboard with stats | ✅ |
| Admin product CRUD with image upload | ✅ |
| Admin categories management | ✅ |
| Admin orders management | ✅ |
| Admin users listing | ✅ |
| Service Worker (offline caching) | ✅ |
| PWA install prompt | ✅ |
| Dark/Light theme toggle | ✅ |
| Professional PDF report | ✅ |

---

## 🛠 Config Options (`config/config.php`)

```php
define('DB_HOST',     'localhost');
define('DB_NAME',     'novamart_db');
define('DB_USER',     'root');
define('DB_PASS',     '');          // blank for XAMPP default
define('APP_URL',     'http://localhost/ecommerce-pwa');
define('APP_NAME',    'NovaMart');
define('VAT_RATE',    0.16);         // 16% VAT
```

---

## 📄 Generating the PDF Report

```
http://localhost/ecommerce-pwa/report/report.php?generate=1
```

The file will automatically download as **`Jaiross-Steven.pdf`**.

**Alternative** (without Composer): Open `report/report.php` in browser → `File → Print → Save as PDF` → name it `Jaiross-Steven.pdf`.

---

## 🏗 Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | PHP 8.x |
| Database | MySQL 8.x via PDO |
| Frontend | HTML5, CSS3, Vanilla JS |
| PWA | Service Worker + Web App Manifest |
| PDF | mPDF 8.x (Composer) |
| Server | Apache (XAMPP) |

---

*© 2026 Jaiross Steven — NovaMart E-Commerce PWA*
