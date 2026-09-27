<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_login('login.php');

$code = $_GET['code'] ?? '';

$stmt = $conn->prepare(
    "SELECT * FROM orders WHERE order_code = :code AND user_id = :uid LIMIT 1"
);
$stmt->execute([':code' => $code, ':uid' => current_user_id()]);
$order = $stmt->fetch();

if (!$order) {
    header("Location: my_orders.php");
    exit;
}

$itemStmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = :oid");
$itemStmt->execute([':oid' => $order['id']]);
$orderItems = $itemStmt->fetchAll();

$base = '../';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Order Confirmed | SmartCanteen</title>
<link rel="stylesheet" href="../css/style.css">
<link rel="stylesheet" href="../css/cart.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

<?php include '../includes/header.php'; ?>

<main class="confirmation-page">
    <div class="confirmation-box">
        <div class="check">&#10003;</div>
        <h2>Order Placed Successfully!</h2>
        <p>Show this Order ID at the counter when collecting your food.</p>

        <div class="order-code-box"><?= h($order['order_code']) ?></div>

        <table class="data-table" style="margin:24px 0;text-align:left;">
            <thead>
                <tr><th>Item</th><th>Qty</th><th>Subtotal</th></tr>
            </thead>
            <tbody>
                <?php foreach ($orderItems as $it): ?>
                    <tr>
                        <td><?= h($it['item_name']) ?></td>
                        <td><?= (int)$it['quantity'] ?></td>
                        <td>Rs. <?= number_format($it['subtotal'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <td colspan="2" style="text-align:right;font-weight:700;">Total</td>
                    <td style="font-weight:700;">Rs. <?= number_format($order['total_amount'], 2) ?></td>
                </tr>
            </tbody>
        </table>

        <a href="my_orders.php" class="btn">View My Orders</a>
        <a href="menu.php" class="btn btn-outline" style="margin-left:10px;">Order More</a>
    </div>
</main>

<?php include '../includes/footer.php'; ?>

</body>
</html>
