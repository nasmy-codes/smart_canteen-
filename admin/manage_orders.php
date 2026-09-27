<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_admin();

$statuses = ['Pending', 'Preparing', 'Ready for Pickup', 'Completed', 'Cancelled'];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $orderId = (int)($_POST['order_id'] ?? 0);
    $newStatus = $_POST['status'] ?? '';

    if (in_array($newStatus, $statuses, true)) {
        $conn->prepare("UPDATE orders SET status = :s WHERE id = :id")
             ->execute([':s' => $newStatus, ':id' => $orderId]);
        $success = 'Order status updated.';
    }
}

$filter = $_GET['status'] ?? '';
if ($filter !== '' && in_array($filter, $statuses, true)) {
    $stmt = $conn->prepare(
        "SELECT o.*, u.full_name, u.username FROM orders o
         JOIN users u ON u.id = o.user_id
         WHERE o.status = :s
         ORDER BY o.created_at DESC"
    );
    $stmt->execute([':s' => $filter]);
} else {
    $stmt = $conn->query(
        "SELECT o.*, u.full_name, u.username FROM orders o
         JOIN users u ON u.id = o.user_id
         ORDER BY o.created_at DESC"
    );
}
$orders = $stmt->fetchAll();

function status_class($status) {
    return 'status-' . strtolower(str_replace(' ', '-', str_replace('for Pickup', '', $status)));
}

$base = '../';
$admin_active = 'orders';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Orders | SmartCanteen Admin</title>
<link rel="stylesheet" href="../css/style.css">
<link rel="stylesheet" href="../css/cart.css">
<link rel="stylesheet" href="../css/admin.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

<?php include '../includes/header.php'; ?>

<div class="admin-wrap">
    <?php include '../includes/admin_sidebar.php'; ?>

    <main class="admin-main">
        <h1>Manage Orders</h1>
        <p class="subtitle">Review orders and update their status as they progress.</p>

        <?php if ($success): ?><div class="alert alert-success"><?= h($success) ?></div><?php endif; ?>

        <div class="admin-panel">
            <div class="admin-toolbar">
                <h2 style="margin:0;">Orders (<?= count($orders) ?>)</h2>
                <form class="inline-form" method="GET" action="manage_orders.php">
                    <select name="status" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <?php foreach ($statuses as $s): ?>
                            <option value="<?= h($s) ?>" <?= $filter === $s ? 'selected' : '' ?>><?= h($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>

            <table class="data-table">
                <thead>
                    <tr><th>Order ID</th><th>Customer</th><th>Total</th><th>Placed</th><th>Status</th><th>Update</th></tr>
                </thead>
                <tbody>
                    <?php if (count($orders) === 0): ?>
                        <tr><td colspan="6">No orders found.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($orders as $o): ?>
                        <tr>
                            <td><?= h($o['order_code']) ?></td>
                            <td><?= h($o['full_name']) ?> <span style="color:#999;">(@<?= h($o['username']) ?>)</span></td>
                            <td>Rs. <?= number_format($o['total_amount'], 2) ?></td>
                            <td><?= date('d M Y, g:i A', strtotime($o['created_at'])) ?></td>
                            <td><span class="status <?= status_class($o['status']) ?>"><?= h($o['status']) ?></span></td>
                            <td>
                                <form class="inline-form" method="POST" action="manage_orders.php">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="order_id" value="<?= (int)$o['id'] ?>">
                                    <select name="status">
                                        <?php foreach ($statuses as $s): ?>
                                            <option value="<?= h($s) ?>" <?= $o['status'] === $s ? 'selected' : '' ?>><?= h($s) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="btn btn-small">Save</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>

</body>
</html>
