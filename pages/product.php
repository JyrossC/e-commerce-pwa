<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/csrf.php';
require_once dirname(__DIR__) . '/includes/validator.php';

$slug = Validator::sanitizeText($_GET['slug'] ?? '');
$id   = Validator::sanitizeInt($_GET['id'] ?? 0);
if (!$slug && !$id) { header('Location: ' . APP_URL . '/pages/shop.php'); exit; }

// Fetch product server-side for SEO meta
try {
    $param = $id ?: $slug;
    $where = $id ? 'p.id = ?' : 'p.slug = ?';
    $product = Database::fetchOne(
        "SELECT p.*, c.name AS category_name, c.slug AS category_slug FROM products p
         LEFT JOIN categories c ON c.id=p.category_id WHERE $where AND p.is_active=1",
        [$param]
    );
} catch (Exception $e) { $product = null; }

if (!$product) { header('Location: ' . APP_URL . '/pages/shop.php'); exit; }

$pageTitle = Validator::escape($product['name']);
$activePage = 'shop';
include dirname(__DIR__) . '/includes/header.php';
?>
<div class="container">
  <nav style="font-size:var(--text-sm);color:var(--text-3);padding:var(--space-lg) 0 0;" aria-label="Breadcrumb">
    <a href="<?= APP_URL ?>/pages/shop.php">Shop</a> ›
    <a href="<?= APP_URL ?>/pages/shop.php?category=<?= Validator::escape($product['category_slug']) ?>"><?= Validator::escape($product['category_name']) ?></a> ›
    <span style="color:var(--text-2);"><?= Validator::escape($product['name']) ?></span>
  </nav>

  <div class="product-detail product-detail-layout">
    <!-- Image -->
    <div class="product-detail-image">
      <img id="main-img" src="<?= Validator::escape($product['image'] ?: 'https://fastly.picsum.photos/id/1/600/600.jpg') ?>"
           alt="<?= Validator::escape($product['name']) ?>"
           onerror="this.src='https://fastly.picsum.photos/id/1/600/600.jpg'">
    </div>

    <!-- Info -->
    <div>
      <div class="tag"><?= Validator::escape($product['category_name']) ?></div>
      <h1 class="product-detail-title" style="margin-top:var(--space-sm);"><?= Validator::escape($product['name']) ?></h1>

      <div style="display:flex;align-items:center;gap:var(--space-md);margin-bottom:var(--space-lg);">
        <div class="product-rating">
          <span class="stars" id="star-display">☆☆☆☆☆</span>
          <span class="rating-count" id="review-count-display">(0 reviews)</span>
        </div>
        <?php if ($product['stock'] > 0): ?>
        <span class="tag" style="background:rgba(16,185,129,0.15);color:var(--success);border:none;">✅ In Stock (<?= (int)$product['stock'] ?> left)</span>
        <?php else: ?>
        <span class="tag" style="background:rgba(239,68,68,0.15);color:var(--error);border:none;">❌ Out of Stock</span>
        <?php endif; ?>
      </div>

      <?php if ($product['sale_price']): ?>
      <div class="product-detail-price">
        TZS <?= number_format($product['sale_price'], 2) ?>
        <span class="original">TZS <?= number_format($product['price'], 2) ?></span>
        <span class="price-discount">-<?= round((1 - $product['sale_price']/$product['price'])*100) ?>%</span>
      </div>
      <?php else: ?>
      <div class="product-detail-price">TZS <?= number_format($product['price'], 2) ?></div>
      <?php endif; ?>

      <p style="color:var(--text-2);font-size:var(--text-sm);line-height:1.8;margin-bottom:var(--space-lg);"><?= nl2br(Validator::escape($product['description'])) ?></p>

      <!-- Quantity -->
      <div class="qty-control">
        <button class="qty-btn" id="qty-minus" onclick="changeQty(-1)">−</button>
        <span class="qty-value" id="qty-val">1</span>
        <button class="qty-btn" id="qty-plus" onclick="changeQty(1)">+</button>
        <span style="font-size:var(--text-xs);color:var(--text-3);">Max: <?= (int)$product['stock'] ?></span>
      </div>

      <!-- Actions -->
      <div style="display:flex;gap:var(--space-md);flex-wrap:wrap;margin-bottom:var(--space-lg);">
        <button class="btn btn-primary btn-lg" id="add-cart-btn"
                onclick="addToCart()" <?= $product['stock'] < 1 ? 'disabled' : '' ?>>
          🛒 Add to Cart
        </button>
        <button class="btn btn-outline" id="wishlist-btn" onclick="toggleWish()" style="min-width:48px;">🤍</button>
      </div>

      <!-- Features -->
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-sm);">
        <div class="card" style="padding:var(--space-sm) var(--space-md);">
          <span style="font-size:var(--text-xs);font-weight:600;">🚚 Free shipping</span>
          <div style="font-size:var(--text-xs);color:var(--text-3);">Orders over TZS 5,000</div>
        </div>
        <div class="card" style="padding:var(--space-sm) var(--space-md);">
          <span style="font-size:var(--text-xs);font-weight:600;">🔄 Easy returns</span>
          <div style="font-size:var(--text-xs);color:var(--text-3);">30-day return policy</div>
        </div>
        <div class="card" style="padding:var(--space-sm) var(--space-md);">
          <span style="font-size:var(--text-xs);font-weight:600;">🔒 Secure checkout</span>
          <div style="font-size:var(--text-xs);color:var(--text-3);">Encrypted payments</div>
        </div>
        <div class="card" style="padding:var(--space-sm) var(--space-md);">
          <span style="font-size:var(--text-xs);font-weight:600;">⭐ Genuine products</span>
          <div style="font-size:var(--text-xs);color:var(--text-3);">Quality guaranteed</div>
        </div>
      </div>
    </div>
  </div>

  <!-- Reviews -->
  <section class="section">
    <div class="section-header">
      <h2 class="section-title">Customer Reviews</h2>
    </div>
    <div id="reviews-container"><div class="spinner"></div></div>

    <?php if (Auth::check()): ?>
    <div class="card" style="margin-top:var(--space-lg);">
      <h3 style="font-size:var(--text-base);font-weight:700;margin-bottom:var(--space-md);">Write a Review</h3>
      <form id="review-form" novalidate>
        <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= CSRF::token() ?>">
        <div class="form-group">
          <label class="form-label">Rating</label>
          <div class="star-rating" id="star-picker">
            <?php for ($i=1; $i<=5; $i++): ?>
            <button type="button" class="star-btn" data-val="<?= $i ?>" onclick="setRating(<?= $i ?>)">★</button>
            <?php endfor; ?>
          </div>
          <input type="hidden" id="review-rating" name="rating" value="0">
        </div>
        <div class="form-group">
          <label class="form-label" for="rev-title">Title</label>
          <input type="text" id="rev-title" class="form-control" placeholder="Summarise your review">
        </div>
        <div class="form-group">
          <label class="form-label" for="rev-comment">Comment</label>
          <textarea id="rev-comment" class="form-control" rows="3" placeholder="Share your experience…"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Submit Review</button>
      </form>
    </div>
    <?php endif; ?>
  </section>

  <!-- Related Products -->
  <section class="section">
    <div class="section-header">
      <h2 class="section-title">Related Products</h2>
      <a href="<?= APP_URL ?>/pages/shop.php?category=<?= Validator::escape($product['category_slug']) ?>" class="section-link">View All →</a>
    </div>
    <div id="related-grid" class="products-grid"><div class="spinner"></div></div>
  </section>
</div>

<script>
const CSRF='<?= Validator::escape(CSRF::token()) ?>',APPURL='<?= APP_URL ?>';
const PRODUCT_ID=<?= (int)$product['id'] ?>,MAX_STOCK=<?= (int)$product['stock'] ?>;
let qty=1,rating=0;

function changeQty(d){ qty=Math.max(1,Math.min(MAX_STOCK,qty+d)); document.getElementById('qty-val').textContent=qty; }
function setRating(n){
  rating=n;
  document.getElementById('review-rating').value=n;
  document.querySelectorAll('.star-btn').forEach((b,i)=>{ b.classList.toggle('selected',i<n); });
}

function escHtml(s){const d=document.createElement('div');d.textContent=s;return d.innerHTML;}
function fmt(n){return 'TZS '+Number(n).toLocaleString('en-TZ',{minimumFractionDigits:2,maximumFractionDigits:2});}

async function addToCart(){
  if(!<?= Auth::check()?'true':'false' ?>){window.location.href=`${APPURL}/pages/login.php`;return;}
  const btn=document.getElementById('add-cart-btn');
  btn.disabled=true; btn.textContent='Adding…';
  try{
    const res=await fetch(`${APPURL}/api/cart/add.php`,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-Token':CSRF},body:JSON.stringify({product_id:PRODUCT_ID,quantity:qty,'<?= CSRF_TOKEN_NAME ?>':CSRF})});
    const json=await res.json();
    if(json.success){NovaMart.updateCartCount(json.data.cart_count);NovaMart.toast('success','Added!','Product added to cart');}
    else NovaMart.toast('error','Error',json.message);
  }catch{NovaMart.toast('error','Error','Failed to add to cart');}
  finally{btn.disabled=false;btn.innerHTML='🛒 Add to Cart';}
}

async function toggleWish(){
  if(!<?= Auth::check()?'true':'false' ?>){window.location.href=`${APPURL}/pages/login.php`;return;}
  const btn=document.getElementById('wishlist-btn');
  try{
    const res=await fetch(`${APPURL}/api/wishlist/toggle.php`,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-Token':CSRF},body:JSON.stringify({product_id:PRODUCT_ID,'<?= CSRF_TOKEN_NAME ?>':CSRF})});
    const json=await res.json();
    if(json.success){btn.textContent=json.data.wishlisted?'❤️':'🤍';NovaMart.toast('info',json.data.wishlisted?'Wishlisted!':'Removed',json.message);}
  }catch{}
}

async function loadReviews(){
  try{
    const res=await fetch(`${APPURL}/api/reviews/index.php?product_id=${PRODUCT_ID}`);
    const json=await res.json();
    if(!json.success)throw new Error(json.message);
    const {reviews,summary}=json.data;
    const avg=parseFloat(summary.avg).toFixed(1);
    const stars='★'.repeat(Math.round(avg))+'☆'.repeat(5-Math.round(avg));
    document.getElementById('star-display').textContent=stars;
    document.getElementById('review-count-display').textContent=`(${summary.total} review${summary.total!==1?'s':''}) · ${avg}/5`;
    const container=document.getElementById('reviews-container');
    if(!reviews.length){container.innerHTML='<p style="color:var(--text-3);font-size:var(--text-sm);">No reviews yet. Be the first!</p>';return;}
    container.innerHTML=reviews.map(r=>`
      <div class="card" style="margin-bottom:var(--space-md);">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:var(--space-sm);">
          <div><strong style="font-size:var(--text-sm);">${escHtml(r.reviewer_name)}</strong>
          <div class="stars" style="font-size:14px;">${'★'.repeat(r.rating)}${'☆'.repeat(5-r.rating)}</div></div>
          <span style="font-size:var(--text-xs);color:var(--text-3);">${new Date(r.created_at).toLocaleDateString()}</span>
        </div>
        ${r.title?`<div style="font-weight:600;margin-top:var(--space-sm);">${escHtml(r.title)}</div>`:''}
        ${r.comment?`<p style="font-size:var(--text-sm);color:var(--text-2);margin-top:4px;">${escHtml(r.comment)}</p>`:''}
      </div>`).join('');
  }catch(err){document.getElementById('reviews-container').innerHTML=`<div class="alert alert-error">${err.message}</div>`;}
}

async function loadRelated(){
  try{
    const res=await fetch(`${APPURL}/api/products/show.php?id=${PRODUCT_ID}`);
    const json=await res.json();
    if(!json.success)return;
    const related=json.data.related;
    const grid=document.getElementById('related-grid');
    if(!related.length){grid.closest('section').style.display='none';return;}
    grid.innerHTML=related.map(p=>`
      <a href="${APPURL}/pages/product.php?slug=${p.slug}" class="product-card" style="text-decoration:none;color:inherit;">
        <div class="product-image-wrap"><img loading="lazy" src="${p.image||'https://fastly.picsum.photos/id/1/600/600.jpg'}" alt="${escHtml(p.name)}" onerror="this.src='https://fastly.picsum.photos/id/1/600/600.jpg'"></div>
        <div class="product-info">
          <div class="product-name">${escHtml(p.name)}</div>
          <div class="product-price"><span class="price-current">${fmt(p.sale_price||p.price)}</span>${p.sale_price?`<span class="price-original">TZS ${Number(p.price).toLocaleString()}</span>`:''}</div>
        </div>
      </a>`).join('');
  }catch{}
}

<?php if (Auth::check()): ?>
document.getElementById('review-form').addEventListener('submit',async function(e){
  e.preventDefault();
  if(!rating){NovaMart.toast('warning','Rating required','Please select a star rating.');return;}
  const btn=this.querySelector('button[type=submit]');
  btn.disabled=true; btn.textContent='Submitting…';
  try{
    const res=await fetch(`${APPURL}/api/reviews/create.php`,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-Token':CSRF},
      body:JSON.stringify({product_id:PRODUCT_ID,rating,title:document.getElementById('rev-title').value,comment:document.getElementById('rev-comment').value,'<?= CSRF_TOKEN_NAME ?>':CSRF})});
    const json=await res.json();
    if(json.success){NovaMart.toast('success','Thanks!',json.message);this.reset();rating=0;document.querySelectorAll('.star-btn').forEach(b=>b.classList.remove('selected'));await loadReviews();}
    else NovaMart.toast('error','Error',json.message);
  }catch{NovaMart.toast('error','Error','Failed to submit review');}
  finally{btn.disabled=false;btn.textContent='Submit Review';}
});
<?php endif; ?>

loadReviews(); loadRelated();
</script>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
