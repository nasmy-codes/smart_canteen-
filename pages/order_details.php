<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_login('login.php');

$code = $_GET['code'] ?? '';

$stmt = $conn->prepare("SELECT * FROM orders WHERE order_code = :code AND user_id = :uid LIMIT 1");
$stmt->execute([':code' => $code, ':uid' => current_user_id()]);
$order = $stmt->fetch();

if (!$order) {
    header("Location: my_orders.php");
    exit;
}

$itemStmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = :oid");
$itemStmt->execute([':oid' => $order['id']]);
$orderItems = $itemStmt->fetchAll();

function status_class($status) {
    return 'status-' . strtolower(str_replace(' ', '-', str_replace('for Pickup', '', $status)));
}

$base = '../';
$active = 'orders';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Order <?= h($order['order_code']) ?> | SmartCanteen</title>
<link rel="stylesheet" href="../css/style.css">
<link rel="stylesheet" href="../css/cart.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

<?php include '../includes/header.php'; ?>

<main class="order-detail-page">
    <h1>Order <?= h($order['order_code']) ?></h1>
    <p class="page-description">
        <span class="status <?= status_class($order['status']) ?>"><?= h($order['status']) ?></span>
    </p>

    <table class="data-table" style="margin-bottom:24px;">
        <tr><td>Order ID</td><td><?= h($order['order_code']) ?></td></tr>
        <tr><td>Date Placed</td><td><?= date('d F Y, g:i A', strtotime($order['created_at'])) ?></td></tr>
        <tr><td>Status</td><td><?= h($order['status']) ?></td></tr>
        <tr><td>Total Amount</td><td>Rs. <?= number_format($order['total_amount'], 2) ?></td></tr>
    </table>

    <table class="data-table">
        <thead>
            <tr><th>Item</th><th>Price</th><th>Qty</th><th>Subtotal</th></tr>
        </thead>
        <tbody>
            <?php foreach ($orderItems as $it): ?>
                <tr>
                    <td><?= h($it['item_name']) ?></td>
                    <td>Rs. <?= number_format($it['price'], 2) ?></td>
                    <td><?= (int)$it['quantity'] ?></td>
                    <td>Rs. <?= number_format($it['subtotal'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <br>
    <a href="my_orders.php" class="btn btn-outline">&larr; Back to My Orders</a>
</main>

<?php include '../includes/footer.php'; ?>

</body>
</html>
