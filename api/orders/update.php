<?php
/** POST /api/orders/update.php — Admin: update order status */
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/includes/response.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/csrf.php';
require_once dirname(__DIR__, 2) . '/includes/validator.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { Response::methodNotAllowed('POST'); }
Auth::requireAdmin(); CSRF::validate();

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$id     = Validator::sanitizeInt($input['id'] ?? 0);
$status = Validator::sanitizeText($input['status'] ?? '');
$payStatus = Validator::sanitizeText($input['payment_status'] ?? '');

if (!$id) { Response::error('Order ID required.', 422); }

$validStatuses = ['pending','processing','shipped','delivered','cancelled','refunded'];
$validPayStatuses = ['unpaid','paid','refunded'];

if ($status && !in_array($status, $validStatuses)) { Response::error('Invalid status value.', 422); }
if ($payStatus && !in_array($payStatus, $validPayStatuses)) { Response::error('Invalid payment status.', 422); }

try {
    $order = Database::fetchOne("SELECT id FROM orders WHERE id = ?", [$id]);
    if (!$order) { Response::notFound('Order not found.'); }

    $updates = [];
    $params  = [];
    if ($status)    { $updates[] = 'status = ?';         $params[] = $status; }
    if ($payStatus) { $updates[] = 'payment_status = ?'; $params[] = $payStatus; }

    if (empty($updates)) { Response::error('Nothing to update.', 422); }

    $params[] = $id;
    Database::query("UPDATE orders SET " . implode(', ', $updates) . " WHERE id = ?", $params);

    Response::success(null, 'Order updated successfully.');
} catch (PDOException $e) {
    error_log("[Orders/Update] " . $e->getMessage());
    Response::error('Update failed.', 500);
}
