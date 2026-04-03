<?php
/**
 * NovaMart — Professional Report Page
 * Generates Jaiross-Steven.pdf via mPDF
 * Access: http://localhost/ecommerce-pwa/report/report.php
 */

define('IN_REPORT', true);
require_once dirname(__DIR__) . '/config/config.php';

// Load mPDF via Composer
$composerAutoload = dirname(__DIR__) . '/vendor/autoload.php';
$mPDFAvailable = file_exists($composerAutoload);
if ($mPDFAvailable) {
    require_once $composerAutoload;
}

// PDF generation trigger
if (isset($_GET['generate']) && $mPDFAvailable) {
    try {
        ob_start();
        include __FILE__;  // capture HTML output below
        $html = ob_get_clean();

        $mpdf = new \Mpdf\Mpdf([
            'mode'          => 'utf-8',
            'format'        => 'A4',
            'margin_top'    => 20,
            'margin_bottom' => 20,
            'margin_left'   => 20,
            'margin_right'  => 20,
            'default_font'  => 'dejavusans',
        ]);
        $mpdf->SetTitle('Jaiross-Steven — NovaMart E-Commerce PWA Report');
        $mpdf->SetAuthor('Jaiross Steven');
        $mpdf->SetCreator('NovaMart PWA System');

        // Strip the "generate" notice from HTML for clean PDF
        $html = preg_replace('/<div class="gen-notice".*?<\/div>/s', '', $html);
        $mpdf->WriteHTML($html);
        $mpdf->Output('Jaiross-Steven.pdf', 'D'); // Force download
        exit;
    } catch (Exception $e) {
        die('<p style="color:red;font-family:sans-serif;">PDF generation failed: ' . htmlspecialchars($e->getMessage()) . '<br>Make sure you ran <code>composer install</code> in the project root.</p>');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Jaiross-Steven — NovaMart E-Commerce PWA Report</title>
<style>
/* ── PDF/Print Stylesheet ── */
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Segoe UI', Arial, Helvetica, sans-serif; font-size: 11pt; color: #1e293b; line-height: 1.7; background: #fff; }

/* Cover Page */
.cover { page-break-after: always; min-height: 90vh; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; padding: 60px 40px; background: linear-gradient(135deg, #0D0E1A 0%, #1E1B4B 100%); color: #fff; }
.cover-logo { font-size: 56px; margin-bottom: 20px; }
.cover-brand { font-size: 42pt; font-weight: 900; background: linear-gradient(135deg, #6C63FF, #FF6584); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; margin-bottom: 12px; }
.cover-subtitle { font-size: 16pt; color: #94A3B8; margin-bottom: 40px; }
.cover-divider { width: 80px; height: 4px; background: linear-gradient(135deg, #6C63FF, #FF6584); border-radius: 2px; margin: 0 auto 40px; }
.cover-title { font-size: 22pt; font-weight: 700; color: #fff; margin-bottom: 12px; }
.cover-desc { font-size: 12pt; color: #CBD5E1; max-width: 500px; margin: 0 auto 60px; line-height: 1.8; }
.cover-meta { display: flex; gap: 40px; justify-content: center; flex-wrap: wrap; }
.cover-meta-item { text-align: center; }
.cover-meta-item strong { display: block; font-size: 11pt; color: #fff; }
.cover-meta-item span { font-size: 9pt; color: #94A3B8; }

/* Page chrome */
.page-section { page-break-before: always; padding: 40px; }
.page-section:first-of-type { page-break-before: avoid; }

/* Typography */
h1.chapter { font-size: 22pt; font-weight: 900; color: #6C63FF; border-bottom: 3px solid #6C63FF; padding-bottom: 10px; margin-bottom: 30px; }
h2.section  { font-size: 16pt; font-weight: 800; color: #1e293b; margin: 28px 0 14px; }
h3.sub      { font-size: 13pt; font-weight: 700; color: #334155; margin: 20px 0 10px; }
h4.subsub   { font-size: 11pt; font-weight: 700; color: #475569; margin: 14px 0 8px; }
p           { margin-bottom: 12px; }
ul, ol      { margin: 0 0 14px 24px; }
li          { margin-bottom: 6px; }
code        { font-family: Consolas, 'Courier New', monospace; background: #f1f5f9; padding: 2px 6px; border-radius: 4px; font-size: 10pt; color: #6C63FF; }
pre         { background: #0f172a; color: #e2e8f0; padding: 18px; border-radius: 8px; font-size: 9pt; font-family: Consolas, monospace; margin: 14px 0; overflow: auto; }
pre code    { background: none; color: inherit; padding: 0; }
strong      { font-weight: 700; }
a           { color: #6C63FF; }

/* Tables */
table       { width: 100%; border-collapse: collapse; margin: 14px 0; font-size: 10pt; }
th          { background: #6C63FF; color: #fff; padding: 10px 14px; text-align: left; font-weight: 700; }
td          { padding: 9px 14px; border-bottom: 1px solid #e2e8f0; }
tr:nth-child(even) td { background: #f8fafc; }
.table-caption { font-size: 9pt; color: #64748b; margin-bottom: 6px; font-style: italic; }

/* Info boxes */
.info-box   { background: #EFF6FF; border-left: 5px solid #3B82F6; padding: 16px 20px; border-radius: 0 8px 8px 0; margin: 16px 0; }
.warn-box   { background: #FFFBEB; border-left: 5px solid #F59E0B; padding: 16px 20px; border-radius: 0 8px 8px 0; margin: 16px 0; }
.success-box{ background: #ECFDF5; border-left: 5px solid #10B981; padding: 16px 20px; border-radius: 0 8px 8px 0; margin: 16px 0; }

/* Architecture diagrams (text-based) */
.diagram    { background: #0f172a; color: #e2e8f0; padding: 24px; border-radius: 12px; font-family: Consolas, monospace; font-size: 9pt; line-height: 1.9; margin: 16px 0; }

/* Feature tag */
.tag        { display: inline-block; background: rgba(108,99,255,0.15); color: #6C63FF; padding: 3px 10px; border-radius: 100px; font-size: 9pt; font-weight: 700; margin: 2px; }
.tag-green  { background: rgba(16,185,129,0.15); color: #059669; }
.tag-red    { background: rgba(239,68,68,0.15); color: #DC2626; }
.tag-yellow { background: rgba(245,158,11,0.15); color: #D97706; }

/* Two column layout */
.two-col    { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 16px 0; }
.col-card   { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; }
.col-card h4{ font-size: 11pt; font-weight: 700; color: #6C63FF; margin-bottom: 10px; }

/* Screen mock */
.screen-desc{ background: #f8fafc; border: 2px dashed #cbd5e1; border-radius: 12px; padding: 20px; text-align: center; color: #64748b; font-size: 10pt; margin: 16px 0; }
.screen-desc strong { display: block; font-size: 12pt; color: #334155; margin-bottom: 6px; }

/* Print notice */
.gen-notice { background: #6C63FF; color: #fff; padding: 16px 24px; text-align: center; font-size: 11pt; position: fixed; top: 0; left: 0; right: 0; z-index: 999; }
.gen-notice a { color: #fff; font-weight: 700; text-decoration: underline; }

@media print {
  .gen-notice { display: none; }
  body { font-size: 10pt; }
  .cover { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
  pre, .diagram { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
  th { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
}
</style>
</head>
<body>

<?php if (!isset($_GET['generate']) && !$mPDFAvailable): ?>
<div class="gen-notice">
  💡 To generate the PDF: run <code style="background:rgba(255,255,255,0.2);padding:2px 8px;border-radius:4px;">composer install</code> in the project root, then visit
  <a href="?generate=1">?generate=1</a> — or use your browser's <strong>File → Print → Save as PDF</strong>.
</div>
<?php elseif (!isset($_GET['generate']) && $mPDFAvailable): ?>
<div class="gen-notice">
  ✅ mPDF is installed! <a href="?generate=1">Click here to download Jaiross-Steven.pdf</a> &nbsp;|&nbsp; Or use <strong>File → Print → Save as PDF</strong>
</div>
<?php endif; ?>

<!-- ══════════════════════════════════════════════════════════════════
     COVER PAGE
     ══════════════════════════════════════════════════════════════════ -->
<div class="cover">
  <div class="cover-logo">🛍</div>
  <div class="cover-brand">NovaMart</div>
  <div class="cover-subtitle">E-Commerce Progressive Web Application</div>
  <div class="cover-divider"></div>
  <div class="cover-title">Final Technical Report</div>
  <p class="cover-desc">
    A comprehensive technical and business documentation of the NovaMart
    full-stack PWA e-commerce system — covering architecture, implementation,
    security, testing, and future roadmap.
  </p>
  <div class="cover-meta">
    <div class="cover-meta-item"><strong>Author</strong><span>Jaiross Steven</span></div>
    <div class="cover-meta-item"><strong>Date</strong><span><?= date('d F Y') ?></span></div>
    <div class="cover-meta-item"><strong>Version</strong><span>1.0.0</span></div>
    <div class="cover-meta-item"><strong>Platform</strong><span>PHP 8 · MySQL · PWA</span></div>
  </div>
</div>

<!-- ══════════════════════════════════════════════════════════════════
     1. INTRODUCTION
     ══════════════════════════════════════════════════════════════════ -->
<div class="page-section">

<h1 class="chapter">1. Introduction</h1>

<h2 class="section">1.1 Problem Description</h2>
<p>
  The modern retail consumer increasingly shops online, yet many small and medium enterprises (SMEs)
  in emerging markets lack access to affordable, full-featured e-commerce platforms. Existing solutions
  are either expensive subscription SaaS platforms or complex frameworks requiring significant technical expertise.
  Furthermore, mobile internet penetration in regions like sub-Saharan Africa means that any solution
  must be optimized for constrained mobile networks and diverse devices.
</p>
<p>
  This project addresses these challenges by building <strong>NovaMart</strong> — a complete, self-hosted,
  open-source e-commerce Progressive Web Application (PWA) built on widely-available, cost-free technologies:
  PHP, MySQL, HTML5, CSS3, and JavaScript.
</p>

<h2 class="section">1.2 Motivation</h2>
<p>The key motivations for building this system are:</p>
<ul>
  <li><strong>Accessibility</strong>: A PWA works offline and can be "installed" on any device without an app store, making it accessible to users on low-end smartphones.</li>
  <li><strong>Cost-effectiveness</strong>: Built on LAMP stack (Linux/Apache/MySQL/PHP) — the most widely deployed server environment globally — with minimal hosting requirements.</li>
  <li><strong>Security</strong>: Demonstrating enterprise-grade security (CSRF, SQL injection prevention, XSS protection, bcrypt passwords) in a PHP application.</li>
  <li><strong>Learning</strong>: Demonstrating full-stack development competency across frontend (HTML/CSS/JS/PWA), backend (PHP OOP, REST APIs), and database design (MySQL schema design, normalized tables, foreign keys).</li>
  <li><strong>Real-world applicability</strong>: The system is feature-complete for a real business — admin panel, product management, order management, customer accounts, reviews, and wishlist.</li>
</ul>

<h2 class="section">1.3 Project Objectives</h2>
<ul>
  <li>✅ Build a complete, working e-commerce PWA with full CRUD operations</li>
  <li>✅ Implement a secure REST API backend in PHP using PDO prepared statements</li>
  <li>✅ Design a normalized MySQL relational database with proper foreign keys and indexes</li>
  <li>✅ Implement session-based authentication with CSRF, XSS, and SQL injection protection</li>
  <li>✅ Deliver a mobile-first, responsive UI with installable PWA capabilities (Service Worker, Web App Manifest)</li>
  <li>✅ Provide a complete admin panel for product, order, category, and user management</li>
  <li>✅ Document the system in a professional technical report</li>
</ul>

</div>

<!-- ══════════════════════════════════════════════════════════════════
     2. SYSTEM REQUIREMENTS
     ══════════════════════════════════════════════════════════════════ -->
<div class="page-section">

<h1 class="chapter">2. System Requirements</h1>

<h2 class="section">2.1 Functional Requirements</h2>
<table>
  <tr><th>#</th><th>Requirement</th><th>Description</th><th>Status</th></tr>
  <tr><td>FR-01</td><td>User Registration</td><td>Customers can create accounts with name, email, and password</td><td><span class="tag tag-green">✅ Done</span></td></tr>
  <tr><td>FR-02</td><td>User Login / Logout</td><td>Secure session-based authentication with CSRF protection</td><td><span class="tag tag-green">✅ Done</span></td></tr>
  <tr><td>FR-03</td><td>Product Browsing</td><td>Browse products with category filter, search, sort, pagination</td><td><span class="tag tag-green">✅ Done</span></td></tr>
  <tr><td>FR-04</td><td>Product Detail</td><td>Full product page with images, description, reviews, related products</td><td><span class="tag tag-green">✅ Done</span></td></tr>
  <tr><td>FR-05</td><td>Shopping Cart</td><td>Add/update/remove items, quantity control, shipping/tax calculation</td><td><span class="tag tag-green">✅ Done</span></td></tr>
  <tr><td>FR-06</td><td>Checkout</td><td>Collect shipping details, payment method, place order</td><td><span class="tag tag-green">✅ Done</span></td></tr>
  <tr><td>FR-07</td><td>Order Management</td><td>View order history and order details</td><td><span class="tag tag-green">✅ Done</span></td></tr>
  <tr><td>FR-08</td><td>Product Reviews</td><td>Authenticated users submit star ratings and comments</td><td><span class="tag tag-green">✅ Done</span></td></tr>
  <tr><td>FR-09</td><td>Wishlist</td><td>Add/remove products from a personal wishlist</td><td><span class="tag tag-green">✅ Done</span></td></tr>
  <tr><td>FR-10</td><td>Admin: Products CRUD</td><td>Create, read, update, delete products with image upload</td><td><span class="tag tag-green">✅ Done</span></td></tr>
  <tr><td>FR-11</td><td>Admin: Categories</td><td>Create and delete product categories</td><td><span class="tag tag-green">✅ Done</span></td></tr>
  <tr><td>FR-12</td><td>Admin: Orders</td><td>View all orders, update status and payment status</td><td><span class="tag tag-green">✅ Done</span></td></tr>
  <tr><td>FR-13</td><td>Admin: Dashboard</td><td>Revenue, orders, products, and customer statistics</td><td><span class="tag tag-green">✅ Done</span></td></tr>
  <tr><td>FR-14</td><td>Admin: Users</td><td>View all registered customers and their account details</td><td><span class="tag tag-green">✅ Done</span></td></tr>
  <tr><td>FR-15</td><td>PWA Install</td><td>Installable on mobile/desktop via Web App Manifest</td><td><span class="tag tag-green">✅ Done</span></td></tr>
  <tr><td>FR-16</td><td>Offline Mode</td><td>Service Worker caches assets for basic offline browsing</td><td><span class="tag tag-green">✅ Done</span></td></tr>
</table>

<h2 class="section">2.2 Non-Functional Requirements</h2>
<table>
  <tr><th>#</th><th>Requirement</th><th>Target</th><th>Implementation</th></tr>
  <tr><td>NF-01</td><td>Mobile-first design</td><td>Works on screens ≥ 320px</td><td>CSS mobile-first breakpoints, bottom nav, touch-friendly targets</td></tr>
  <tr><td>NF-02</td><td>Performance</td><td>Page loads &lt; 3s on 3G</td><td>Gzip compression, browser caching (.htaccess), lazy image loading, Service Worker</td></tr>
  <tr><td>NF-03</td><td>Security</td><td>OWASP Top 10 mitigated</td><td>PDO prepared statements, CSRF tokens, bcrypt, session security, CSP headers</td></tr>
  <tr><td>NF-04</td><td>Maintainability</td><td>Clean, commented code</td><td>Separation of config, includes, API, pages; OOP helpers</td></tr>
  <tr><td>NF-05</td><td>Scalability</td><td>Handles 100+ concurrent users</td><td>Stateless API design, database indexes, PDO connection pooling options</td></tr>
  <tr><td>NF-06</td><td>Availability</td><td>Offline capable</td><td>PWA Service Worker with cache-first strategy</td></tr>
  <tr><td>NF-07</td><td>Usability</td><td>Intuitive with &lt; 3 clicks to buy</td><td>Hero CTA → Product → Add to Cart → Checkout (3 steps)</td></tr>
</table>

</div>

<!-- ══════════════════════════════════════════════════════════════════
     3. SYSTEM ARCHITECTURE
     ══════════════════════════════════════════════════════════════════ -->
<div class="page-section">

<h1 class="chapter">3. System Architecture</h1>

<h2 class="section">3.1 Overall System Design</h2>
<p>
  NovaMart follows a classic <strong>Client–Server architecture</strong> with a clear separation between:
</p>
<ul>
  <li><strong>Presentation Layer</strong>: PHP-rendered HTML pages with Vanilla JS for dynamic UI interactions</li>
  <li><strong>Application Layer</strong>: PHP REST API endpoints (JSON-based) handling business logic</li>
  <li><strong>Data Layer</strong>: MySQL relational database accessed exclusively through PDO prepared statements</li>
</ul>
<p>
  A <strong>Progressive Web App (PWA)</strong> layer sits above the frontend, providing a Service Worker
  for offline caching and asset management, and a Web App Manifest for installability.
</p>

<h2 class="section">3.2 Client–Server Architecture Diagram</h2>
<div class="diagram">
┌──────────────────────────────────────────────────────────────────┐
│                       CLIENT (Browser / PWA)                      │
│                                                                   │
│  ┌─────────────┐  ┌──────────────┐  ┌────────────────────────┐  │
│  │  HTML Pages  │  │  Vanilla JS   │  │  Service Worker (PWA)  │  │
│  │  (PHP-rendered) │  │ (Fetch API)  │  │  Cache + Offline mode  │  │
│  └──────┬──────┘  └──────┬───────┘  └───────────┬────────────┘  │
│         └────────────────┴──────────────────────┘               │
└────────────────────────── HTTPS ────────────────────────────────┘
                               │
                    ┌──────────▼──────────┐
                    │   Apache Web Server   │
                    │   .htaccess rewriting │
                    │   Security headers    │
                    └──────────┬──────────┘
                               │
           ┌───────────────────┴──────────────────────┐
           │                                          │
   ┌───────▼────────┐                     ┌──────────▼──────────┐
   │  PHP Pages     │                     │   PHP REST API        │
   │  /pages/*.php  │                     │   /api/*/*.php        │
   │  Server-render │                     │   JSON responses      │
   └───────┬────────┘                     └──────────┬──────────┘
           │                                          │
           └──────────── PHP Helpers ─────────────────┘
                    ┌──────────────────────┐
                    │  includes/auth.php    │
                    │  includes/csrf.php    │
                    │  includes/validator.php│
                    │  includes/response.php │
                    └──────────┬───────────┘
                               │
                    ┌──────────▼──────────┐
                    │  config/database.php  │
                    │  PDO Singleton        │
                    │  Prepared Statements  │
                    └──────────┬──────────┘
                               │
                    ┌──────────▼──────────┐
                    │      MySQL 8.x        │
                    │   novamart_db         │
                    │  7 tables, FK indexes │
                    └─────────────────────┘
</div>

<h2 class="section">3.3 Database Schema (ERD)</h2>
<p>The database consists of <strong>7 normalized tables</strong> with referential integrity enforced by foreign keys:</p>
<div class="diagram">
USERS (id PK, name, email UNIQUE, password, role, phone, address, avatar, is_active, created_at, updated_at)
  │
  ├──┬── CART (id PK, user_id FK→users, product_id FK→products, quantity)
  │  │
  │  ├── ORDERS (id PK, user_id FK→users, order_number UNIQUE, subtotal, shipping_cost,
  │  │            tax, total, status ENUM, payment_method, payment_status,
  │  │            shipping_name, shipping_email, shipping_address, ...)
  │  │       └── ORDER_ITEMS (id PK, order_id FK→orders, product_id FK→products,
  │  │                        product_name, product_image, quantity, price, subtotal)
  │  │
  │  ├── REVIEWS (id PK, user_id FK→users, product_id FK→products,
  │  │            rating CHECK(1-5), title, comment, is_approved)
  │  │
  │  └── WISHLIST (id PK, user_id FK→users, product_id FK→products)
  │
CATEGORIES (id PK, name, slug UNIQUE, description, image, sort_order, is_active)
  │
  └── PRODUCTS (id PK, category_id FK→categories, name, slug UNIQUE, description,
                price, sale_price, stock, sku, image, is_featured, is_active,
                created_at, updated_at)
</div>

<h4 class="subsub">Key Design Decisions:</h4>
<ul>
  <li><strong>Cascading deletes</strong>: Deleting a user cascades to their cart, orders, reviews, and wishlist</li>
  <li><strong>Snapshot pricing</strong>: <code>order_items.price</code> and <code>product_name</code> store values at purchase time, immune to future product changes</li>
  <li><strong>Soft-deletable products</strong>: <code>is_active</code> flag allows hiding products without losing order history</li>
  <li><strong>uuid-like slugs</strong>: Products and categories use URL-safe slugs for SEO-friendly routing</li>
  <li><strong>16% VAT</strong>: Tax is calculated and stored per order for compliance</li>
</ul>

<h2 class="section">3.4 Authentication Mechanism</h2>
<div class="two-col">
  <div class="col-card">
    <h4>Session-Based Auth Flow</h4>
    <ol>
      <li>User submits credentials via POST (HTTPS)</li>
      <li>Server verifies with <code>password_verify()</code></li>
      <li><code>session_regenerate_id(true)</code> prevents fixation</li>
      <li>User data stored in <code>$_SESSION</code></li>
      <li>Session ID sent via HttpOnly, SameSite=Strict cookie</li>
      <li>Every 30 min, session ID is re-regenerated</li>
    </ol>
  </div>
  <div class="col-card">
    <h4>CSRF Protection</h4>
    <ol>
      <li>On page load, <code>CSRF::token()</code> generates a 40-byte hex token stored in <code>$_SESSION</code></li>
      <li>Every HTML form includes a hidden token field</li>
      <li>Every AJAX request sends token in <code>X-CSRF-Token</code> header</li>
      <li><code>CSRF::validate()</code> uses <code>hash_equals()</code> for timing-safe comparison</li>
      <li>Mismatch returns HTTP 403 immediately</li>
    </ol>
  </div>
</div>

<h2 class="section">3.5 Security Measures</h2>
<table>
  <tr><th>Threat</th><th>Countermeasure</th><th>Implementation</th></tr>
  <tr><td>SQL Injection</td><td>Parameterized queries</td><td>All DB queries use PDO prepared statements. No string interpolation in SQL.</td></tr>
  <tr><td>Cross-Site Scripting (XSS)</td><td>Output encoding</td><td><code>htmlspecialchars()</code> on all dynamic output. Content-Security-Policy header restricts inline scripts.</td></tr>
  <tr><td>CSRF</td><td>Synchronizer Token</td><td>40-byte token per session, validated on every state-changing request (POST/PUT/DELETE).</td></tr>
  <tr><td>Password compromise</td><td>bcrypt hashing</td><td><code>password_hash(…, PASSWORD_BCRYPT, ['cost'=&gt;12])</code>. Passwords are never stored in plain text.</td></tr>
  <tr><td>Session Fixation</td><td>Session regeneration</td><td><code>session_regenerate_id(true)</code> on login and every 30 minutes.</td></tr>
  <tr><td>Clickjacking</td><td>X-Frame-Options</td><td><code>X-Frame-Options: SAMEORIGIN</code> header set globally in config.php and .htaccess.</td></tr>
  <tr><td>MIME Sniffing</td><td>Content-Type header</td><td><code>X-Content-Type-Options: nosniff</code> header on all responses.</td></tr>
  <tr><td>Directory traversal</td><td>.htaccess restrictions</td><td><code>config/</code>, <code>includes/</code>, <code>database/</code>, <code>vendor/</code> directories deny all HTTP access.</td></tr>
  <tr><td>File upload attacks</td><td>MIME type validation</td><td>Uploaded images verified with PHP <code>finfo</code> (server-side). Only JPEG/PNG/WEBP/GIF allowed.</td></tr>
  <tr><td>Brute force</td><td>Timing-safe verify</td><td><code>password_verify()</code> is inherently constant-time. Future: rate limiting middleware.</td></tr>
</table>

</div>

<!-- ══════════════════════════════════════════════════════════════════
     4. IMPLEMENTATION DETAILS
     ══════════════════════════════════════════════════════════════════ -->
<div class="page-section">

<h1 class="chapter">4. Implementation Details</h1>

<h2 class="section">4.1 Technologies Used</h2>
<table>
  <tr><th>Layer</th><th>Technology</th><th>Version</th><th>Purpose</th></tr>
  <tr><td>Backend</td><td>PHP</td><td>8.0+</td><td>Server-side logic, REST API, session management</td></tr>
  <tr><td>Database</td><td>MySQL</td><td>8.0+</td><td>Relational data storage via XAMPP</td></tr>
  <tr><td>DB Access</td><td>PDO</td><td>Built-in</td><td>Database abstraction, prepared statements</td></tr>
  <tr><td>Frontend</td><td>HTML5 / CSS3</td><td>—</td><td>Semantic structure, mobile-first styles</td></tr>
  <tr><td>Frontend JS</td><td>Vanilla JavaScript</td><td>ES2021+</td><td>Fetch API, DOM manipulation, PWA logic</td></tr>
  <tr><td>Fonts</td><td>Google Fonts (Inter)</td><td>—</td><td>Modern typography</td></tr>
  <tr><td>PDF</td><td>mPDF</td><td>8.2+</td><td>Server-side PDF generation (Composer)</td></tr>
  <tr><td>Server</td><td>Apache (XAMPP)</td><td>2.4+</td><td>Local development server with mod_rewrite</td></tr>
  <tr><td>PWA</td><td>Service Worker API</td><td>—</td><td>Offline caching, install prompt</td></tr>
  <tr><td>PWA</td><td>Web App Manifest</td><td>—</td><td>Native-like install on mobile/desktop</td></tr>
</table>

<h2 class="section">4.2 Project Directory Structure</h2>
<pre><code>ecommerce-pwa/
├── .htaccess                  ← Apache: URL rewriting, security rules
├── index.php                  ← Entry point (redirects to shop)
├── manifest.json              ← PWA Web App Manifest
├── sw.js                      ← Service Worker (cache strategies)
├── offline.html               ← PWA offline fallback page
├── composer.json              ← PHP dependencies (mPDF)
│
├── config/
│   ├── config.php             ← App constants, session & security setup
│   └── database.php           ← PDO singleton DB class
│
├── database/
│   ├── schema.sql             ← Full DDL (7 tables)
│   └── seed.sql               ← Demo data (20 products, users, orders)
│
├── includes/                  ← Shared PHP helpers (no direct HTTP access)
│   ├── auth.php               ← Session auth helpers
│   ├── csrf.php               ← CSRF token management
│   ├── validator.php          ← Input validation &amp; sanitization
│   ├── response.php           ← JSON response formatter
│   ├── header.php             ← Global HTML header &amp; nav
│   └── footer.php             ← Global HTML footer &amp; scripts
│
├── api/                       ← REST JSON API endpoints
│   ├── auth/  (login, logout, register, check)
│   ├── products/  (index, show, create, update, delete)
│   ├── cart/  (index, add, update, remove, clear)
│   ├── orders/  (index, show, create, update)
│   ├── reviews/  (index, create)
│   ├── wishlist/  (index, toggle)
│   ├── categories/  (index, create, update, delete)
│   └── stats/  (dashboard)
│
├── pages/                     ← Frontend PHP pages
│   ├── shop.php               ← Product listing + hero
│   ├── product.php            ← Product detail + reviews
│   ├── cart.php               ← Shopping cart
│   ├── checkout.php           ← Order placement
│   ├── orders.php             ← Order history
│   ├── order-detail.php       ← Single order view
│   ├── wishlist.php           ← Wishlist
│   ├── profile.php            ← User profile
│   ├── login.php              ← Login form
│   ├── register.php           ← Registration form
│   └── admin/
│       ├── dashboard.php      ← Admin stats
│       ├── products.php       ← Product CRUD
│       ├── categories.php     ← Category management
│       ├── orders.php         ← Order management
│       └── users.php          ← User listing
│
├── assets/
│   ├── css/
│   │   ├── style.css          ← Main stylesheet (mobile-first, dark mode)
│   │   └── admin.css          ← Admin panel styles
│   ├── js/
│   │   ├── app.js             ← Main app JS (toast, nav, offline, PWA)
│   │   └── pwa.js             ← Service Worker registration
│   └── images/
│       ├── icons/             ← PWA icons (192px, 512px)
│       └── products/          ← Uploaded product images
│
└── report/
    ├── report.php             ← This report (generates PDF)
    └── generate_pdf.php       ← Standalone PDF generation script</code></pre>

<h2 class="section">4.3 Frontend Implementation</h2>
<h3 class="sub">Mobile-First CSS Design System</h3>
<p>The stylesheet (<code>style.css</code>) uses CSS Custom Properties (variables) to define a complete design system:</p>
<ul>
  <li><strong>Color palette</strong>: Purple primary (#6C63FF), Pink secondary (#FF6584), Teal accent (#43E8D8)</li>
  <li><strong>Dark theme by default</strong>: Deep navy backgrounds (#0D0E1A, #13142A) with high-contrast text</li>
  <li><strong>Light theme toggle</strong>: Swapped via <code>data-theme="light"</code> attribute on <code>&lt;html&gt;</code></li>
  <li><strong>Glassmorphism</strong>: Header and cards use <code>backdrop-filter: blur(20px)</code> for frosted glass effect</li>
  <li><strong>Typography</strong>: Inter (Google Fonts) — clean, modern, highly legible on all screens</li>
  <li><strong>Breakpoints</strong>: Mobile default → Tablet (768px) → Desktop (1024px) → Large (1280px)</li>
  <li><strong>Bottom Navigation</strong>: Fixed 4-tab bottom nav on mobile; hidden on desktop (&gt;768px)</li>
  <li><strong>Animations</strong>: Subtle fade-in, slide-up, and badge-pop keyframe animations using <code>cubic-bezier</code> easing</li>
</ul>

<h3 class="sub">Vanilla JavaScript Architecture</h3>
<p>All JavaScript is written in native ES2021+ Vanilla JS — no frameworks or bundlers.</p>
<ul>
  <li><strong>Fetch API</strong>: All API calls use <code>async/await fetch()</code> with proper error handling</li>
  <li><strong>NovaMart namespace</strong>: Global JS object exposes <code>toast()</code>, <code>updateCartCount()</code>, <code>toggleTheme()</code></li>
  <li><strong>Toast notifications</strong>: Non-blocking, auto-dismissing toasts for success/error/info/warning states</li>
  <li><strong>CSRF in AJAX</strong>: Token embedded in <code>&lt;meta name="csrf-token"&gt;</code>, read by JS and sent as <code>X-CSRF-Token</code> header</li>
  <li><strong>Skeleton loading</strong>: Product grid shows animated skeleton cards while data loads</li>
</ul>

<h2 class="section">4.4 Backend Implementation</h2>
<h3 class="sub">REST API Design</h3>
<p>All API endpoints in <code>/api/</code> follow consistent conventions:</p>
<div class="info-box">
  <strong>API Response Format:</strong><br>
  <code>{ "success": true/false, "message": "...", "data": { ... } }</code><br>
  All error messages are sanitized with <code>htmlspecialchars()</code> before output.
</div>
<table>
  <tr><th>Method</th><th>Endpoint</th><th>Auth</th><th>Description</th></tr>
  <tr><td>POST</td><td>/api/auth/register.php</td><td>Public</td><td>Register new customer</td></tr>
  <tr><td>POST</td><td>/api/auth/login.php</td><td>Public</td><td>Login with guest cart merge</td></tr>
  <tr><td>GET</td><td>/api/auth/logout.php</td><td>Public</td><td>Destroy session + redirect</td></tr>
  <tr><td>GET</td><td>/api/products/index.php</td><td>Public</td><td>List products (search, filter, sort, paginate)</td></tr>
  <tr><td>GET</td><td>/api/products/show.php</td><td>Public</td><td>Single product + related</td></tr>
  <tr><td>POST</td><td>/api/products/create.php</td><td>Admin</td><td>Create product (with image upload)</td></tr>
  <tr><td>POST</td><td>/api/products/update.php</td><td>Admin</td><td>Update product</td></tr>
  <tr><td>POST</td><td>/api/products/delete.php</td><td>Admin</td><td>Delete product</td></tr>
  <tr><td>GET</td><td>/api/cart/index.php</td><td>Customer</td><td>Get cart with totals</td></tr>
  <tr><td>POST</td><td>/api/cart/add.php</td><td>Customer</td><td>Add item to cart</td></tr>
  <tr><td>POST</td><td>/api/cart/update.php</td><td>Customer</td><td>Update cart quantity</td></tr>
  <tr><td>POST</td><td>/api/cart/remove.php</td><td>Customer</td><td>Remove cart item</td></tr>
  <tr><td>POST</td><td>/api/orders/create.php</td><td>Customer</td><td>Place order (atomic transaction)</td></tr>
  <tr><td>GET</td><td>/api/orders/index.php</td><td>Auth</td><td>List orders (scoped by role)</td></tr>
  <tr><td>POST</td><td>/api/orders/update.php</td><td>Admin</td><td>Update order status</td></tr>
  <tr><td>POST</td><td>/api/wishlist/toggle.php</td><td>Customer</td><td>Add/remove wishlist item</td></tr>
  <tr><td>GET</td><td>/api/stats/dashboard.php</td><td>Admin</td><td>Revenue, orders, top products</td></tr>
</table>

<h3 class="sub">Database Class (PDO Singleton)</h3>
<pre><code>class Database {
    private static ?PDO $instance = null;

    public static function getInstance(): PDO {
        if (self::$instance === null) {
            $dsn  = "mysql:host=localhost;dbname=novamart_db;charset=utf8mb4";
            $opts = [ PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                      PDO::ATTR_EMULATE_PREPARES => false ];
            self::$instance = new PDO($dsn, 'root', '', $opts);
        }
        return self::$instance;
    }

    public static function query(string $sql, array $params = []): PDOStatement {
        $stmt = self::getInstance()-&gt;prepare($sql); // ✅ Prepared statement
        $stmt-&gt;execute($params);
        return $stmt;
    }
}</code></pre>

<h2 class="section">4.5 Challenges Faced</h2>
<ul>
  <li><strong>Guest cart merge</strong>: Merging localStorage-based guest cart into DB cart on login required careful stock validation and quantity capping.</li>
  <li><strong>Atomic order placement</strong>: Using DB transactions (<code>BEGIN / COMMIT / ROLLBACK</code>) to ensure stock is decremented and order placed together, preventing partial failures.</li>
  <li><strong>Service Worker scope</strong>: Configuring the Service Worker for a subdirectory (<code>/ecommerce-pwa/</code>) required explicit scope declaration in registration.</li>
  <li><strong>CSRF in multipart forms</strong>: Image upload uses <code>multipart/form-data</code>, so CSRF token must be in the POST body or <code>X-CSRF-Token</code> header rather than <code>Content-Type: application/json</code>.</li>
  <li><strong>Mobile bottom nav z-index</strong>: Coordinating fixed header, bottom nav, toast notifications, and modal overlays required careful z-index management.</li>
</ul>

<h2 class="section">4.6 Assumptions Made</h2>
<ul>
  <li>The application is deployed on an Apache server with <code>mod_rewrite</code> and <code>mod_deflate</code> enabled (XAMPP default)</li>
  <li>PHP 8.0+ is available with PDO, PDO_MySQL, finfo, and session extensions</li>
  <li>Currency is Tanzanian Shilling (TZS) with 16% VAT — easily configurable in <code>config.php</code></li>
  <li>Free shipping threshold is TZS 5,000 — configurable in <code>api/cart/index.php</code></li>
  <li>Product images can be either uploaded locally or provided as external URLs (for CDN compatibility)</li>
  <li>The admin user is seeded via SQL; self-registration is customer-only by design for security</li>
</ul>

</div>

<!-- ══════════════════════════════════════════════════════════════════
     5. SECURITY CONSIDERATIONS
     ══════════════════════════════════════════════════════════════════ -->
<div class="page-section">

<h1 class="chapter">5. Security Considerations</h1>

<h2 class="section">5.1 Authentication Handling</h2>
<p>
  Authentication uses <strong>PHP native sessions</strong>. On successful login, four values are stored in
  <code>$_SESSION</code>: <code>user_id</code>, <code>user_name</code>, <code>user_email</code>, <code>user_role</code>.
  The session cookie is configured with:
</p>
<ul>
  <li><code>HttpOnly = 1</code> — JavaScript cannot access the session cookie</li>
  <li><code>SameSite = Strict</code> — Cookie is never sent in cross-site requests</li>
  <li><code>use_strict_mode = 1</code> — Prevents session fixation by rejecting uninitialized session IDs</li>
  <li><code>gc_maxlifetime = 7200</code> — Sessions expire after 2 hours of inactivity</li>
</ul>

<h2 class="section">5.2 Password Storage</h2>
<div class="success-box">
  <strong>Passwords are stored using bcrypt with cost factor 12.</strong><br>
  PHP's <code>password_hash($pass, PASSWORD_BCRYPT, ['cost' => 12])</code> produces a 60-character
  hash that includes the algorithm identifier, salt, and cost — making rainbow table attacks infeasible.
  The <code>password_needs_rehash()</code> function automatically upgrades hashes if the cost increases in future.
</div>

<h2 class="section">5.3 Data Protection</h2>
<ul>
  <li><strong>SQL Injection</strong>: Every database interaction uses parameterized queries. Example: <code>Database::query("SELECT * FROM users WHERE email = ?", [$email])</code> — the <code>$email</code> value is never concatenated into the SQL string.</li>
  <li><strong>XSS</strong>: All dynamic data rendered in HTML passes through <code>Validator::escape()</code> which calls <code>htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8')</code>.</li>
  <li><strong>Content Security Policy</strong>: The CSP header restricts script and style sources to <code>'self'</code> and Google Fonts CDN only.</li>
  <li><strong>HTTPS Ready</strong>: HSTS header and HTTPS redirect are pre-configured in <code>.htaccess</code> (activated by uncommenting for production).</li>
</ul>

<h2 class="section">5.4 Access Control</h2>
<table>
  <tr><th>Resource</th><th>Guest</th><th>Customer</th><th>Admin</th></tr>
  <tr><td>Browse products / shop</td><td>✅</td><td>✅</td><td>✅</td></tr>
  <tr><td>View product detail</td><td>✅</td><td>✅</td><td>✅</td></tr>
  <tr><td>Add to cart / wishlist</td><td>❌ (redirect login)</td><td>✅</td><td>✅</td></tr>
  <tr><td>Checkout / place order</td><td>❌</td><td>✅</td><td>✅</td></tr>
  <tr><td>View own orders</td><td>❌</td><td>✅ (own only)</td><td>✅ (all)</td></tr>
  <tr><td>Submit reviews</td><td>❌</td><td>✅</td><td>✅</td></tr>
  <tr><td>Admin dashboard</td><td>❌</td><td>❌ (403)</td><td>✅</td></tr>
  <tr><td>Product CRUD</td><td>❌</td><td>❌</td><td>✅</td></tr>
  <tr><td>Order status update</td><td>❌</td><td>❌</td><td>✅</td></tr>
  <tr><td>Access /config/, /includes/</td><td>❌ (.htaccess)</td><td>❌</td><td>❌</td></tr>
</table>
<div class="warn-box">
  <strong>Role enforcement is double-layered:</strong> The PHP page <code>Auth::requireAdminPage()</code>
  redirects non-admins away, AND every API endpoint independently calls <code>Auth::requireAdmin()</code>
  — so bypassing the UI still results in a 403 JSON response.
</div>

</div>

<!-- ══════════════════════════════════════════════════════════════════
     6. TESTING & RESULTS
     ══════════════════════════════════════════════════════════════════ -->
<div class="page-section">

<h1 class="chapter">6. Testing &amp; Results</h1>

<h2 class="section">6.1 Application Screenshots</h2>

<div class="screen-desc">
  <strong>🛒 Shop Page — Hero + Product Grid</strong>
  URL: http://localhost/ecommerce-pwa/pages/shop.php<br>
  Features visible: Hero banner with CTA, category filter chips, product cards with badges (Sale, Featured),
  star ratings, Add to Cart button, wishlist toggle, sort and price filters.
</div>

<div class="screen-desc">
  <strong>📦 Product Detail Page</strong>
  URL: http://localhost/ecommerce-pwa/pages/product.php?slug={slug}<br>
  Features visible: Large product image, breadcrumb, price with sale badge, quantity selector,
  Add to Cart + Wishlist buttons, customer reviews with star ratings, related products grid.
</div>

<div class="screen-desc">
  <strong>🛒 Shopping Cart</strong>
  URL: http://localhost/ecommerce-pwa/pages/cart.php<br>
  Features visible: Cart items with thumbnail, quantity +/− controls, line totals,
  free shipping progress bar, VAT breakdown, Proceed to Checkout button.
</div>

<div class="screen-desc">
  <strong>✅ Checkout Page</strong>
  URL: http://localhost/ecommerce-pwa/pages/checkout.php<br>
  Features visible: Shipping form with country selector, payment method selector (COD/M-Pesa/Card),
  order summary with all items + total, Place Order button.
</div>

<div class="screen-desc">
  <strong>📊 Admin Dashboard</strong>
  URL: http://localhost/ecommerce-pwa/pages/admin/dashboard.php<br>
  Features visible: 4 KPI stat cards (Revenue, Orders, Products, Customers), recent orders table,
  top products by sales, quick action buttons.
</div>

<div class="screen-desc">
  <strong>📦 Admin Products CRUD</strong>
  URL: http://localhost/ecommerce-pwa/pages/admin/products.php<br>
  Features visible: Product table with search/filter, edit/delete actions, modal form for Add/Edit
  with image upload, category selector, price/sale price/stock/featured/active settings.
</div>

<h2 class="section">6.2 Major Features Verification</h2>
<table>
  <tr><th>Feature</th><th>Test Action</th><th>Expected Result</th><th>Status</th></tr>
  <tr><td>User Registration</td><td>Submit form with valid data</td><td>Account created, auto-login, redirect to shop</td><td><span class="tag tag-green">✅ Pass</span></td></tr>
  <tr><td>Login with wrong password</td><td>Submit invalid credentials</td><td>Generic "Invalid email or password" error (no leakage)</td><td><span class="tag tag-green">✅ Pass</span></td></tr>
  <tr><td>CSRF protection</td><td>POST without token</td><td>HTTP 403 with JSON error message</td><td><span class="tag tag-green">✅ Pass</span></td></tr>
  <tr><td>SQL injection attempt</td><td>Enter <code>' OR '1'='1</code> in email</td><td>No SQL error; query returns 0 rows gracefully</td><td><span class="tag tag-green">✅ Pass</span></td></tr>
  <tr><td>XSS attempt</td><td>Submit <code>&lt;script&gt;alert(1)&lt;/script&gt;</code> in review</td><td>Rendered as escaped text, no JS execution</td><td><span class="tag tag-green">✅ Pass</span></td></tr>
  <tr><td>Add to Cart</td><td>Click "Add to Cart" on product</td><td>Cart badge increments, toast notification shown</td><td><span class="tag tag-green">✅ Pass</span></td></tr>
  <tr><td>Stock enforcement</td><td>Add more than available stock</td><td>Quantity capped at available stock; error shown</td><td><span class="tag tag-green">✅ Pass</span></td></tr>
  <tr><td>Place Order</td><td>Complete checkout form</td><td>Order created, cart cleared, order number returned</td><td><span class="tag tag-green">✅ Pass</span></td></tr>
  <tr><td>Admin product creation</td><td>Fill product modal + save</td><td>Product appears in product table and shop page</td><td><span class="tag tag-green">✅ Pass</span></td></tr>
  <tr><td>Admin order status</td><td>Change to "Shipped"</td><td>Status badge updates in orders table</td><td><span class="tag tag-green">✅ Pass</span></td></tr>
  <tr><td>PWA install prompt</td><td>Visit on Chrome mobile</td><td>Install banner appears after 4 seconds</td><td><span class="tag tag-green">✅ Pass</span></td></tr>
  <tr><td>Offline access</td><td>Disable internet, reload</td><td>Offline page displayed; cached pages served</td><td><span class="tag tag-green">✅ Pass</span></td></tr>
</table>

<h2 class="section">6.3 Incomplete Features</h2>
<div class="warn-box">
  The following features were scoped for a future iteration due to time constraints:
  <ul style="margin-top:8px;">
    <li><strong>Email notifications</strong>: Order confirmation emails (SMTP integration with PHPMailer planned)</li>
    <li><strong>Real payment gateway</strong>: M-Pesa Daraja API and Stripe integration (currently COD simulation)</li>
    <li><strong>Product image gallery</strong>: Multiple images per product (currently single image)</li>
    <li><strong>Discount coupons</strong>: Coupon code system (DB schema exists but UI not built)</li>
    <li><strong>Rate limiting</strong>: Login attempt throttling (currently relies on bcrypt slowness alone)</li>
  </ul>
</div>

</div>

<!-- ══════════════════════════════════════════════════════════════════
     7. FUTURE WORK
     ══════════════════════════════════════════════════════════════════ -->
<div class="page-section">

<h1 class="chapter">7. Future Work</h1>

<h2 class="section">7.1 Possible Improvements</h2>
<table>
  <tr><th>Improvement</th><th>Priority</th><th>Complexity</th><th>Description</th></tr>
  <tr><td>M-Pesa Integration</td><td>🔴 High</td><td>Medium</td><td>Safaricom Daraja API for STK Push payment</td></tr>
  <tr><td>Email notifications</td><td>🔴 High</td><td>Low</td><td>PHPMailer SMTP for order confirmation and shipping updates</td></tr>
  <tr><td>Product image gallery</td><td>🟡 Medium</td><td>Low</td><td>Multiple product images with carousel thumbnail</td></tr>
  <tr><td>Discount coupons</td><td>🟡 Medium</td><td>Low</td><td>Coupon code validation at checkout</td></tr>
  <tr><td>Advanced search</td><td>🟡 Medium</td><td>Medium</td><td>Full-text MySQL search, attribute filters, price range slider</td></tr>
  <tr><td>Product variants</td><td>🟡 Medium</td><td>High</td><td>Size/color variants with per-variant stock</td></tr>
  <tr><td>Rate limiting</td><td>🟢 Low</td><td>Low</td><td>Redis-based request throttling on auth endpoints</td></tr>
  <tr><td>Analytics dashboard</td><td>🟢 Low</td><td>Medium</td><td>Chart.js revenue graphs, sales trends, customer geography</td></tr>
  <tr><td>Product import/export</td><td>🟢 Low</td><td>Low</td><td>CSV/Excel bulk product upload for admins</td></tr>
  <tr><td>Customer loyalty</td><td>🟢 Low</td><td>Medium</td><td>Points system, tiered discounts, referral program</td></tr>
</table>

<h2 class="section">7.2 Scalability Considerations</h2>
<ul>
  <li>
    <strong>Horizontal scaling</strong>: The stateless API design (session stored server-side but could move to Redis)
    enables running behind a load balancer. The PDO singleton would need adjustment for persistent shared state.
  </li>
  <li>
    <strong>Database optimization</strong>: Add read replicas for product catalogue (read-heavy). Implement query result
    caching (Redis/Memcached) for popular product listing and category queries.
  </li>
  <li>
    <strong>CDN for static assets</strong>: CSS, JS, and product images should be served via a CDN (e.g. Cloudflare)
    to reduce server load and improve global latency.
  </li>
  <li>
    <strong>Microservices migration path</strong>: The existing API-first architecture (PHP JSON endpoints)
    maps cleanly to a microservices split — e.g., separate Orders Service, Inventory Service, and Notification Service.
  </li>
  <li>
    <strong>Containerization</strong>: Docker Compose file can package PHP-FPM + Nginx + MySQL + Redis —
    enabling <code>docker-compose up</code> deployment for staging/production environments.
  </li>
  <li>
    <strong>Full-text search</strong>: At scale, MySQL FULLTEXT search should be replaced with Elasticsearch
    for faster, more relevant product search results.
  </li>
</ul>

<div class="info-box" style="margin-top:40px;">
  <strong>📄 Report prepared by: Jaiross Steven</strong><br>
  System: NovaMart E-Commerce PWA · Version 1.0.0<br>
  Generated: <?= date('d F Y, H:i:s') ?><br>
  Technology: PHP <?= phpversion() ?> · MySQL 8.x · Apache (XAMPP) · Vanilla JS · PWA
</div>

</div>

</body>
</html>
