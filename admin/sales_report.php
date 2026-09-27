<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_admin();

// Default range: last 7 days
$from = $_GET['from'] ?? date('Y-m-d', strtotime('-6 days'));
$to   = $_GET['to']   ?? date('Y-m-d');

// Basic validation / fallback
if (!strtotime($from)) $from = date('Y-m-d', strtotime('-6 days'));
if (!strtotime($to))   $to   = date('Y-m-d');

$stmt = $conn->prepare(
    "SELECT * FROM orders
     WHERE DATE(created_at) BETWEEN :from AND :to AND status != 'Cancelled'
     ORDER BY created_at ASC"
);
$stmt->execute([':from' => $from, ':to' => $to]);
$orders = $stmt->fetchAll();

$totalRevenue = 0;
$byDay = [];
foreach ($orders as $o) {
    $totalRevenue += $o['total_amount'];
    $day = date('Y-m-d', strtotime($o['created_at']));
    $byDay[$day] = ($byDay[$day] ?? 0) + $o['total_amount'];
}
ksort($byDay);
$maxDay = count($byDay) ? max($byDay) : 0;

// Top selling items in this range
$topStmt = $conn->prepare(
    "SELECT oi.item_name, SUM(oi.quantity) AS qty, SUM(oi.subtotal) AS revenue
     FROM order_items oi
     JOIN orders o ON o.id = oi.order_id
     WHERE DATE(o.created_at) BETWEEN :from AND :to AND o.status != 'Cancelled'
     GROUP BY oi.item_name
     ORDER BY qty DESC
     LIMIT 5"
);
$topStmt->execute([':from' => $from, ':to' => $to]);
$topItems = $topStmt->fetchAll();

$base = '../';
$admin_active = 'reports';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sales Reports | SmartCanteen Admin</title>
<link rel="stylesheet" href="../css/style.css">
<link rel="stylesheet" href="../css/admin.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

<?php include '../includes/header.php'; ?>

<div class="admin-wrap">
    <?php include '../includes/admin_sidebar.php'; ?>

    <main class="admin-main">
        <h1>Sales Reports</h1>
        <p class="subtitle">Track revenue and order volume over a date range.</p>

        <div class="admin-panel">
            <form class="inline-form" method="GET" action="sales_report.php">
                <label>From <input type="date" name="from" value="<?= h($from) ?>"></label>
                <label>To <input type="date" name="to" value="<?= h($to) ?>"></label>
                <button type="submit" class="btn btn-small">Apply</button>
            </form>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <p class="label">Orders in Range</p>
                <p class="value"><?= count($orders) ?></p>
            </div>
            <div class="stat-card">
                <p class="label">Total Revenue</p>
                <p class="value">Rs. <?= number_format($totalRevenue, 2) ?></p>
            </div>
            <div class="stat-card">
                <p class="label">Average Order Value</p>
                <p class="value">Rs. <?= number_format(count($orders) ? $totalRevenue / count($orders) : 0, 2) ?></p>
            </div>
        </div>

        <div class="admin-panel">
            <h2>Revenue by Day</h2>
            <?php if (count($byDay) === 0): ?>
                <p>No sales in this date range.</p>
            <?php else: ?>
                <div style="display:flex;flex-direction:column;gap:12px;">
                    <?php foreach ($byDay as $day => $amount): ?>
                        <div>
                            <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:4px;">
                                <span><?= date('D, d M Y', strtotime($day)) ?></span>
                                <span>Rs. <?= number_format($amount, 2) ?></span>
                            </div>
                            <div style="background:#eef0f2;border-radius:6px;height:14px;">
                                <div style="background:#14532D;height:14px;border-radius:6px;width:<?= $maxDay > 0 ? round(($amount / $maxDay) * 100) : 0 ?>%;"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="admin-panel">
            <h2>Top Selling Items</h2>
            <table class="data-table">
                <thead>
                    <tr><th>Item</th><th>Quantity Sold</th><th>Revenue</th></tr>
                </thead>
                <tbody>
                    <?php if (count($topItems) === 0): ?>
                        <tr><td colspan="3">No sales in this date range.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($topItems as $ti): ?>
                        <tr>
                            <td><?= h($ti['item_name']) ?></td>
                            <td><?= (int)$ti['qty'] ?></td>
                            <td>Rs. <?= number_format($ti['revenue'], 2) ?></td>
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
