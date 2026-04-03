<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/csrf.php';
require_once dirname(__DIR__) . '/includes/validator.php';

Auth::requireAuthPage();
$pageTitle = 'Checkout'; $activePage = 'checkout';
include dirname(__DIR__) . '/includes/header.php';
?>
<div class="container section">
  <div class="section-header">
    <h1 class="section-title">Checkout</h1>
    <a href="<?= APP_URL ?>/pages/cart.php" class="section-link">← Back to Cart</a>
  </div>

  <div id="checkout-loading" style="text-align:center;padding:40px;"><div class="spinner"></div></div>
  <div id="checkout-layout" class="hidden" style="display:none;">
    <div style="display:grid;grid-template-columns:1fr;gap:var(--space-lg);" id="checkout-grid">

      <!-- Shipping Form -->
      <div class="card">
        <h2 style="font-size:var(--text-lg);font-weight:800;margin-bottom:var(--space-lg);">📦 Shipping Information</h2>
        <div id="form-alert" class="hidden"></div>
        <form id="checkout-form" novalidate>
          <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= CSRF::token() ?>">
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-md);">
            <div class="form-group" style="grid-column:1/-1;">
              <label class="form-label" for="shipping_name">Full Name *</label>
              <input type="text" id="shipping_name" name="shipping_name" class="form-control" required
                     value="<?= Validator::escape($_SESSION['user_name'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label class="form-label" for="shipping_email">Email *</label>
              <input type="email" id="shipping_email" name="shipping_email" class="form-control" required
                     value="<?= Validator::escape($_SESSION['user_email'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label class="form-label" for="shipping_phone">Phone Number</label>
              <input type="tel" id="shipping_phone" name="shipping_phone" class="form-control" placeholder="+255...">
            </div>
            <div class="form-group" style="grid-column:1/-1;">
              <label class="form-label" for="shipping_address">Street Address *</label>
              <textarea id="shipping_address" name="shipping_address" class="form-control" rows="2" required placeholder="House/Flat No., Street Name"></textarea>
            </div>
            <div class="form-group">
              <label class="form-label" for="shipping_city">City *</label>
              <input type="text" id="shipping_city" name="shipping_city" class="form-control" required placeholder="Dar es Salaam">
            </div>
            <div class="form-group">
              <label class="form-label" for="shipping_country">Country *</label>
              <select id="shipping_country" name="shipping_country" class="form-control">
                <option value="Tanzania" selected>Tanzania</option>
                <option value="Kenya">Kenya</option>
                <option value="Uganda">Uganda</option>
                <option value="Ethiopia">Ethiopia</option>
                <option value="Rwanda">Rwanda</option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label" for="payment_method">Payment Method *</label>
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:var(--space-sm);">
              <label class="payment-option" id="pay-cod" style="cursor:pointer;border:2px solid var(--primary);border-radius:var(--r-md);padding:var(--space-md);text-align:center;transition:all 0.2s;">
                <input type="radio" name="payment_method" value="cod" checked style="display:none;">
                <div style="font-size:1.5rem;">💵</div>
                <div style="font-size:var(--text-sm);font-weight:600;margin-top:4px;">Cash on Delivery</div>
              </label>
              <label class="payment-option" id="pay-mpesa" style="cursor:pointer;border:2px solid var(--border);border-radius:var(--r-md);padding:var(--space-md);text-align:center;transition:all 0.2s;">
                <input type="radio" name="payment_method" value="mpesa" style="display:none;">
                <div style="font-size:1.5rem;">📱</div>
                <div style="font-size:var(--text-sm);font-weight:600;margin-top:4px;">M-Pesa</div>
              </label>
              <label class="payment-option" id="pay-card" style="cursor:pointer;border:2px solid var(--border);border-radius:var(--r-md);padding:var(--space-md);text-align:center;transition:all 0.2s;">
                <input type="radio" name="payment_method" value="card" style="display:none;">
                <div style="font-size:1.5rem;">💳</div>
                <div style="font-size:var(--text-sm);font-weight:600;margin-top:4px;">Card</div>
              </label>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label" for="notes">Order Notes (optional)</label>
            <textarea id="notes" name="notes" class="form-control" rows="2" placeholder="Any special instructions..."></textarea>
          </div>
        </form>
      </div>

      <!-- Order Summary -->
      <div>
        <div class="order-summary-box">
          <h3 style="font-size:var(--text-lg);font-weight:800;margin-bottom:var(--space-lg);">Your Order</h3>
          <div id="co-items"></div>
          <div class="divider"></div>
          <div class="summary-row"><span>Subtotal</span><span id="co-subtotal">—</span></div>
          <div class="summary-row"><span>Shipping</span><span id="co-shipping">—</span></div>
          <div class="summary-row"><span>VAT (16%)</span><span id="co-tax">—</span></div>
          <div class="summary-row total"><span>Total</span><span id="co-total" style="color:var(--primary);">—</span></div>
          <button type="button" class="btn btn-primary btn-block btn-lg" style="margin-top:var(--space-lg);" id="place-order-btn" onclick="placeOrder()">
            <span id="order-btn-text">Place Order 🎉</span>
          </button>
          <p style="text-align:center;font-size:var(--text-xs);color:var(--text-3);margin-top:var(--space-md);">🔒 Your information is protected</p>
        </div>
      </div>
    </div>
  </div>
  <div id="order-success" class="empty-state hidden">
    <div class="empty-icon">🎉</div>
    <h2 class="empty-title">Order Placed Successfully!</h2>
    <p class="empty-subtitle" id="order-success-msg"></p>
    <a href="<?= APP_URL ?>/pages/orders.php" class="btn btn-primary">View My Orders</a>
  </div>
</div>

<script>
const CSRF='<?= Validator::escape(CSRF::token()) ?>';
const APPURL='<?= APP_URL ?>';
function fmt(n){return 'TZS '+Number(n).toLocaleString('en-TZ',{minimumFractionDigits:2,maximumFractionDigits:2});}
function esc(s){const d=document.createElement('div');d.textContent=s;return d.innerHTML;}

// Payment option UI
document.querySelectorAll('[name="payment_method"]').forEach(r=>{
  r.closest('label').addEventListener('click',function(){
    document.querySelectorAll('.payment-option').forEach(l=>{l.style.borderColor='var(--border)';});
    this.style.borderColor='var(--primary)';
  });
});

async function loadCheckout(){
  try{
    const res=await fetch(`${APPURL}/api/cart/index.php`);
    const json=await res.json();
    if(!json.success)throw new Error(json.message);
    const {items,subtotal,shipping,tax,total}=json.data;
    if(!items.length){window.location.href=`${APPURL}/pages/cart.php`;return;}
    document.getElementById('checkout-loading').classList.add('hidden');
    const layout=document.getElementById('checkout-layout');
    layout.style.display='grid'; layout.classList.remove('hidden');
    document.getElementById('co-items').innerHTML=items.map(i=>`
      <div style="display:flex;gap:var(--space-sm);align-items:center;margin-bottom:var(--space-sm);">
        <img src="${i.image||'https://fastly.picsum.photos/id/1/60/60.jpg'}" style="width:48px;height:48px;border-radius:var(--r-sm);object-fit:cover;" alt="">
        <div style="flex:1;min-width:0;"><div style="font-size:var(--text-sm);font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${esc(i.name)}</div>
        <div style="font-size:var(--text-xs);color:var(--text-3);">Qty: ${i.quantity}</div></div>
        <div style="font-size:var(--text-sm);font-weight:700;color:var(--primary);">${fmt(i.line_total)}</div>
      </div>`).join('');
    document.getElementById('co-subtotal').textContent=fmt(subtotal);
    document.getElementById('co-shipping').textContent=shipping===0?'✅ Free!':fmt(shipping);
    document.getElementById('co-tax').textContent=fmt(tax);
    document.getElementById('co-total').textContent=fmt(total);
  }catch(err){document.getElementById('checkout-loading').innerHTML=`<div class="alert alert-error">${err.message}</div>`;}
}

async function placeOrder(){
  const btn=document.getElementById('place-order-btn');
  const btnText=document.getElementById('order-btn-text');
  const alertEl=document.getElementById('form-alert');
  alertEl.className='hidden';
  const form=document.getElementById('checkout-form');
  const data={
    shipping_name:   form.shipping_name.value.trim(),
    shipping_email:  form.shipping_email.value.trim(),
    shipping_phone:  form.shipping_phone.value.trim(),
    shipping_address:form.shipping_address.value.trim(),
    shipping_city:   form.shipping_city.value.trim(),
    shipping_country:form.shipping_country.value,
    payment_method:  form.querySelector('[name="payment_method"]:checked')?.value||'cod',
    notes:           form.notes.value.trim(),
    '<?= CSRF_TOKEN_NAME ?>':CSRF,
  };
  console.log('[Checkout] Data being sent:', data);

  // Client-side validation
  const errors = [];
  if(!data.shipping_name || data.shipping_name.length > 255) errors.push('Name is required (max 255 chars)');
  if(!data.shipping_email) errors.push('Email is required');
  if(!data.shipping_address || data.shipping_address.length < 5 || data.shipping_address.length > 1000) 
    errors.push('Address must be 5-1000 characters');
  if(data.shipping_phone && data.shipping_phone.length > 255) errors.push('Phone number is too long (max 255 chars)');
  if(!data.shipping_city) errors.push('City is required');
  if(!data.shipping_country) errors.push('Country is required');

  if(errors.length > 0) {
    alertEl.className = 'alert alert-error';
    alertEl.innerHTML = `<strong>Please fix the following:</strong><ul style="margin-top:5px;padding-left:20px;font-size:var(--text-xs);">${errors.map(e => `<li>${esc(e)}</li>`).join('')}</ul>`;
    return;
  }
  btn.disabled=true; btnText.textContent='Placing order…';
  try{
    const res=await fetch(`${APPURL}/api/orders/create.php`,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-Token':CSRF},body:JSON.stringify(data)});
    const json=await res.json();
    if(json.success){
      NovaMart.updateCartCount(0);
      document.getElementById('checkout-layout').style.display='none';
      const succ=document.getElementById('order-success');
      succ.classList.remove('hidden');
      document.getElementById('order-success-msg').textContent=json.message;
      NovaMart.toast('success','Order Placed!',json.message);
    }else{
      alertEl.className='alert alert-error';
      let msg = json.message || 'Order failed.';
      if(json.errors) {
        msg += '<ul style="margin-top:5px;padding-left:20px;font-size:var(--text-xs);">';
        Object.values(json.errors).flat().forEach(m => { msg += `<li>${esc(m)}</li>`; });
        msg += '</ul>';
        Object.keys(json.errors).forEach(f => {
          const el = document.getElementById(f);
          if(el) el.classList.add('is-invalid');
        });
      }
      alertEl.innerHTML = msg;
    }
  }catch{alertEl.className='alert alert-error';alertEl.textContent='Network error. Try again.';}
  finally{btn.disabled=false;btnText.textContent='Place Order 🎉';}
}

loadCheckout();
</script>
<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
