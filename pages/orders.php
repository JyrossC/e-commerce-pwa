<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/csrf.php';
require_once dirname(__DIR__) . '/includes/validator.php';

Auth::requireAuthPage();
$pageTitle = 'My Orders'; $activePage = 'orders';
include dirname(__DIR__) . '/includes/header.php';
?>
<div class="container section">
  <h1 class="section-title" style="margin-bottom:var(--space-lg);">📦 My Orders</h1>
  <div id="orders-loading" style="text-align:center;padding:40px;"><div class="spinner"></div></div>
  <div id="orders-list"></div>
  <div id="empty-orders" class="empty-state hidden">
    <div class="empty-icon">📦</div>
    <h2 class="empty-title">No orders yet</h2>
    <p class="empty-subtitle">Start shopping to see your orders here.</p>
    <a href="<?= APP_URL ?>/pages/shop.php" class="btn btn-primary">Shop Now</a>
  </div>
  <div id="pagination" class="pagination hidden"></div>
</div>
<script>
const APPURL='<?= APP_URL ?>';
function fmt(n){return 'TZS '+Number(n).toLocaleString('en-TZ',{minimumFractionDigits:2,maximumFractionDigits:2});}
function esc(s){const d=document.createElement('div');d.textContent=s;return d.innerHTML;}

async function loadOrders(page=1){
  const loading=document.getElementById('orders-loading');
  const list=document.getElementById('orders-list');
  const empty=document.getElementById('empty-orders');
  loading.style.display='block'; list.innerHTML=''; empty.classList.add('hidden');
  try{
    const res=await fetch(`${APPURL}/api/orders/index.php?page=${page}`);
    const json=await res.json();
    if(!json.success)throw new Error(json.message);
    loading.style.display='none';
    const {orders,pagination:pg}=json.data;
    if(!orders.length){empty.classList.remove('hidden');return;}

    const statusClass={pending:'status-pending',processing:'status-processing',shipped:'status-shipped',delivered:'status-delivered',cancelled:'status-cancelled',refunded:'status-refunded'};
    list.innerHTML=orders.map(o=>`
      <div class="order-card">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:var(--space-sm);">
          <div>
            <div class="order-number">Order #${esc(o.order_number)}</div>
            <div style="font-size:var(--text-sm);color:var(--text-2);margin-top:2px;">${new Date(o.created_at).toLocaleDateString('en-GB',{day:'numeric',month:'short',year:'numeric'})}</div>
          </div>
          <span class="status-badge ${statusClass[o.status]||'status-pending'}">${esc(o.status)}</span>
        </div>
        <div style="display:flex;align-items:center;justify-content:space-between;margin-top:var(--space-md);padding-top:var(--space-sm);border-top:1px solid var(--border);">
          <div>
            <span style="font-size:var(--text-sm);color:var(--text-3);">${o.item_count} item${o.item_count!==1?'s':''}</span>
            <div style="font-size:var(--text-lg);font-weight:800;color:var(--primary);">${fmt(o.total)}</div>
          </div>
          <a href="${APPURL}/pages/order-detail.php?id=${o.id}" class="btn btn-outline btn-sm">View Details</a>
        </div>
      </div>`).join('');

    const pag=document.getElementById('pagination');
    if(pg.total_pages>1){
      pag.classList.remove('hidden');
      let html='';
      html+=`<button class="page-btn" onclick="loadOrders(${pg.page-1})" ${pg.page===1?'disabled':''}>‹</button>`;
      for(let i=1;i<=pg.total_pages;i++) html+=`<button class="page-btn ${i===pg.page?'active':''}" onclick="loadOrders(${i})">${i}</button>`;
      html+=`<button class="page-btn" onclick="loadOrders(${pg.page+1})" ${pg.page===pg.total_pages?'disabled':''}>›</button>`;
      pag.innerHTML=html;
    }
  }catch(err){loading.innerHTML=`<div class="alert alert-error">${err.message}</div>`;}
}
loadOrders();
</script>
<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
