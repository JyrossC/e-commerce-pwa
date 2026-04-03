<?php
/**
 * NovaMart — Registration Page
 */
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/csrf.php';

Auth::redirectIfLoggedIn();
$pageTitle = 'Create Account';
$activePage = 'register';
include dirname(__DIR__) . '/includes/header.php';
?>

<div class="auth-page">
  <div class="auth-card">
    <div class="auth-logo">
      <a href="<?= APP_URL ?>/pages/shop.php" class="logo">
        <span class="logo-icon">🛍</span><span class="logo-text"><?= APP_NAME ?></span>
      </a>
    </div>

    <div id="form-alert" class="hidden"></div>
    <h1 class="auth-title">Create your account</h1>
    <p class="auth-subtitle">Join <?= APP_NAME ?> and start shopping today</p>

    <form id="register-form" class="mt-lg" novalidate>
      <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= CSRF::token() ?>">

      <div class="form-group">
        <label class="form-label" for="name">Full Name</label>
        <div class="input-group">
          <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
            fill="none" stroke="currentColor" stroke-width="2">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
            <circle cx="12" cy="7" r="4" />
          </svg>
          <input type="text" id="name" name="name" class="form-control" placeholder="Jyross Curtis" autocomplete="name"
            required>
        </div>
        <span class="form-error hidden" id="name-error"></span>
      </div>

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
        <label class="form-label" for="password">Password</label>
        <div class="input-group" style="position:relative;">
          <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
            fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="11" width="18" height="11" rx="2" />
            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
          </svg>
          <input type="password" id="password" name="password" class="form-control"
            placeholder="**********" required>
          <button type="button" class="password-toggle" onclick="togglePwd('password',this)">👁</button>
        </div>
        <small style="font-size: 10px; margin: 10px;">Min 8 chars with uppercase, number & symbol</small>
        <div id="pwd-strength" style="margin-top:6px;display:none;">
          <div style="height:4px;background:var(--bg-2);border-radius:2px;overflow:hidden;">
            <div id="pwd-bar" style="height:100%;transition:all 0.3s;border-radius:2px;width:0%;"></div>
          </div>
          <span id="pwd-label" style="font-size:10px;color:var(--text-3);"></span>
        </div>
        <span class="form-error hidden" id="password-error"></span>
      </div>

      <div class="form-group">
        <label class="form-label" for="password_confirmation">Confirm Password</label>
        <div class="input-group" style="position:relative;">
          <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
            fill="none" stroke="currentColor" stroke-width="2">
            <path d="M9 11l3 3L22 4" />
            <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11" />
          </svg>
          <input type="password" id="password_confirmation" name="password_confirmation" class="form-control"
            placeholder="**********" required>
          <button type="button" class="password-toggle" onclick="togglePwd('password_confirmation',this)">👁</button>
        </div>
        <small style="font-size: 10px; margin: 10px;">Retype Your Password</small>
        <span class="form-error hidden" id="password_confirmation-error"></span>
      </div>

      <div class="form-check mb-lg">
        <input type="checkbox" id="terms" required>
        <label for="terms" style="font-size:var(--text-sm);color:var(--text-2);">
          I agree to the <a href="#" style="color:var(--primary);">Terms of Service</a> and <a href="#"
            style="color:var(--primary);">Privacy Policy</a>
        </label>
      </div>

      <button type="submit" class="btn btn-primary btn-block" id="reg-btn">
        <span id="reg-text">Create Account</span>
        <span id="reg-spinner" class="hidden"
          style="width:16px;height:16px;border:2px solid rgba(255,255,255,0.4);border-top-color:#fff;border-radius:50%;animation:spin 0.8s linear infinite;display:inline-block;"></span>
      </button>
    </form>

    <div class="auth-footer">
      Already have an account? <a href="<?= APP_URL ?>/pages/login.php">Sign in</a>
    </div>
  </div>
</div>

<script>
  function togglePwd(id, btn) {
    const el = document.getElementById(id);
    el.type = el.type === 'password' ? 'text' : 'password';
    btn.textContent = el.type === 'password' ? '👁' : '🙈';
  }

  document.getElementById('password').addEventListener('input', function () {
    const val = this.value;
    const bar = document.getElementById('pwd-bar');
    const lbl = document.getElementById('pwd-label');
    document.getElementById('pwd-strength').style.display = val ? 'block' : 'none';
    let score = 0;
    if (val.length >= 8) score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[\W_]/.test(val)) score++;
    const levels = [
      { w: '25%', color: 'var(--error)', label: 'Weak' },
      { w: '50%', color: 'var(--warning)', label: 'Fair' },
      { w: '75%', color: 'var(--info)', label: 'Good' },
      { w: '100%', color: 'var(--success)', label: 'Strong' },
    ];
    const lvl = levels[score - 1] || levels[0];
    bar.style.width = lvl.w; bar.style.background = lvl.color;
    lbl.textContent = lvl.label; lbl.style.color = lvl.color;
  });

  document.getElementById('register-form').addEventListener('submit', async function (e) {
    e.preventDefault();
    if (!document.getElementById('terms').checked) {
      const alertEl = document.getElementById('form-alert');
      alertEl.className = 'alert alert-error';
      alertEl.textContent = 'Please accept the terms and conditions.';
      return;
    }
    const btn = document.getElementById('reg-btn');
    const text = document.getElementById('reg-text');
    const spin = document.getElementById('reg-spinner');
    const alertEl = document.getElementById('form-alert');
    document.querySelectorAll('.form-error').forEach(el => { el.textContent = ''; el.classList.add('hidden'); });
    alertEl.className = 'hidden';
    btn.disabled = true; text.textContent = 'Creating…'; spin.classList.remove('hidden');

    const data = {
      name: document.getElementById('name').value.trim(),
      email: document.getElementById('email').value.trim(),
      password: document.getElementById('password').value,
      password_confirmation: document.getElementById('password_confirmation').value,
      '<?= CSRF_TOKEN_NAME ?>': document.querySelector('[name="<?= CSRF_TOKEN_NAME ?>"]').value,
    };

    try {
      const res = await fetch('<?= APP_URL ?>/api/auth/register.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-Token': data['<?= CSRF_TOKEN_NAME ?>']
        },
        body: JSON.stringify(data),
      });
      const json = await res.json();
      if (json.success) {
        NovaMart.toast('success', 'Welcome!', json.message);
        setTimeout(() => window.location.href = '<?= APP_URL ?>/pages/shop.php', 800);
      } else {
        alertEl.className = 'alert alert-error';
        alertEl.textContent = json.message || 'Registration failed.';
        if (json.errors) Object.entries(json.errors).forEach(([f, msgs]) => {
          const el = document.getElementById(f + '-error');
          if (el) { el.textContent = msgs[0]; el.classList.remove('hidden'); }
        });
      }
    } catch { alertEl.className = 'alert alert-error'; alertEl.textContent = 'Network error.'; }
    finally { btn.disabled = false; text.textContent = 'Create Account'; spin.classList.add('hidden'); }
  });
</script>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>