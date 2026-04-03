<?php
/**
 * NovaMart — HTML Page Header & Navigation
 * Include at the top of every page.
 *
 * @param string $pageTitle  Title for the browser tab
 * @param string $activePage Slug to mark active nav link
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/csrf.php';
require_once dirname(__DIR__) . '/includes/validator.php';

$pageTitle  = $pageTitle  ?? APP_NAME;
$activePage = $activePage ?? '';
$csrfToken  = CSRF::token();

// Cart count (from DB if logged in)
$cartCount = 0;
if (Auth::check()) {
    try {
        $cartCount = (int) Database::fetchColumn(
            "SELECT COALESCE(SUM(quantity),0) FROM cart WHERE user_id = ?",
            [Auth::id()]
        );
    } catch (Exception $e) { $cartCount = 0; }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
  <meta name="description" content="<?= Validator::escape(APP_TAGLINE) ?> — Shop electronics, fashion, books and more.">
  <meta name="theme-color" content="#6C63FF">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="apple-mobile-web-app-title" content="<?= Validator::escape(APP_NAME) ?>">

  <!-- PWA Manifest -->
  <link rel="manifest" href="<?= APP_URL ?>/manifest.json">
  <link rel="apple-touch-icon" href="<?= APP_URL ?>/assets/images/icons/icon-192.png">
  <link rel="icon" type="image/png" href="<?= APP_URL ?>/assets/images/icons/icon-192.png">

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

  <!-- Stylesheet -->
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css?v=1.0.1">

  <title><?= Validator::escape($pageTitle) ?> — <?= Validator::escape(APP_NAME) ?></title>

  <!-- CSRF Meta for JS -->
  <meta name="csrf-token" content="<?= Validator::escape($csrfToken) ?>">
  <meta name="app-url" content="<?= APP_URL ?>">
</head>
<body>

<!-- ═══════════════════════ HEADER / NAV ═══════════════════════ -->
<header class="site-header" id="site-header">
  <div class="container">
    <div class="header-inner">

      <!-- Logo -->
      <a href="<?= APP_URL ?>/pages/shop.php" class="logo" id="logo-link" aria-label="<?= APP_NAME ?> Home">
        <span class="logo-icon">🛍</span>
        <span class="logo-text"><?= Validator::escape(APP_NAME) ?></span>
      </a>

      <!-- Search bar (hidden on very small screens, toggle on mobile) -->
      <div class="search-bar" id="search-bar">
        <form action="<?= APP_URL ?>/pages/shop.php" method="GET" class="search-form" role="search">
          <input type="search" name="q" id="header-search" class="search-input"
                 placeholder="Search products…" value="<?= Validator::escape($_GET['q'] ?? '') ?>">
          <button type="submit" class="search-btn" aria-label="Search">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
            </svg>
          </button>
        </form>
      </div>

      <!-- Nav Actions -->
      <nav class="nav-actions" aria-label="User actions">
        <!-- Search toggle for mobile -->
        <button class="icon-btn search-toggle" id="search-toggle" aria-label="Toggle search">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
          </svg>
        </button>

        <!-- Wishlist -->
        <?php if (Auth::check()): ?>
        <a href="<?= APP_URL ?>/pages/wishlist.php" class="icon-btn <?= $activePage === 'wishlist' ? 'active' : '' ?>" aria-label="Wishlist">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
          </svg>
        </a>
        <?php endif; ?>

        <!-- Cart -->
        <a href="<?= APP_URL ?>/pages/cart.php" class="icon-btn cart-btn <?= $activePage === 'cart' ? 'active' : '' ?>" aria-label="Cart (<?= $cartCount ?> items)">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
          </svg>
          <?php if ($cartCount > 0): ?>
          <span class="badge" id="cart-count" aria-live="polite"><?= $cartCount ?></span>
          <?php else: ?>
          <span class="badge hidden" id="cart-count" aria-live="polite">0</span>
          <?php endif; ?>
        </a>

        <!-- User menu -->
        <?php if (Auth::check()): ?>
        <div class="user-menu" id="user-menu">
          <button class="user-avatar-btn" id="user-menu-toggle" aria-expanded="false" aria-haspopup="true">
            <span class="avatar-initials"><?= strtoupper(substr($_SESSION['user_name'], 0, 1)) ?></span>
          </button>
          <div class="dropdown-menu" id="user-dropdown" role="menu">
            <div class="dropdown-header">
              <strong><?= Validator::escape($_SESSION['user_name']) ?></strong>
              <small><?= Validator::escape($_SESSION['user_email']) ?></small>
            </div>
            <div class="dropdown-divider"></div>
            <?php if (Auth::isAdmin()): ?>
            <a href="<?= APP_URL ?>/pages/admin/dashboard.php" class="dropdown-item" role="menuitem">📊 Dashboard</a>
            <?php endif; ?>
            <a href="<?= APP_URL ?>/pages/profile.php" class="dropdown-item" role="menuitem">👤 My Profile</a>
            <a href="<?= APP_URL ?>/pages/orders.php" class="dropdown-item" role="menuitem">📦 My Orders</a>
            <a href="<?= APP_URL ?>/pages/wishlist.php" class="dropdown-item" role="menuitem">❤️ Wishlist</a>
            <div class="dropdown-divider"></div>
            <a href="<?= APP_URL ?>/api/auth/logout.php" class="dropdown-item danger" role="menuitem"
               onclick="return confirm('Are you sure you want to sign out?')">🚪 Sign Out</a>
          </div>
        </div>
        <?php else: ?>
        <a href="<?= APP_URL ?>/pages/login.php" class="btn-outline-sm <?= $activePage === 'login' ? 'active' : '' ?>">Sign In</a>
        <?php endif; ?>

        <!-- Hamburger for mobile nav -->
        <button class="hamburger" id="hamburger" aria-label="Toggle navigation" aria-expanded="false">
          <span></span><span></span><span></span>
        </button>
      </nav>
    </div><!-- /.header-inner -->

    <!-- Mobile Search (expanded below header) -->
    <div class="mobile-search" id="mobile-search" hidden>
      <form action="<?= APP_URL ?>/pages/shop.php" method="GET" class="search-form" role="search">
        <input type="search" name="q" id="mobile-search-input" class="search-input"
               placeholder="Search products…" value="<?= Validator::escape($_GET['q'] ?? '') ?>">
        <button type="submit" class="search-btn" aria-label="Search">Search</button>
      </form>
    </div>
  </div><!-- /.container -->

  <!-- Mobile Nav Drawer -->
  <nav class="mobile-nav" id="mobile-nav" aria-label="Mobile navigation" hidden>
    <ul class="mobile-nav-list">
      <li><a href="<?= APP_URL ?>/pages/shop.php" class="<?= $activePage === 'shop' ? 'active' : '' ?>">🏪 Shop</a></li>
      <?php if (Auth::check()): ?>
      <li><a href="<?= APP_URL ?>/pages/orders.php"  class="<?= $activePage === 'orders' ? 'active' : '' ?>">📦 My Orders</a></li>
      <li><a href="<?= APP_URL ?>/pages/wishlist.php" class="<?= $activePage === 'wishlist' ? 'active' : '' ?>">❤️ Wishlist</a></li>
      <li><a href="<?= APP_URL ?>/pages/profile.php"  class="<?= $activePage === 'profile' ? 'active' : '' ?>">👤 Profile</a></li>
      <?php if (Auth::isAdmin()): ?>
      <li><a href="<?= APP_URL ?>/pages/admin/dashboard.php">📊 Admin</a></li>
      <?php endif; ?>
      <li><a href="<?= APP_URL ?>/api/auth/logout.php" class="danger">🚪 Sign Out</a></li>
      <?php else: ?>
      <li><a href="<?= APP_URL ?>/pages/login.php">Sign In</a></li>
      <li><a href="<?= APP_URL ?>/pages/register.php">Create Account</a></li>
      <?php endif; ?>
    </ul>
  </nav>
</header>

<!-- Toast notification container -->
<div class="toast-container" id="toast-container" aria-live="polite" aria-atomic="true"></div>

<!-- Offline indicator -->
<div class="offline-banner" id="offline-banner" hidden>
  📶 You appear to be offline. Some features may be unavailable.
</div>

<main id="main-content">
