<?php
/**
 * NovaMart — Login Page
 */
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/csrf.php';

Auth::redirectIfLoggedIn();

$pageTitle = 'Sign In';
$activePage = 'login';

$msg = $_GET['msg'] ?? '';
include dirname(__DIR__) . '/includes/header.php';
?>

<div class="auth-page">
  <div class="auth-card">
    <div class="auth-logo">
      <a href="<?= APP_URL ?>/pages/shop.php" class="logo">
        <span class="logo-icon">🛍</span>
        <span class="logo-text"><?= APP_NAME ?></span>
      </a>
    </div>

    <?php if ($msg === 'logged_out'): ?>
      <div class="alert alert-info">You have been signed out successfully.</div>
    <?php endif; ?>

    <div id="form-alert" class="hidden"></div>

    <h1 class="auth-title">Welcome back</h1>
    <p class="auth-subtitle">Sign in to your <?= APP_NAME ?> account</p>

    <form id="login-form" class="mt-lg" novalidate>
      <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= CSRF::token() ?>">

      <div class="form-group">
        <label class="form-label" for="email">Email Address</label>
        <div class="input-group">
          <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
            fill="none" stroke="currentColor" stroke-width="2">
            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
            <polyline points="22,6 12,12 2,6" />
          </svg>
          <input type="email" id="email" name="email" class="form-control" placeholder="you@example.com"
            autocomplete="email" required>
        </div>
        <span class="form-error hidden" id="email-error"></span>
      </div>

      <div class="form-group">
        <label class="form-label" for="password">
          Password
          <a href="#" class="float-right" style="float:right;font-size:var(--text-xs);color:var(--primary);">Forgot
            password?</a>
        </label>
        <div class="input-group" style="position:relative;">
          <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
            fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
          </svg>
          <input type="password" id="password" name="password" class="form-control" placeholder="••••••••"
            autocomplete="current-password" required>
          <button type="button" class="password-toggle" id="pwd-toggle"
            aria-label="Toggle password visibility">👁</button>
        </div>
        <span class="form-error hidden" id="password-error"></span>
      </div>

      <div class="form-check mb-lg">
        <input type="checkbox" id="remember" name="remember">
        <label for="remember" style="font-size:var(--text-sm);color:var(--text-2);">Remember me</label>
      </div>

      <button type="submit" class="btn btn-primary btn-block" id="login-btn">
        <span id="login-text">Sign In</span>
        <span id="login-spinner" class="hidden"
          style="width:16px;height:16px;border:2px solid rgba(255,255,255,0.4);border-top-color:#fff;border-radius:50%;animation:spin 0.8s linear infinite;display:inline-block;"></span>
      </button>
    </form>

    <div class="auth-divider">or</div>

    <!-- <div style="text-align:center;">
      <p style="font-size:var(--text-xs);color:var(--text-3);margin-bottom:var(--space-md);">Demo credentials:</p>
      <div style="display:flex;gap:var(--space-sm);flex-wrap:wrap;justify-content:center;">
        <button class="btn btn-ghost btn-sm" onclick="fillDemo('demo@novamart.com','Demo@1234')">🧑 Customer Demo</button>
        <button class="btn btn-ghost btn-sm" onclick="fillDemo('admin@novamart.com','Admin@1234')">👑 Admin Demo</button>
      </div>
    </div> -->

    <div class="auth-footer">
      Don't have an account? <a href="<?= APP_URL ?>/pages/register.php">Create one free</a>
    </div>
  </div>
</div>

<script>
  function fillDemo(email, pass) {
    document.getElementById('email').value = email;
    document.getElementById('password').value = pass;
  }

  document.getElementById('pwd-toggle').addEventListener('click', function () {
    const pwd = document.getElementById('password');
    const isText = pwd.type === 'text';
    pwd.type = isText ? 'password' : 'text';
    this.textContent = isText ? '👁' : '🙈';
  });

  document.getElementById('login-form').addEventListener('submit', async function (e) {
    e.preventDefault();
    const btn = document.getElementById('login-btn');
    const text = document.getElementById('login-text');
    const spin = document.getElementById('login-spinner');
    const alertEl = document.getElementById('form-alert');

    // Clear errors
    document.querySelectorAll('.form-error').forEach(el => { el.textContent = ''; el.classList.add('hidden'); });
    alertEl.className = 'hidden';
    btn.disabled = true; text.textContent = 'Signing in…'; spin.classList.remove('hidden');

    const data = {
      email: document.getElementById('email').value.trim(),
      password: document.getElementById('password').value,
      '<?= CSRF_TOKEN_NAME ?>': document.querySelector('[name="<?= CSRF_TOKEN_NAME ?>"]').value,
    };

    try {
      const res = await fetch('<?= APP_URL ?>/api/auth/login.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-Token': data['<?= CSRF_TOKEN_NAME ?>']
        },
        body: JSON.stringify(data),
      });
      const json = await res.json();

      if (json.success) {
        NovaMart.toast('success', 'Welcome back!', json.message);
        setTimeout(() => {
          const dest = json.data?.redirect
            || (json.data?.user?.role === 'admin' ? '<?= APP_URL ?>/pages/admin/dashboard.php' : '<?= APP_URL ?>/pages/shop.php');
          window.location.href = dest;
        }, 600);
      } else {
        alertEl.className = 'alert alert-error';
        alertEl.textContent = json.message || 'Login failed.';
        if (json.errors) {
          Object.entries(json.errors).forEach(([field, msgs]) => {
            const el = document.getElementById(field + '-error');
            if (el) { el.textContent = msgs[0]; el.classList.remove('hidden'); }
          });
        }
      }
    } catch (err) {
      alertEl.className = 'alert alert-error';
      alertEl.textContent = 'Network error. Please try again.';
    } finally {
      btn.disabled = false; text.textContent = 'Sign In'; spin.classList.add('hidden');
    }
  });
</script>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>