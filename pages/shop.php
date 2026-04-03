<?php
/**
 * NovaMart — Shop / Product Listing Page
 */
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/validator.php';
require_once dirname(__DIR__) . '/includes/csrf.php';

$pageTitle  = 'Shop';
$activePage = 'shop';

// Fetch categories for filter bar
try {
    $categories = Database::fetchAll("SELECT id, name, slug FROM categories WHERE is_active=1 ORDER BY sort_order, name");
} catch (Exception $e) { $categories = []; }

include dirname(__DIR__) . '/includes/header.php';
?>

<!-- Hero Banner -->
<section class="hero">
  <div class="container">
    <div class="hero-content">
      <div class="hero-tag">✨ New arrivals every week</div>
      <h1>Discover Premium Products<br>At Unbeatable Prices</h1>
      <p>Shop electronics, fashion, books, sports gear and more — all in one place.</p>
      <div class="hero-cta">
        <a href="#products" class="btn btn-primary btn-lg">Shop Now</a>
        <a href="<?= APP_URL ?>/pages/shop.php?featured=1" class="btn btn-outline btn-lg">Featured Items</a>
      </div>
    </div>
    <div class="stats-strip">
      <div class="stat-item"><div class="stat-value">20+</div><div class="stat-label">Products</div></div>
      <div class="stat-item"><div class="stat-value">6</div><div class="stat-label">Categories</div></div>
      <div class="stat-item"><div class="stat-value">24/7</div><div class="stat-label">Support</div></div>
      <div class="stat-item"><div class="stat-value">🚚 Free</div><div class="stat-label">Ship over TZS 5k</div></div>
    </div>
  </div>
</section>

<!-- Categories -->
<section class="section" style="padding-bottom:0;">
  <div class="container">
    <div class="categories-scroll" id="category-filter">
      <button class="cat-chip active" data-cat="">🛒 All Products</button>
      <?php foreach ($categories as $cat): ?>
      <button class="cat-chip" data-cat="<?= Validator::escape($cat['slug']) ?>">
        <?= Validator::escape($cat['name']) ?>
      </button>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Products Section -->
<section class="section" id="products">
  <div class="container">
    <!-- Filter & Sort Bar -->
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:var(--space-md);margin-bottom:var(--space-lg);">
      <div id="results-label" style="font-size:var(--text-sm);color:var(--text-3);">Loading products…</div>
      <div class="filter-bar">
        <select class="filter-select" id="sort-select">
          <option value="newest">🕐 Newest</option>
          <option value="price_asc">💰 Price: Low to High</option>
          <option value="price_desc">💎 Price: High to Low</option>
          <option value="name_asc">🔤 Name A–Z</option>
          <option value="popular">⭐ Most Popular</option>
        </select>
        <select class="filter-select" id="price-filter">
          <option value="">All Prices</option>
          <option value="0-2000">Under TZS 2,000</option>
          <option value="2000-10000">TZS 2,000–10,000</option>
          <option value="10000-50000">TZS 10,000–50,000</option>
          <option value="50000-">Over TZS 50,000</option>
        </select>
      </div>
    </div>

    <!-- Search results hint -->
    <div id="search-hint" class="hidden" style="margin-bottom:var(--space-md);">
      <div class="alert alert-info">
        Showing results for: <strong id="search-term"></strong>
        <a href="?" style="margin-left:var(--space-sm);font-weight:600;">Clear ✕</a>
      </div>
    </div>

    <!-- Product Grid -->
    <div id="products-grid" class="products-grid">
      <!-- Skeleton loading -->
      <?php for ($i = 0; $i < 8; $i++): ?>
      <div class="product-card" style="pointer-events:none;">
        <div class="product-image-wrap skeleton" style="aspect-ratio:1;"></div>
        <div class="product-info">
          <div class="skeleton" style="height:12px;width:60%;margin-bottom:8px;"></div>
          <div class="skeleton" style="height:16px;margin-bottom:6px;"></div>
          <div class="skeleton" style="height:16px;width:80%;margin-bottom:12px;"></div>
          <div class="skeleton" style="height:20px;width:40%;"></div>
        </div>
      </div>
      <?php endfor; ?>
    </div>

    <!-- Pagination -->
    <div id="pagination" class="pagination hidden"></div>

    <!-- Empty state -->
    <div id="empty-state" class="empty-state hidden">
      <div class="empty-icon">🔍</div>
      <h2 class="empty-title">No products found</h2>
      <p class="empty-subtitle">Try adjusting your search or filters.</p>
      <button class="btn btn-primary" onclick="resetFilters()">Clear Filters</button>
    </div>
  </div>
</section>

<script>
const CSRF   = '<?= Validator::escape(CSRF::token()) ?>';
const APPURL = '<?= APP_URL ?>';

let currentPage = 1;
let currentCat  = '';
let currentSort = 'newest';
let currentQ    = new URLSearchParams(location.search).get('q') || '';
let priceMin = null, priceMax = null;

// Show search hint
if (currentQ) {
  document.getElementById('search-hint').classList.remove('hidden');
  document.getElementById('search-term').textContent = currentQ;
}

async function loadProducts() {
  const grid  = document.getElementById('products-grid');
  const empty = document.getElementById('empty-state');
  const label = document.getElementById('results-label');
  const pagination = document.getElementById('pagination');

  // Show skeletons
  grid.innerHTML = `${Array(8).fill('<div class="product-card" style="pointer-events:none;"><div class="product-image-wrap skeleton" style="aspect-ratio:1;"></div><div class="product-info"><div class="skeleton" style="height:12px;width:60%;margin-bottom:8px;"></div><div class="skeleton" style="height:16px;margin-bottom:6px;"></div><div class="skeleton" style="height:16px;width:80%;margin-bottom:12px;"></div><div class="skeleton" style="height:20px;width:40%;"></div></div></div>').join('')}`;
  empty.classList.add('hidden');
  pagination.classList.add('hidden');

  const params = new URLSearchParams({ page: currentPage, sort: currentSort });
  if (currentCat)  params.set('category', currentCat);
  if (currentQ)    params.set('q', currentQ);
  if (priceMin !== null) params.set('min_price', priceMin);
  if (priceMax !== null) params.set('max_price', priceMax);

  try {
    const res  = await fetch(`${APPURL}/api/products/index.php?${params}`);
    const json = await res.json();
    if (!json.success) throw new Error(json.message);

    const { products, pagination: pg } = json.data;

    label.textContent = `${pg.total} product${pg.total !== 1 ? 's' : ''} found`;

    if (!products.length) {
      grid.innerHTML = '';
      empty.classList.remove('hidden');
      return;
    }

    grid.innerHTML = products.map(p => renderProductCard(p)).join('');

    // Pagination
    if (pg.total_pages > 1) {
      pagination.classList.remove('hidden');
      let pages = '';
      pages += `<button class="page-btn" onclick="changePage(${currentPage-1})" ${currentPage===1?'disabled':''}>‹</button>`;
      for (let i = 1; i <= pg.total_pages; i++) {
        if (i === currentPage || Math.abs(i - currentPage) <= 2 || i === 1 || i === pg.total_pages) {
          pages += `<button class="page-btn ${i===currentPage?'active':''}" onclick="changePage(${i})">${i}</button>`;
        } else if (Math.abs(i - currentPage) === 3) {
          pages += `<span style="padding:0 4px;color:var(--text-3);">…</span>`;
        }
      }
      pages += `<button class="page-btn" onclick="changePage(${currentPage+1})" ${currentPage===pg.total_pages?'disabled':''}>›</button>`;
      pagination.innerHTML = pages;
    }
  } catch (err) {
    grid.innerHTML = `<div class="alert alert-error" style="grid-column:1/-1;">${err.message}</div>`;
  }
}

function renderProductCard(p) {
  const price   = p.effective_price;
  const savings = p.sale_price ? Math.round((1 - p.sale_price/p.price) * 100) : 0;
  const stars   = '★'.repeat(Math.round(p.avg_rating)) + '☆'.repeat(5-Math.round(p.avg_rating));
  const badge   = !p.in_stock ? '<span class="product-badge badge-out">Out of Stock</span>'
                : p.sale_price  ? `<span class="product-badge badge-sale">-${savings}%</span>`
                : p.is_featured ? '<span class="product-badge badge-featured">Featured</span>' : '';

  return `<div class="product-card" data-id="${p.id}">
    <a href="${APPURL}/pages/product.php?slug=${p.slug}" style="text-decoration:none;color:inherit;display:contents;">
      <div class="product-image-wrap">
        <img loading="lazy" src="${p.image || 'https://fastly.picsum.photos/id/1/600/600.jpg'}" alt="${escHtml(p.name)}" onerror="this.src='https://fastly.picsum.photos/id/1/600/600.jpg'">
        ${badge}
        <button class="product-wish ${p.wished?'wished':''}" onclick="toggleWishlist(event,${p.id},this)" title="Add to wishlist">
          ${p.wished ? '❤️' : '🤍'}
        </button>
      </div>
      <div class="product-info">
        <div class="product-category">${escHtml(p.category_name||'')}</div>
        <div class="product-name">${escHtml(p.name)}</div>
        <div class="product-rating">
          <span class="stars">${stars}</span>
          <span class="rating-count">(${p.review_count})</span>
        </div>
        <div class="product-price">
          <span class="price-current">TZS ${formatPrice(price)}</span>
          ${p.sale_price ? `<span class="price-original">TZS ${formatPrice(p.price)}</span><span class="price-discount">-${savings}%</span>` : ''}
        </div>
      </div>
    </a>
    <div class="product-actions">
      <button class="btn btn-primary" onclick="addToCart(event,${p.id})" ${!p.in_stock?'disabled':''} style="height:36px;font-size:var(--text-xs);border-radius:var(--r-md);flex:1;">
        ${p.in_stock ? '🛒 Add to Cart' : '❌ Out of Stock'}
      </button>
    </div>
  </div>`;
}

function changePage(n) { currentPage = n; loadProducts(); window.scrollTo({top:0,behavior:'smooth'}); }
function resetFilters() {
  currentCat=''; currentQ=''; currentPage=1;
  document.querySelectorAll('.cat-chip').forEach(c=>c.classList.remove('active'));
  document.querySelector('.cat-chip[data-cat=""]').classList.add('active');
  loadProducts();
}

// Category filter
document.querySelectorAll('.cat-chip').forEach(chip => {
  chip.addEventListener('click', function() {
    document.querySelectorAll('.cat-chip').forEach(c => c.classList.remove('active'));
    this.classList.add('active');
    currentCat = this.dataset.cat;
    currentPage = 1;
    loadProducts();
  });
});

// Sort
document.getElementById('sort-select').addEventListener('change', function() {
  currentSort = this.value; currentPage = 1; loadProducts();
});

// Price filter
document.getElementById('price-filter').addEventListener('change', function() {
  const val = this.value;
  if (!val) { priceMin = priceMax = null; }
  else {
    const [mn, mx] = val.split('-');
    priceMin = mn || null; priceMax = mx || null;
  }
  currentPage = 1; loadProducts();
});

// Pre-select category from URL
const urlCat = new URLSearchParams(location.search).get('category');
if (urlCat) {
  currentCat = urlCat;
  document.querySelectorAll('.cat-chip').forEach(c => {
    c.classList.toggle('active', c.dataset.cat === urlCat);
  });
}

// Initial load
loadProducts();

async function addToCart(e, productId) {
  e.preventDefault(); e.stopPropagation();
  if (!<?= Auth::check() ? 'true' : 'false' ?>) {
    window.location.href = '<?= APP_URL ?>/pages/login.php';
    return;
  }
  const btn = e.currentTarget;
  btn.disabled = true; btn.textContent = '…';
  try {
    const res  = await fetch('<?= APP_URL ?>/api/cart/add.php', {
      method: 'POST',
      headers: {'Content-Type':'application/json','X-CSRF-Token':CSRF},
      body: JSON.stringify({product_id:productId, quantity:1, '<?= CSRF_TOKEN_NAME ?>':CSRF}),
    });
    const json = await res.json();
    if (json.success) {
      NovaMart.updateCartCount(json.data.cart_count);
      NovaMart.toast('success','Added!','Product added to your cart');
    } else { NovaMart.toast('error','Error', json.message); }
  } catch { NovaMart.toast('error','Error','Could not add to cart'); }
  finally { btn.disabled=false; btn.innerHTML='🛒 Add to Cart'; }
}

async function toggleWishlist(e, pid, btn) {
  e.preventDefault(); e.stopPropagation();
  if (!<?= Auth::check() ? 'true' : 'false' ?>) { window.location.href='<?= APP_URL ?>/pages/login.php'; return; }
  try {
    const res  = await fetch('<?= APP_URL ?>/api/wishlist/toggle.php', {
      method:'POST', headers:{'Content-Type':'application/json','X-CSRF-Token':CSRF},
      body:JSON.stringify({product_id:pid,'<?= CSRF_TOKEN_NAME ?>':CSRF}),
    });
    const json = await res.json();
    if (json.success) {
      btn.textContent = json.data.wishlisted ? '❤️' : '🤍';
      btn.classList.toggle('wished', json.data.wishlisted);
      NovaMart.toast('info', json.data.wishlisted ? 'Wishlisted!' : 'Removed', json.message);
    }
  } catch {}
}

function formatPrice(n) { return Number(n).toLocaleString('en-TZ', {minimumFractionDigits:2,maximumFractionDigits:2}); }
function escHtml(s) { const d=document.createElement('div'); d.textContent=s; return d.innerHTML; }
</script>

<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
