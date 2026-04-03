<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/csrf.php';
require_once dirname(__DIR__) . '/includes/validator.php';

Auth::requireAuthPage();
$pageTitle = 'Shopping Cart'; $activePage = 'cart';
include dirname(__DIR__) . '/includes/header.php';
?>
<div class="container section">
  <div class="section-header">
    <h1 class="section-title">Shopping Cart</h1>
    <a href="<?= APP_URL ?>/pages/shop.php" class="section-link">← Continue Shopping</a>
  </div>
  <div id="cart-loading" style="text-align:center;padding:var(--space-xl);"><div class="spinner"></div></div>
  <div id="cart-layout" class="cart-layout hidden">
    <div>
      <div id="cart-items"></div>
      <button class="btn btn-ghost btn-sm mt-md" onclick="clearAllCart()">🗑 Clear Cart</button>
    </div>
    <div>
      <div class="order-summary-box">
        <h3 style="font-size:var(--text-lg);font-weight:800;margin-bottom:var(--space-lg);">Order Summary</h3>
        <div style="margin-bottom:var(--space-md);">
          <div style="display:flex;justify-content:space-between;font-size:var(--text-xs);color:var(--text-3);margin-bottom:4px;">
            <span>Free shipping progress</span><span id="ship-label"></span>
          </div>
          <div class="free-ship-progress"><div class="free-ship-fill" id="ship-fill" style="width:0%"></div></div>
        </div>
        <div class="divider"></div>
        <div class="summary-row"><span>Subtotal</span><span id="sum-subtotal">—</span></div>
        <div class="summary-row"><span>Shipping</span><span id="sum-shipping">—</span></div>
        <div class="summary-row"><span>VAT (16%)</span><span id="sum-tax">—</span></div>
        <div class="summary-row total"><span>Total</span><span id="sum-total" style="color:var(--primary);">—</span></div>
        <div style="margin-top:var(--space-lg);">
          <a href="<?= APP_URL ?>/pages/checkout.php" class="btn btn-primary btn-block btn-lg">Proceed to Checkout →</a>
        </div>
        <p style="text-align:center;font-size:var(--text-xs);color:var(--text-3);margin-top:var(--space-md);">🔒 Secure checkout · 🚚 Fast delivery</p>
      </div>
    </div>
  </div>
  <div id="empty-cart" class="empty-state hidden">
    <div class="empty-icon">🛒</div>
    <h2 class="empty-title">Your cart is empty</h2>
    <p class="empty-subtitle">Add products to get started!</p>
    <a href="<?= APP_URL ?>/pages/shop.php" class="btn btn-primary">Start Shopping</a>
  </div>
</div>
<script>
const CSRF = '<?= Validator::escape(CSRF::token()) ?>';
const APPURL = '<?= APP_URL ?>';
function fmt(n){return 'TZS '+Number(n).toLocaleString('en-TZ',{minimumFractionDigits:2,maximumFractionDigits:2});}
function esc(s){const d=document.createElement('div');d.textContent=s;return d.innerHTML;}

async function loadCart(){
  const loading=document.getElementById('cart-loading');
  const layout=document.getElementById('cart-layout');
  const empty=document.getElementById('empty-cart');
  layout.classList.add('hidden'); empty.classList.add('hidden');
  loading.classList.remove('hidden');
  try{
    const res=await fetch(`${APPURL}/api/cart/index.php`);
    const json=await res.json();
    if(!json.success)throw new Error(json.message);
    loading.classList.add('hidden');
    const {items,subtotal,shipping,tax,total,free_shipping_threshold}=json.data;
    if(!items.length){empty.classList.remove('hidden');NovaMart.updateCartCount(0);return;}
    layout.classList.remove('hidden');
    document.getElementById('cart-items').innerHTML=items.map(item=>`
      <div class="cart-item" id="cart-item-${item.cart_id}">
        <div class="cart-item-image">
          <img loading="lazy" src="${item.image||'https://fastly.picsum.photos/id/1/200/200.jpg'}" alt="${esc(item.name)}" onerror="this.src='https://fastly.picsum.photos/id/1/200/200.jpg'">
        </div>
        <div class="cart-item-info">
          <a href="${APPURL}/pages/product.php?id=${item.id}" class="cart-item-name">${esc(item.name)}</a>
          <div class="cart-item-price">${fmt(item.effective_price)}</div>
          <div style="font-size:var(--text-xs);color:var(--text-3);">Line: ${fmt(item.line_total)}</div>
          <div class="cart-item-controls">
            <button class="cart-qty-btn" onclick="updateQty(${item.cart_id},${item.quantity-1})">−</button>
            <span class="cart-qty-value">${item.quantity}</span>
            <button class="cart-qty-btn" onclick="updateQty(${item.cart_id},${item.quantity+1})" ${item.quantity>=item.stock?'disabled':''}>+</button>
            <button class="cart-remove" onclick="removeItem(${item.cart_id})">✕ Remove</button>
          </div>
        </div>
      </div>`).join('');
    document.getElementById('sum-subtotal').textContent=fmt(subtotal);
    document.getElementById('sum-shipping').textContent=shipping===0?'✅ Free!':fmt(shipping);
    document.getElementById('sum-tax').textContent=fmt(tax);
    document.getElementById('sum-total').textContent=fmt(total);
    const pct=Math.min(100,(subtotal/free_shipping_threshold)*100);
    document.getElementById('ship-fill').style.width=pct+'%';
    const rem=free_shipping_threshold-subtotal;
    document.getElementById('ship-label').textContent=rem>0?`TZS ${Math.ceil(rem).toLocaleString()} to free!`:'🎉 You got free shipping!';
    NovaMart.updateCartCount(items.reduce((s,i)=>s+i.quantity,0));
  }catch(err){loading.innerHTML=`<div class="alert alert-error">${err.message}</div>`;}
}

async function updateQty(id,qty){
  try{
    const res=await fetch(`${APPURL}/api/cart/update.php`,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-Token':CSRF},body:JSON.stringify({cart_id:id,quantity:qty,'<?= CSRF_TOKEN_NAME ?>':CSRF})});
    const json=await res.json();
    if(json.success)await loadCart();
    else NovaMart.toast('error','Error',json.message);
  }catch{NovaMart.toast('error','Error','Failed to update cart');}
}

async function removeItem(id){
  const el=document.getElementById('cart-item-'+id);
  if(el)el.style.opacity='0.4';
  try{
    const res=await fetch(`${APPURL}/api/cart/remove.php`,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-Token':CSRF},body:JSON.stringify({cart_id:id,'<?= CSRF_TOKEN_NAME ?>':CSRF})});
    const json=await res.json();
    if(json.success){NovaMart.toast('info','Removed','Item removed from cart');await loadCart();}
    else{if(el)el.style.opacity='1';NovaMart.toast('error','Error',json.message);}
  }catch{if(el)el.style.opacity='1';}
}

async function clearAllCart(){
  if(!confirm('Remove all items from your cart?'))return;
  const items=document.querySelectorAll('.cart-item');
  const ids=[...items].map(el=>parseInt(el.id.replace('cart-item-','')));
  for(const id of ids)await removeItem(id);
}

loadCart();
</script>
<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
