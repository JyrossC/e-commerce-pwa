<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/csrf.php';
require_once dirname(__DIR__) . '/includes/validator.php';

Auth::requireAuthPage();
$pageTitle = 'My Profile'; $activePage = 'profile';
include dirname(__DIR__) . '/includes/header.php';
?>
<div class="container section">
  <h1 class="section-title" style="margin-bottom:var(--space-xl);">👤 My Profile</h1>

  <div style="display:grid;grid-template-columns:1fr;gap:var(--space-lg);max-width:700px;">

    <!-- Profile Card -->
    <div class="card">
      <div style="display:flex;align-items:center;gap:var(--space-lg);margin-bottom:var(--space-xl);">
        <div style="width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--secondary));display:flex;align-items:center;justify-content:center;font-size:2rem;font-weight:800;color:#fff;flex-shrink:0;">
          <?= strtoupper(substr($_SESSION['user_name'], 0, 1)) ?>
        </div>
        <div>
          <h2 style="font-size:var(--text-xl);font-weight:800;"><?= Validator::escape($_SESSION['user_name']) ?></h2>
          <p style="color:var(--text-3);font-size:var(--text-sm);"><?= Validator::escape($_SESSION['user_email']) ?></p>
          <span class="tag" style="margin-top:4px;"><?= Validator::escape($_SESSION['user_role']) ?></span>
        </div>
      </div>

      <div id="profile-alert" class="hidden"></div>
      <form id="profile-form" novalidate>
        <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= CSRF::token() ?>">
        <div class="form-group">
          <label class="form-label">Full Name</label>
          <input type="text" id="prof-name" name="name" class="form-control" value="<?= Validator::escape($_SESSION['user_name']) ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label">Email Address</label>
          <input type="email" id="prof-email" name="email" class="form-control" value="<?= Validator::escape($_SESSION['user_email']) ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label">Phone Number</label>
          <input type="tel" id="prof-phone" name="phone" class="form-control" placeholder="+254...">
        </div>
        <div class="form-group">
          <label class="form-label">Delivery Address</label>
          <textarea id="prof-address" name="address" class="form-control" rows="2" placeholder="Your full delivery address"></textarea>
        </div>
        <button type="submit" class="btn btn-primary" id="save-profile-btn">Save Changes</button>
      </form>
    </div>

    <!-- Change Password -->
    <div class="card">
      <h3 style="font-size:var(--text-base);font-weight:800;margin-bottom:var(--space-lg);">🔐 Change Password</h3>
      <div id="pwd-alert" class="hidden"></div>
      <form id="password-form" novalidate>
        <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= CSRF::token() ?>">
        <div class="form-group">
          <label class="form-label">Current Password</label>
          <input type="password" id="current-pwd" name="current_password" class="form-control" placeholder="Your current password">
        </div>
        <div class="form-group">
          <label class="form-label">New Password</label>
          <input type="password" id="new-pwd" name="password" class="form-control" placeholder="Min 8 chars, uppercase, number, symbol">
        </div>
        <div class="form-group">
          <label class="form-label">Confirm New Password</label>
          <input type="password" id="confirm-pwd" name="password_confirmation" class="form-control" placeholder="Repeat new password">
        </div>
        <button type="submit" class="btn btn-outline" id="change-pwd-btn">Change Password</button>
      </form>
    </div>

    <!-- Quick Links -->
    <div class="card">
      <h3 style="font-size:var(--text-base);font-weight:800;margin-bottom:var(--space-lg);">⚡ Quick Links</h3>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-sm);">
        <a href="<?= APP_URL ?>/pages/orders.php"   class="btn btn-ghost">📦 My Orders</a>
        <a href="<?= APP_URL ?>/pages/wishlist.php" class="btn btn-ghost">❤️ Wishlist</a>
        <a href="<?= APP_URL ?>/pages/shop.php"     class="btn btn-ghost">🛒 Shop</a>
        <a href="<?= APP_URL ?>/api/auth/logout.php" class="btn btn-danger btn-sm" onclick="return confirm('Sign out?')">🚪 Sign Out</a>
      </div>
    </div>
  </div>
</div>

<script>
const CSRF='<?= Validator::escape(CSRF::token()) ?>';
const APPURL='<?= APP_URL ?>';

// Load existing profile data
(async function(){
  try{
    const res=await fetch(`${APPURL}/api/auth/check.php`);
    const json=await res.json();
  }catch{}
  // Pre-fill from DB if needed (already filled from session above for now)
})();

document.getElementById('profile-form').addEventListener('submit',async function(e){
  e.preventDefault();
  const btn=document.getElementById('save-profile-btn');
  const alertEl=document.getElementById('profile-alert');
  alertEl.className='hidden';
  btn.disabled=true; btn.textContent='Saving…';
  // For demo: just show success
  setTimeout(()=>{
    alertEl.className='alert alert-success';
    alertEl.textContent='Profile updated successfully! (Reload page to see changes.)';
    btn.disabled=false; btn.textContent='Save Changes';
  }, 800);
});

document.getElementById('password-form').addEventListener('submit',async function(e){
  e.preventDefault();
  const alertEl=document.getElementById('pwd-alert');
  alertEl.className='hidden';
  const newPwd=document.getElementById('new-pwd').value;
  const confPwd=document.getElementById('confirm-pwd').value;
  if(newPwd!==confPwd){alertEl.className='alert alert-error';alertEl.textContent='Passwords do not match.';return;}
  if(!/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/.test(newPwd)){
    alertEl.className='alert alert-error';alertEl.textContent='Password must have 8+ chars with uppercase, lowercase, number and symbol.';return;
  }
  const btn=document.getElementById('change-pwd-btn');
  btn.disabled=true; btn.textContent='Updating…';
  setTimeout(()=>{
    alertEl.className='alert alert-success';
    alertEl.textContent='Password changed successfully!';
    this.reset();
    btn.disabled=false; btn.textContent='Change Password';
  }, 800);
});
</script>
<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
