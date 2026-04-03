<?php
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/csrf.php';
require_once dirname(__DIR__, 2) . '/includes/validator.php';

Auth::requireAdminPage();
$pageTitle = 'Admin Dashboard'; $activePage = 'admin';
include dirname(__DIR__, 2) . '/includes/header.php';
?>
<link rel="stylesheet" href="<?= APP_URL ?>/assets/css/admin.css">

<div class="container section">
  <!-- Admin Header -->
  <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:var(--space-md);margin-bottom:var(--space-xl);">
    <div>
      <h1 style="font-size:var(--text-2xl);font-weight:800;">📊 Admin Dashboard</h1>
      <p style="color:var(--text-3);font-size:var(--text-sm);">Welcome back, <?= Validator::escape($_SESSION['user_name']) ?> · <?= date('D, d M Y') ?></p>
    </div>
    <div style="display:flex;gap:var(--space-sm);">
      <a href="<?= APP_URL ?>/pages/admin/products.php" class="btn btn-primary btn-sm">+ Add Product</a>
      <a href="<?= APP_URL ?>/pages/shop.php" class="btn btn-ghost btn-sm" target="_blank">🌐 View Store</a>
    </div>
  </div>

  <!-- Stat Cards -->
  <div class="admin-stats-grid" id="stats-grid">
    <?php for ($i = 0; $i < 4; $i++): ?>
    <div class="admin-stat-card skeleton" style="height:110px;"></div>
    <?php endfor; ?>
  </div>

  <!-- Charts Row -->
  <div style="display:grid;grid-template-columns:1fr;gap:var(--space-lg);margin-top:var(--space-xl);">
    <!-- Recent Orders -->
    <div class="card">
      <div class="section-header" style="margin-bottom:var(--space-md);">
        <h3 style="font-size:var(--text-base);font-weight:800;">📋 Recent Orders</h3>
        <a href="<?= APP_URL ?>/pages/admin/orders.php" class="section-link">View All</a>
      </div>
      <div id="recent-orders-table"><div class="spinner"></div></div>
    </div>

    <!-- Top Products -->
    <div class="card">
      <div class="section-header" style="margin-bottom:var(--space-md);">
        <h3 style="font-size:var(--text-base);font-weight:800;">🏆 Top Products</h3>
        <a href="<?= APP_URL ?>/pages/admin/products.php" class="section-link">Manage</a>
      </div>
      <div id="top-products-list"><div class="spinner"></div></div>
    </div>
  </div>

  <!-- Quick Actions -->
  <div class="card" style="margin-top:var(--space-lg);">
    <h3 style="font-size:var(--text-base);font-weight:800;margin-bottom:var(--space-lg);">⚡ Quick Actions</h3>
    <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:var(--space-sm);">
      <a href="<?= APP_URL ?>/pages/admin/products.php" class="btn btn-ghost">📦 Manage Products</a>
      <a href="<?= APP_URL ?>/pages/admin/categories.php" class="btn btn-ghost">🗂 Manage Categories</a>
      <a href="<?= APP_URL ?>/pages/admin/orders.php" class="btn btn-ghost">🧾 Manage Orders</a>
      <a href="<?= APP_URL ?>/pages/admin/users.php" class="btn btn-ghost">👥 Manage Users</a>
    </div>
  </div>
</div>

<script>
const APPURL='<?= APP_URL ?>';
function fmt(n){return 'TZS '+Number(n).toLocaleString('en-TZ',{minimumFractionDigits:2,maximumFractionDigits:2});}
function esc(s){const d=document.createElement('div');d.textContent=s;return d.innerHTML;}
const SC={pending:'status-pending',processing:'status-processing',shipped:'status-shipped',delivered:'status-delivered',cancelled:'status-cancelled',refunded:'status-refunded'};

async function loadDashboard(){
  try{
    const res=await fetch(`${APPURL}/api/stats/dashboard.php`);
    const json=await res.json();
    if(!json.success)throw new Error(json.message);
    const {stats,recentOrders,topProducts}=json.data;

    // Stat Cards
    document.getElementById('stats-grid').innerHTML=`
      <div class="admin-stat-card" style="background:linear-gradient(135deg,rgba(108,99,255,0.15),rgba(108,99,255,0.05));">
        <div class="admin-stat-icon">💰</div>
        <div class="admin-stat-value">${fmt(stats.total_revenue)}</div>
        <div class="admin-stat-label">Total Revenue</div>
        <div class="admin-stat-sub">Today: ${fmt(stats.revenue_today)}</div>
      </div>
      <div class="admin-stat-card" style="background:linear-gradient(135deg,rgba(67,232,216,0.15),rgba(67,232,216,0.05));">
        <div class="admin-stat-icon">🧾</div>
        <div class="admin-stat-value">${stats.total_orders}</div>
        <div class="admin-stat-label">Total Orders</div>
        <div class="admin-stat-sub">Today: ${stats.orders_today} · Pending: ${stats.pending_orders}</div>
      </div>
      <div class="admin-stat-card" style="background:linear-gradient(135deg,rgba(16,185,129,0.15),rgba(16,185,129,0.05));">
        <div class="admin-stat-icon">📦</div>
        <div class="admin-stat-value">${stats.total_products}</div>
        <div class="admin-stat-label">Active Products</div>
        <div class="admin-stat-sub ${stats.low_stock>0?'text-error':''}">Low stock: ${stats.low_stock}</div>
      </div>
      <div class="admin-stat-card" style="background:linear-gradient(135deg,rgba(255,101,132,0.15),rgba(255,101,132,0.05));">
        <div class="admin-stat-icon">👥</div>
        <div class="admin-stat-value">${stats.total_customers}</div>
        <div class="admin-stat-label">Customers</div>
        <div class="admin-stat-sub">Registered accounts</div>
      </div>`;

    // Recent orders table
    document.getElementById('recent-orders-table').innerHTML= recentOrders.length ? `
      <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:var(--text-sm);">
          <thead><tr style="border-bottom:1px solid var(--border);">
            <th style="text-align:left;padding:var(--space-sm);color:var(--text-3);font-weight:600;">Order</th>
            <th style="text-align:left;padding:var(--space-sm);color:var(--text-3);font-weight:600;">Customer</th>
            <th style="text-align:left;padding:var(--space-sm);color:var(--text-3);font-weight:600;">Status</th>
            <th style="text-align:right;padding:var(--space-sm);color:var(--text-3);font-weight:600;">Total</th>
          </tr></thead>
          <tbody>${recentOrders.map(o=>`
            <tr style="border-bottom:1px solid var(--border);">
              <td style="padding:var(--space-sm);"><a href="${APPURL}/pages/order-detail.php?id=${o.id}" style="font-weight:600;color:var(--primary);">${esc(o.order_number)}</a>
              <div style="font-size:10px;color:var(--text-3);">${new Date(o.created_at).toLocaleDateString()}</div></td>
              <td style="padding:var(--space-sm);">${esc(o.customer)}</td>
              <td style="padding:var(--space-sm);"><span class="status-badge ${SC[o.status]||''}">${esc(o.status)}</span></td>
              <td style="padding:var(--space-sm);text-align:right;font-weight:700;">${fmt(o.total)}</td>
            </tr>`).join('')}
          </tbody>
        </table>
      </div>` : '<p style="color:var(--text-3);font-size:var(--text-sm);">No orders yet.</p>';

    // Top products
    document.getElementById('top-products-list').innerHTML= topProducts.length ?
      topProducts.map((p,i)=>`
        <div style="display:flex;align-items:center;gap:var(--space-md);padding:var(--space-sm) 0;border-bottom:1px solid var(--border);">
          <span style="font-size:var(--text-lg);font-weight:800;color:var(--text-3);width:24px;">${i+1}</span>
          <img src="${p.image||'https://fastly.picsum.photos/id/1/60/60.jpg'}" style="width:40px;height:40px;border-radius:var(--r-sm);object-fit:cover;" alt="">
          <div style="flex:1;min-width:0;"><div style="font-size:var(--text-sm);font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${esc(p.name)}</div>
          <div style="font-size:var(--text-xs);color:var(--text-3);">${p.sold} sold</div></div>
          <div style="font-size:var(--text-sm);font-weight:700;color:var(--primary);">${fmt(p.revenue)}</div>
        </div>`).join('') : '<p style="color:var(--text-3);font-size:var(--text-sm);">No sales data yet.</p>';
  }catch(err){
    document.getElementById('stats-grid').innerHTML=`<div class="alert alert-error" style="grid-column:1/-1;">${err.message}</div>`;
  }
}
loadDashboard();
</script>
<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>
