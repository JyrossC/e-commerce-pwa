<?php
/**
 * NovaMart — HTML Page Footer
 * Include at the bottom of every page.
 */
?>
</main><!-- /#main-content -->

<!-- ═══════════════════════ BOTTOM NAV (mobile) ═══════════════════════ -->
<nav class="bottom-nav" aria-label="Bottom navigation">
  <a href="<?= APP_URL ?>/pages/shop.php"
     class="bottom-nav-item <?= ($activePage === 'shop') ? 'active' : '' ?>"
     aria-label="Shop">
    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"
         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
      <polyline points="9 22 9 12 15 12 15 22"/>
    </svg>
    <span>Home</span>
  </a>

  <a href="<?= APP_URL ?>/pages/shop.php?view=categories"
     class="bottom-nav-item <?= ($activePage === 'categories') ? 'active' : '' ?>"
     aria-label="Categories">
    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"
         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
      <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
    </svg>
    <span>Categories</span>
  </a>

  <a href="<?= APP_URL ?>/pages/cart.php"
     class="bottom-nav-item <?= ($activePage === 'cart') ? 'active' : '' ?>"
     aria-label="Cart" style="position:relative;">
    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"
         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
      <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
    </svg>
    <span>Cart</span>
  </a>

  <?php if (Auth::check()): ?>
  <a href="<?= APP_URL ?>/pages/profile.php"
     class="bottom-nav-item <?= ($activePage === 'profile') ? 'active' : '' ?>"
     aria-label="Account">
    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"
         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
      <circle cx="12" cy="7" r="4"/>
    </svg>
    <span>Account</span>
  </a>
  <?php else: ?>
  <a href="<?= APP_URL ?>/pages/login.php"
     class="bottom-nav-item <?= ($activePage === 'login') ? 'active' : '' ?>"
     aria-label="Sign In">
    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"
         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
      <polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/>
    </svg>
    <span>Sign In</span>
  </a>
  <?php endif; ?>
</nav>

<!-- ═══════════════════════ SITE FOOTER ═══════════════════════ -->
<footer class="site-footer">
  <div class="container">
    <div class="footer-grid">
      <!-- Brand -->
      <div class="footer-brand">
        <a href="<?= APP_URL ?>/pages/shop.php" class="logo" aria-label="NovaMart Home">
          <span class="logo-icon">🛍</span>
          <span class="logo-text"><?= Validator::escape(APP_NAME) ?></span>
        </a>
        <p><?= Validator::escape(APP_TAGLINE) ?></p>
        <p class="footer-copy">&copy; <?= date('Y') ?> <?= Validator::escape(APP_NAME) ?>. All rights reserved.</p>
      </div>

      <!-- Quick Links -->
      <div class="footer-links">
        <h4>Quick Links</h4>
        <ul>
          <li><a href="<?= APP_URL ?>/pages/shop.php">Shop All</a></li>
          <li><a href="<?= APP_URL ?>/pages/shop.php?featured=1">Featured</a></li>
          <li><a href="<?= APP_URL ?>/pages/cart.php">My Cart</a></li>
          <li><a href="<?= APP_URL ?>/pages/orders.php">My Orders</a></li>
        </ul>
      </div>

      <!-- Categories -->
      <div class="footer-links">
        <h4>Categories</h4>
        <ul>
          <li><a href="<?= APP_URL ?>/pages/shop.php?category=electronics">Electronics</a></li>
          <li><a href="<?= APP_URL ?>/pages/shop.php?category=fashion">Fashion</a></li>
          <li><a href="<?= APP_URL ?>/pages/shop.php?category=books">Books</a></li>
          <li><a href="<?= APP_URL ?>/pages/shop.php?category=sports">Sports</a></li>
        </ul>
      </div>

      <!-- Account -->
      <div class="footer-links">
        <h4>Account</h4>
        <ul>
          <?php if (Auth::check()): ?>
          <li><a href="<?= APP_URL ?>/pages/profile.php">My Profile</a></li>
          <li><a href="<?= APP_URL ?>/pages/wishlist.php">Wishlist</a></li>
          <li><a href="<?= APP_URL ?>/api/auth/logout.php">Sign Out</a></li>
          <?php else: ?>
          <li><a href="<?= APP_URL ?>/pages/login.php">Sign In</a></li>
          <li><a href="<?= APP_URL ?>/pages/register.php">Create Account</a></li>
          <?php endif; ?>
        </ul>
      </div>
    </div>

    <div class="footer-bottom">
      <p>Built with ❤️ by <strong>Jaiross Steven</strong> · PWA E-Commerce v<?= APP_VERSION ?></p>
      <p>Secured with CSRF protection, bcrypt passwords, and PDO prepared statements.</p>
    </div>
  </div>
</footer>

<!-- ═══════════════════════ SCRIPTS ═══════════════════════ -->
<script src="<?= APP_URL ?>/assets/js/app.js?v=1.0.1"></script>
<script src="<?= APP_URL ?>/assets/js/pwa.js?v=1.0.1"></script>

<!-- PWA Install Prompt -->
<div class="install-prompt" id="install-prompt" hidden>
  <div class="install-prompt-content">
    <span class="logo-icon">🛍</span>
    <div>
      <strong>Install <?= APP_NAME ?></strong>
      <small>Add to your home screen for the best experience</small>
    </div>
    <div class="install-buttons">
      <button class="btn-primary-sm" id="install-accept">Install</button>
      <button class="btn-ghost-sm" id="install-dismiss">Not now</button>
    </div>
  </div>
</div>

</body>
</html>
