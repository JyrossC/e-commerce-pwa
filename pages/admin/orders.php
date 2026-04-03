<?php
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/csrf.php';
require_once dirname(__DIR__, 2) . '/includes/validator.php';

Auth::requireAdminPage();
$pageTitle = 'Manage Orders'; $activePage = 'admin';
include dirname(__DIR__, 2) . '/includes/header.php';
?>
<link rel="stylesheet" href="<?= APP_URL ?>/assets/css/admin.css">
<div class="container section">
  <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:var(--space-md);margin-bottom:var(--space-xl);">
    <div>
      <h1 style="font-size:var(--text-2xl);font-weight:800;">🧾 Orders</h1>
      <p style="color:var(--text-3);font-size:var(--text-sm);">Manage and update customer orders</p>
    </div>
  </div>
  <nav class="admin-nav">
    <a href="<?= APP_URL ?>/pages/admin/dashboard.php">📊 Dashboard</a>
    <a href="<?= APP_URL ?>/pages/admin/products.php">📦 Products</a>
    <a href="<?= APP_URL ?>/pages/admin/categories.php">🗂 Categories</a>
    <a href="<?= APP_URL ?>/pages/admin/orders.php" class="active">🧾 Orders</a>
    <a href="<?= APP_URL ?>/pages/admin/users.php">👥 Users</a>
  </nav>

  <div style="display:flex;gap:var(--space-sm);margin-bottom:var(--space-lg);flex-wrap:wrap;">
    <select id="status-filter" class="filter-select" onchange="loadOrders(1)">
      <option value="">All Status</option>
      <option value="pending">Pending</option>
      <option value="processing">Processing</option>
      <option value="shipped">Shipped</option>
      <option value="delivered">Delivered</option>
      <option value="cancelled">Cancelled</option>
    </select>
  </div>

  <div id="ord-loading" style="text-align:center;padding:40px;"><div class="spinner"></div></div>
  <div id="ord-wrap" class="hidden" style="overflow-x:auto;">
    <table class="admin-table">
      <thead><tr>
        <th>Order #</th><th>Customer</th><th>Items</th><th>Total</th><th>Status</th><th>Payment</th><th>Date</th><th>Actions</th>
      </tr></thead>
      <tbody id="ord-tbody"></tbody>
    </table>
    <div id="ord-pagination" class="pagination" style="margin-top:var(--space-lg);"></div>
  </div>
  <div id="ord-empty" class="empty-state hidden">
    <div class="empty-icon">🧾</div><h2 class="empty-title">No orders found</h2>
  </div>
</div>

<!-- Status Update Modal -->
<div class="modal-overlay hidden" id="status-modal" onclick="if(event.target===this)closeStatusModal()">
  <div class="modal-box" style="max-width:420px;">
    <div class="modal-header">
      <h2 class="modal-title">Update Order Status</h2>
      <button class="modal-close" onclick="closeStatusModal()">✕</button>
    </div>
    <div id="status-modal-body">
      <input type="hidden" id="edit-order-id">
      <div class="form-group">
        <label class="form-label" for="new-status">Order Status</label>
        <select id="new-status" class="form-control">
          <option value="pending">Pending</option>
          <option value="processing">Processing</option>
          <option value="shipped">Shipped</option>
          <option value="delivered">Delivered</option>
          <option value="cancelled">Cancelled</option>
          <option value="refunded">Refunded</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label" for="new-pay-status">Payment Status</label>
        <select id="new-pay-status" class="form-control">
          <option value="unpaid">Unpaid</option>
          <option value="paid">Paid</option>
          <option value="refunded">Refunded</option>
        </select>
      </div>
      <div style="display:flex;gap:var(--space-md);justify-content:flex-end;">
        <button class="btn btn-ghost" onclick="closeStatusModal()">Cancel</button>
        <button class="btn btn-primary" onclick="saveStatus()">Update</button>
      </div>
    </div>
  </div>
</div>

<script>
const CSRF='<?= Validator::escape(CSRF::token()) ?>',APPURL='<?= APP_URL ?>';
function esc(s){const d=document.createElement('div');d.textContent=s;return d.innerHTML;}
function fmt(n){return 'TZS '+Number(n).toLocaleString('en-TZ',{minimumFractionDigits:2,maximumFractionDigits:2});}
const SC={pending:'status-pending',processing:'status-processing',shipped:'status-shipped',delivered:'status-delivered',cancelled:'status-cancelled',refunded:'status-refunded'};
let currentPage=1;

async function loadOrders(page=1){
  currentPage=page;
  const loading=document.getElementById('ord-loading');
  const wrap=document.getElementById('ord-wrap');
  const empty=document.getElementById('ord-empty');
  const status=document.getElementById('status-filter').value;
  loading.style.display='block'; wrap.classList.add('hidden'); empty.classList.add('hidden');

  const params=new URLSearchParams({page,limit:15});
  if(status)params.set('status',status);

  try{
    const res=await fetch(`${APPURL}/api/orders/index.php?${params}`);
    const json=await res.json();
    loading.style.display='none';
    if(!json.success)throw new Error(json.message);
    const {orders,pagination:pg}=json.data;
    if(!orders.length){empty.classList.remove('hidden');return;}
    wrap.classList.remove('hidden');

    document.getElementById('ord-tbody').innerHTML=orders.map(o=>`
      <tr>
        <td><a href="${APPURL}/pages/order-detail.php?id=${o.id}" style="font-weight:700;color:var(--primary);">${esc(o.order_number)}</a></td>
        <td><div style="font-size:var(--text-sm);">${esc(o.customer_name)}</div><div style="font-size:10px;color:var(--text-3);">${esc(o.customer_email)}</div></td>
        <td style="text-align:center;">${o.item_count}</td>
        <td style="font-weight:700;">${fmt(o.total)}</td>
        <td><span class="status-badge ${SC[o.status]||''}">${esc(o.status)}</span></td>
        <td><span class="status-badge ${SC[o.payment_status]||''}">${esc(o.payment_status)}</span></td>
        <td style="font-size:var(--text-xs);color:var(--text-3);">${new Date(o.created_at).toLocaleDateString('en-GB')}</td>
        <td><button class="btn btn-ghost btn-sm" onclick="openStatusModal(${o.id},'${o.status}','${o.payment_status}')">✏️ Update</button></td>
      </tr>`).join('');

    const pag=document.getElementById('ord-pagination');
    if(pg.total_pages>1){
      let h=`<button class="page-btn" onclick="loadOrders(${pg.page-1})" ${pg.page===1?'disabled':''}>‹</button>`;
      for(let i=1;i<=pg.total_pages;i++) h+=`<button class="page-btn ${i===pg.page?'active':''}" onclick="loadOrders(${i})">${i}</button>`;
      h+=`<button class="page-btn" onclick="loadOrders(${pg.page+1})" ${pg.page===pg.total_pages?'disabled':''}>›</button>`;
      pag.innerHTML=h;
    } else { pag.innerHTML=''; }
  }catch(err){loading.innerHTML=`<div class="alert alert-error">${err.message}</div>`;}
}

function openStatusModal(id,status,payStatus){
  document.getElementById('edit-order-id').value=id;
  document.getElementById('new-status').value=status;
  document.getElementById('new-pay-status').value=payStatus;
  document.getElementById('status-modal').classList.remove('hidden');
}
function closeStatusModal(){ document.getElementById('status-modal').classList.add('hidden'); }

async function saveStatus(){
  const id=document.getElementById('edit-order-id').value;
  const status=document.getElementById('new-status').value;
  const payStatus=document.getElementById('new-pay-status').value;
  try{
    const res=await fetch(`${APPURL}/api/orders/update.php`,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-Token':CSRF},
      body:JSON.stringify({id,status,payment_status:payStatus,'<?= CSRF_TOKEN_NAME ?>':CSRF})});
    const json=await res.json();
    if(json.success){NovaMart.toast('success','Updated!',json.message);closeStatusModal();loadOrders(currentPage);}
    else NovaMart.toast('error','Error',json.message);
  }catch{NovaMart.toast('error','Error','Update failed');}
}
loadOrders();
</script>
<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>
