<?php
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/csrf.php';
require_once dirname(__DIR__, 2) . '/includes/validator.php';

Auth::requireAdminPage();
$pageTitle = 'Manage Categories'; $activePage = 'admin';

// Handle category CRUD server-side for simplicity
$error = ''; $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRF::validate();
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        $name = Validator::sanitizeText($_POST['name'] ?? '');
        $desc = Validator::sanitizeText($_POST['description'] ?? '');
        if ($name) {
            $slug = Validator::slug($name) . '-' . substr(bin2hex(random_bytes(2)), 0, 4);
            Database::query("INSERT INTO categories (name, slug, description) VALUES (?,?,?)", [$name, $slug, $desc]);
            $success = "Category '$name' created successfully.";
        } else { $error = "Category name is required."; }
    } elseif ($action === 'delete') {
        $id = Validator::sanitizeInt($_POST['cat_id'] ?? 0);
        if ($id) { Database::query("DELETE FROM categories WHERE id = ?", [$id]); $success = "Category deleted."; }
    }
}

$categories = Database::fetchAll(
    "SELECT c.*, COUNT(p.id) AS product_count FROM categories c
     LEFT JOIN products p ON p.category_id=c.id GROUP BY c.id ORDER BY c.sort_order, c.name"
);
include dirname(__DIR__, 2) . '/includes/header.php';
?>
<link rel="stylesheet" href="<?= APP_URL ?>/assets/css/admin.css">
<div class="container section">
  <h1 style="font-size:var(--text-2xl);font-weight:800;margin-bottom:var(--space-sm);">🗂 Categories</h1>
  <p style="color:var(--text-3);font-size:var(--text-sm);margin-bottom:var(--space-xl);">Manage product categories</p>

  <nav class="admin-nav">
    <a href="<?= APP_URL ?>/pages/admin/dashboard.php">📊 Dashboard</a>
    <a href="<?= APP_URL ?>/pages/admin/products.php">📦 Products</a>
    <a href="<?= APP_URL ?>/pages/admin/categories.php" class="active">🗂 Categories</a>
    <a href="<?= APP_URL ?>/pages/admin/orders.php">🧾 Orders</a>
    <a href="<?= APP_URL ?>/pages/admin/users.php">👥 Users</a>
  </nav>

  <?php if ($success): ?><div class="alert alert-success"><?= Validator::escape($success) ?></div><?php endif; ?>
  <?php if ($error):   ?><div class="alert alert-error"><?= Validator::escape($error) ?></div><?php endif; ?>

  <div style="display:grid;grid-template-columns:1fr;gap:var(--space-lg);">

    <!-- Add Category Form -->
    <div class="card">
      <h3 style="font-size:var(--text-base);font-weight:800;margin-bottom:var(--space-lg);">+ Add New Category</h3>
      <form method="POST" action="">
        <?= CSRF::field() ?>
        <input type="hidden" name="action" value="create">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-md);">
          <div class="form-group">
            <label class="form-label" for="cat-name">Category Name *</label>
            <input type="text" id="cat-name" name="name" class="form-control" placeholder="e.g. Electronics" required>
          </div>
          <div class="form-group">
            <label class="form-label" for="cat-desc">Description</label>
            <input type="text" id="cat-desc" name="description" class="form-control" placeholder="Short description">
          </div>
        </div>
        <button type="submit" class="btn btn-primary">Create Category</button>
      </form>
    </div>

    <!-- Categories Table -->
    <div style="overflow-x:auto;">
      <table class="admin-table">
        <thead><tr>
          <th>#</th><th>Name</th><th>Slug</th><th>Products</th><th>Status</th><th>Actions</th>
        </tr></thead>
        <tbody>
          <?php foreach ($categories as $cat): ?>
          <tr>
            <td style="color:var(--text-3);"><?= (int)$cat['id'] ?></td>
            <td style="font-weight:700;"><?= Validator::escape($cat['name']) ?></td>
            <td><code style="font-size:var(--text-xs);color:var(--primary);"><?= Validator::escape($cat['slug']) ?></code></td>
            <td><?= (int)$cat['product_count'] ?> products</td>
            <td><span style="color:<?= $cat['is_active']?'var(--success)':'var(--error)' ?>;"><?= $cat['is_active']?'Active':'Inactive' ?></span></td>
            <td>
              <a href="<?= APP_URL ?>/pages/shop.php?category=<?= Validator::escape($cat['slug']) ?>" class="btn btn-ghost btn-sm" target="_blank">🌐 View</a>
              <?php if ($cat['product_count'] == 0): ?>
              <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this category?')">
                <?= CSRF::field() ?>
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="cat_id" value="<?= (int)$cat['id'] ?>">
                <button type="submit" class="btn btn-danger btn-sm">🗑 Delete</button>
              </form>
              <?php else: ?>
              <span style="font-size:var(--text-xs);color:var(--text-3);">Has products</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>
