<?php
/** GET /api/stats/dashboard.php — Admin dashboard stats */
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/includes/response.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') { Response::methodNotAllowed('GET'); }
Auth::requireAdmin();

try {
    $stats = [
        'total_revenue'   => (float) Database::fetchColumn("SELECT COALESCE(SUM(total),0) FROM orders WHERE status != 'cancelled'"),
        'total_orders'    => (int)   Database::fetchColumn("SELECT COUNT(*) FROM orders"),
        'total_products'  => (int)   Database::fetchColumn("SELECT COUNT(*) FROM products WHERE is_active=1"),
        'total_customers' => (int)   Database::fetchColumn("SELECT COUNT(*) FROM users WHERE role='customer'"),
        'pending_orders'  => (int)   Database::fetchColumn("SELECT COUNT(*) FROM orders WHERE status='pending'"),
        'low_stock'       => (int)   Database::fetchColumn("SELECT COUNT(*) FROM products WHERE stock <= 5 AND is_active=1"),
        'orders_today'    => (int)   Database::fetchColumn("SELECT COUNT(*) FROM orders WHERE DATE(created_at)=CURDATE()"),
        'revenue_today'   => (float) Database::fetchColumn("SELECT COALESCE(SUM(total),0) FROM orders WHERE DATE(created_at)=CURDATE() AND status!='cancelled'"),
    ];

    // Recent orders
    $recentOrders = Database::fetchAll(
        "SELECT o.id, o.order_number, o.total, o.status, o.created_at, u.name AS customer
         FROM orders o JOIN users u ON u.id=o.user_id ORDER BY o.created_at DESC LIMIT 5"
    );

    // Monthly revenue (last 6 months)
    $monthlyRevenue = Database::fetchAll(
        "SELECT DATE_FORMAT(created_at,'%Y-%m') AS month, COALESCE(SUM(total),0) AS revenue, COUNT(*) AS orders
         FROM orders WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH) AND status!='cancelled'
         GROUP BY month ORDER BY month"
    );

    // Top products
    $topProducts = Database::fetchAll(
        "SELECT p.name, p.image, SUM(oi.quantity) AS sold, SUM(oi.subtotal) AS revenue
         FROM order_items oi JOIN products p ON p.id=oi.product_id
         GROUP BY p.id ORDER BY sold DESC LIMIT 5"
    );

    Response::success(compact('stats','recentOrders','monthlyRevenue','topProducts'));
} catch (PDOException $e) { error_log("[Stats] ".$e->getMessage()); Response::error('Failed.',500); }
