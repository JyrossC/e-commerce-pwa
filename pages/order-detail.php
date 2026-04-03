<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/validator.php';

Auth::requireAuthPage();
$orderId = Validator::sanitizeInt($_GET['id'] ?? 0);
if (!$orderId) { header('Location: ' . APP_URL . '/pages/orders.php'); exit; }
$pageTitle = 'Order Detail'; $activePage = 'orders';
include dirname(__DIR__) . '/includes/header.php';
?>
<div class="container section">
  <div class="section-header">
    <h1 class="section-title">Order Details</h1>
    <a href="<?= APP_URL ?>/pages/orders.php" class="section-link">← My Orders</a>
  </div>
  <div id="order-loading" style="text-align:center;padding:40px;"><div class="spinner"></div></div>
  <div id="order-detail" class="hidden"></div>
</div>
<script>
const APPURL='<?= APP_URL ?>';
const orderId=<?= $orderId ?>;
function fmt(n){return 'TZS '+Number(n).toLocaleString('en-TZ',{minimumFractionDigits:2,maximumFractionDigits:2});}
function esc(s){const d=document.createElement('div');d.textContent=s;return d.innerHTML;}
const SC={pending:'status-pending',processing:'status-processing',shipped:'status-shipped',delivered:'status-delivered',cancelled:'status-cancelled',refunded:'status-refunded'};

async function loadOrder(){
  try{
    const res=await fetch(`${APPURL}/api/orders/show.php?id=${orderId}`);
    const json=await res.json();
    if(!json.success)throw new Error(json.message);
    document.getElementById('order-loading').classList.add('hidden');
    const {order:o,items}=json.data;
    document.getElementById('order-detail').innerHTML=`
      <div style="display:grid;gap:var(--space-lg);">
        <div class="card">
          <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:var(--space-md);">
            <div>
              <div class="text-muted text-sm">Order Number</div>
              <div style="font-size:var(--text-xl);font-weight:800;">#${esc(o.order_number)}</div>
              <div class="text-sm text-muted">${new Date(o.created_at).toLocaleDateString('en-GB',{weekday:'long',day:'numeric',month:'long',year:'numeric'})}</div>
            </div>
            <span class="status-badge ${SC[o.status]||''}" style="font-size:var(--text-sm);padding:6px 16px;">${esc(o.status)}</span>
          </div>
        </div>
        <div class="card">
          <h3 style="font-size:var(--text-base);font-weight:700;margin-bottom:var(--space-md);">🛍 Items Ordered</h3>
          ${items.map(i=>`
          <div style="display:flex;gap:var(--space-md);align-items:center;padding:var(--space-sm) 0;border-bottom:1px solid var(--border);">
            <img src="${i.product_image||'https://fastly.picsum.photos/id/1/60/60.jpg'}" style="width:56px;height:56px;border-radius:var(--r-sm);object-fit:cover;" alt="">
            <div style="flex:1;"><div style="font-weight:600;font-size:var(--text-sm);">${esc(i.product_name)}</div>
            <div class="text-sm text-muted">Qty: ${i.quantity} × ${fmt(i.price)}</div></div>
            <div style="font-weight:700;color:var(--primary);">${fmt(i.subtotal)}</div>
          </div>`).join('')}
          <div style="margin-top:var(--space-md);">
            <div class="summary-row text-sm"><span>Subtotal</span><span>${fmt(o.subtotal)}</span></div>
            <div class="summary-row text-sm"><span>Shipping</span><span>${parseFloat(o.shipping_cost)===0?'Free':fmt(o.shipping_cost)}</span></div>
            <div class="summary-row text-sm"><span>VAT (16%)</span><span>${fmt(o.tax)}</span></div>
            <div class="summary-row total"><span>Total</span><span style="color:var(--primary);">${fmt(o.total)}</span></div>
          </div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-md);">
          <div class="card">
            <h3 style="font-size:var(--text-base);font-weight:700;margin-bottom:var(--space-md);">🚚 Shipping To</h3>
            <p style="font-size:var(--text-sm);color:var(--text-2);line-height:1.8;">${esc(o.shipping_name)}<br>${esc(o.shipping_address)}<br>${esc(o.shipping_city)}, ${esc(o.shipping_country)}</p>
          </div>
          <div class="card">
            <h3 style="font-size:var(--text-base);font-weight:700;margin-bottom:var(--space-md);">💳 Payment</h3>
            <p class="text-sm" style="color:var(--text-2);">Method: <strong>${esc(o.payment_method.toUpperCase())}</strong></p>
            <p class="text-sm" style="color:var(--text-2);">Status: <span class="${SC[o.payment_status]||'status-pending'}" style="display:inline;">${esc(o.payment_status)}</span></p>
          </div>
        </div>
      </div>`;
    document.getElementById('order-detail').classList.remove('hidden');
    document.title=`Order #${o.order_number} — <?= APP_NAME ?>`;
  }catch(err){document.getElementById('order-loading').innerHTML=`<div class="alert alert-error">${err.message}</div>`;}
}
loadOrder();
</script>
<?php include dirname(__DIR__) . '/includes/footer.php'; ?>
