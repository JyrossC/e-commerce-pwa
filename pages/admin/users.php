<?php
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/validator.php';

Auth::requireAdminPage();
$pageTitle = 'Manage Users'; $activePage = 'admin';

$page  = max(1, Validator::sanitizeInt($_GET['page'] ?? 1));
$limit = 20; $offset = ($page-1)*$limit;
$total = (int) Database::fetchColumn("SELECT COUNT(*) FROM users");
$users = Database::fetchAll("SELECT id,name,email,role,is_active,created_at FROM users ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
$totalPages = (int) ceil($total / $limit);

include dirname(__DIR__, 2) . '/includes/header.php';
?>
<link rel="stylesheet" href="<?= APP_URL ?>/assets/css/admin.css">
<div class="container section">
  <h1 style="font-size:var(--text-2xl);font-weight:800;margin-bottom:var(--space-sm);">👥 Users</h1>
  <p style="color:var(--text-3);font-size:var(--text-sm);margin-bottom:var(--space-xl);"><?= $total ?> registered accounts</p>
  <nav class="admin-nav">
    <a href="<?= APP_URL ?>/pages/admin/dashboard.php">📊 Dashboard</a>
    <a href="<?= APP_URL ?>/pages/admin/products.php">📦 Products</a>
    <a href="<?= APP_URL ?>/pages/admin/categories.php">🗂 Categories</a>
    <a href="<?= APP_URL ?>/pages/admin/orders.php">🧾 Orders</a>
    <a href="<?= APP_URL ?>/pages/admin/users.php" class="active">👥 Users</a>
  </nav>
  <div style="overflow-x:auto;">
    <table class="admin-table">
      <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Joined</th></tr></thead>
      <tbody>
        <?php foreach ($users as $u): ?>
        <tr>
          <td style="color:var(--text-3);"><?= (int)$u['id'] ?></td>
          <td>
            <div style="display:flex;align-items:center;gap:var(--space-sm);">
              <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--secondary));display:flex;align-items:center;justify-content:center;font-size:var(--text-sm);font-weight:800;color:#fff;flex-shrink:0;"><?= strtoupper(substr($u['name'],0,1)) ?></div>
              <span style="font-weight:600;"><?= Validator::escape($u['name']) ?></span>
            </div>
          </td>
          <td><?= Validator::escape($u['email']) ?></td>
          <td><span class="tag" style="<?= $u['role']==='admin'?'background:rgba(108,99,255,0.15);color:var(--primary)':'' ?>"><?= Validator::escape($u['role']) ?></span></td>
          <td><span style="color:<?= $u['is_active']?'var(--success)':'var(--error)' ?>;"><?= $u['is_active']?'Active':'Suspended' ?></span></td>
          <td style="font-size:var(--text-xs);color:var(--text-3);"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php if ($totalPages > 1): ?>
  <div class="pagination">
    <?php for ($i=1;$i<=$totalPages;$i++): ?>
    <a href="?page=<?= $i ?>" class="page-btn <?= $i===$page?'active':'' ?>"><?= $i ?></a>
    <?php endfor; ?>
  </div>
  <?php endif; ?>
</div>
<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>
