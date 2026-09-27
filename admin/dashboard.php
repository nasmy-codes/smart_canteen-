<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_admin();

$totalItems   = $conn->query("SELECT COUNT(*) c FROM food_items")->fetch()['c'];
$totalOrders  = $conn->query("SELECT COUNT(*) c FROM orders")->fetch()['c'];
$pendingCount = $conn->query("SELECT COUNT(*) c FROM orders WHERE status IN ('Pending','Preparing')")->fetch()['c'];
$todaySales   = $conn->query("SELECT COALESCE(SUM(total_amount),0) s FROM orders WHERE DATE(created_at) = CURDATE() AND status != 'Cancelled'")->fetch()['s'];

$recentOrders = $conn->query(
    "SELECT o.*, u.full_name FROM orders o
     JOIN users u ON u.id = o.user_id
     ORDER BY o.created_at DESC LIMIT 6"
)->fetchAll();

function status_class($status) {
    return 'status-' . strtolower(str_replace(' ', '-', str_replace('for Pickup', '', $status)));
}

$base = '../';
$admin_active = 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard | SmartCanteen</title>
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
        <h1>Dashboard</h1>
        <p class="subtitle">Overview of SmartCanteen activity.</p>

        <div class="stats-grid">
            <div class="stat-card">
                <p class="label">Menu Items</p>
                <p class="value"><?= (int)$totalItems ?></p>
            </div>
            <div class="stat-card">
                <p class="label">Total Orders</p>
                <p class="value"><?= (int)$totalOrders ?></p>
            </div>
            <div class="stat-card">
                <p class="label">Pending / Preparing</p>
                <p class="value"><?= (int)$pendingCount ?></p>
            </div>
            <div class="stat-card">
                <p class="label">Today's Sales</p>
                <p class="value">Rs. <?= number_format($todaySales, 2) ?></p>
            </div>
        </div>

        <div class="admin-panel">
            <h2>Recent Orders</h2>
            <table class="data-table">
                <thead>
                    <tr><th>Order ID</th><th>Customer</th><th>Total</th><th>Status</th><th>Date</th></tr>
                </thead>
                <tbody>
                    <?php if (count($recentOrders) === 0): ?>
                        <tr><td colspan="5">No orders yet.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($recentOrders as $o): ?>
                        <tr>
                            <td><?= h($o['order_code']) ?></td>
                            <td><?= h($o['full_name']) ?></td>
                            <td>Rs. <?= number_format($o['total_amount'], 2) ?></td>
                            <td><span class="status <?= status_class($o['status']) ?>"><?= h($o['status']) ?></span></td>
                            <td><?= date('d M Y, g:i A', strtotime($o['created_at'])) ?></td>
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
