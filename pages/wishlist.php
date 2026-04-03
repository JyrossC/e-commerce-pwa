<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/csrf.php';
require_once dirname(__DIR__) . '/includes/validator.php';

Auth::requireAuthPage();
$pageTitle = 'My Wishlist'; $activePage = 'wishlist';
include dirname(__DIR__) . '/includes/header.php';
?>
<div class="container section">
  <h1 class="section-title" style="margin-bottom:var(--space-xl);">❤️ My Wishlist</h1>
  <div id="wish-loading" style="text-align:center;padding:40px;"><div class="spinner"></div></div>
  <div id="wish-grid" class="products-grid hidden"></div>
  <div id="wish-empty" class="empty-state hidden">
    <div class="empty-icon">🤍</div>
    <h2 class="empty-title">Your wishlist is empty</h2>
    <p class="empty-subtitle">Browse the shop and add items you love!</p>
    <a href="<?= APP_URL ?>/pages/shop.php" class="btn btn-primary">Browse Shop</a>
  </div>
</div>
<script>
const CSRF='<?= Validator::escape(CSRF::token()) ?>',APPURL='<?= APP_URL ?>';
function esc(s){const d=document.createElement('div');d.textContent=s;return d.innerHTML;}
function fmt(n){return 'TZS '+Number(n).toLocaleString('en-TZ',{minimumFractionDigits:2,maximumFractionDigits:2});}

async function loadWishlist(){
  try{
    const res=await fetch(`${APPURL}/api/wishlist/index.php`);
    const json=await res.json();
    document.getElementById('wish-loading').style.display='none';
    if(!json.success)throw new Error(json.message);
    const items=json.data.items;
    if(!items||!items.length){document.getElementById('wish-empty').classList.remove('hidden');return;}
    const grid=document.getElementById('wish-grid');
    grid.classList.remove('hidden');
    grid.innerHTML=items.map(p=>`
      <div class="product-card" data-pid="${p.product_id}">
        <a href="${APPURL}/pages/product.php?id=${p.product_id}" style="text-decoration:none;color:inherit;display:contents;">
          <div class="product-image-wrap">
            <img loading="lazy" src="${p.image||'https://fastly.picsum.photos/id/1/600/600.jpg'}" alt="${esc(p.name)}" onerror="this.src='https://fastly.picsum.photos/id/1/600/600.jpg'">
            <button class="product-wish wished" onclick="removeWish(event,${p.product_id},this)" title="Remove from wishlist">❤️</button>
          </div>
          <div class="product-info">
            <div class="product-name">${esc(p.name)}</div>
            <div class="product-price"><span class="price-current">${fmt(p.sale_price||p.price)}</span>${p.sale_price?`<span class="price-original">TZS ${Number(p.price).toLocaleString()}</span>`:''}</div>
          </div>
        </a>
        <div class="product-actions">
          <button class="btn btn-primary" onclick="addToCart(event,${p.product_id})" ${(p.stock<=0)?'disabled':''}>🛒 Add to Cart</button>
        </div>
      </div>`).join('');
  }catch(err){document.getElementById('wish-loading').innerHTML=`<div class="alert alert-error">${err.message}</div>`;}
}

async function removeWish(e,pid,btn){
  e.preventDefault();e.stopPropagation();
  try{
    const res=await fetch(`${APPURL}/api/wishlist/toggle.php`,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-Token':CSRF},body:JSON.stringify({product_id:pid,'<?= CSRF_TOKEN_NAME ?>':CSRF})});
    const json=await res.json();
    if(json.success){
      const card=document.querySelector(`.product-card[data-pid="${pid}"]`);
      if(card){card.style.opacity='0';card.style.transition='opacity 0.3s';setTimeout(()=>{card.remove();const g=document.getElementById('wish-grid');if(!g.children.length){g.classList.add('hidden');document.getElementById('wish-empty').classList.remove('hidden');}},300);}
    }
  }catch{}
}

async function addToCart(e,pid){
  e.preventDefault();e.stopPropagation();
  const btn=e.currentTarget;btn.disabled=true;btn.textContent='…';
  try{
    const res=await fetch(`${APPURL}/api/cart/add.php`,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-Token':CSRF},body:JSON.stringify({product_id:pid,quantity:1,'<?= CSRF_TOKEN_NAME ?>':CSRF})});
    const json=await res.json();
    if(json.success){NovaMart.updateCartCount(json.data.cart_count);NovaMart.toast('success','Added!','Product added to cart');}
    else NovaMart.toast('error','Error',json.message);
  }catch{NovaMart.toast('error','Error','Failed');}
  finally{btn.disabled=false;btn.innerHTML='🛒 Add to Cart';}
}
loadWishlist();
</script>
<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
