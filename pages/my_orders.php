<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_login('login.php');

if (is_admin()) {
    header("Location: ../admin/dashboard.php");
    exit;
}

$search = trim($_GET['q'] ?? '');

if ($search !== '') {
    $stmt = $conn->prepare(
        "SELECT * FROM orders WHERE user_id = :uid AND order_code LIKE :q ORDER BY created_at DESC"
    );
    $stmt->execute([':uid' => current_user_id(), ':q' => '%' . $search . '%']);
} else {
    $stmt = $conn->prepare(
        "SELECT * FROM orders WHERE user_id = :uid ORDER BY created_at DESC"
    );
    $stmt->execute([':uid' => current_user_id()]);
}
$orders = $stmt->fetchAll();

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
<title>My Orders | SmartCanteen</title>
<link rel="stylesheet" href="../css/style.css">
<link rel="stylesheet" href="../css/cart.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

<?php include '../includes/header.php'; ?>

<main class="orders-page">
    <h1>My Orders</h1>
    <p class="page-description">View your previous orders and check their current status.</p>

    <form class="order-search" method="GET" action="my_orders.php">
        <input type="text" name="q" placeholder="Search by Order ID" value="<?= h($search) ?>">
        <button type="submit">Search</button>
    </form>

    <?php if (count($orders) === 0): ?>
        <div class="empty-cart">
            <p><?= $search !== '' ? 'No orders match that Order ID.' : "You haven't placed any orders yet." ?></p>
            <br>
            <a href="menu.php" class="btn">Browse the Menu</a>
        </div>
    <?php else: ?>
        <div class="orders-container">
            <?php foreach ($orders as $order): ?>
                <?php
                    $itemStmt = $conn->prepare("SELECT item_name, quantity FROM order_items WHERE order_id = :oid");
                    $itemStmt->execute([':oid' => $order['id']]);
                    $names = array_map(fn($r) => $r['item_name'] . ' x ' . $r['quantity'], $itemStmt->fetchAll());
                ?>
                <div class="order-card">
                    <div class="order-header">
                        <div>
                            <p>Order ID</p>
                            <h3><?= h($order['order_code']) ?></h3>
                        </div>
                        <span class="status <?= status_class($order['status']) ?>"><?= h($order['status']) ?></span>
                    </div>
                    <hr>
                    <div class="order-details">
                        <p><strong>Date:</strong> <?= date('d F Y, g:i A', strtotime($order['created_at'])) ?></p>
                        <p><strong>Items:</strong> <?= h(implode(', ', $names)) ?></p>
                        <p><strong>Total:</strong> Rs. <?= number_format($order['total_amount'], 2) ?></p>
                    </div>
                    <div class="order-footer">
                        <p>Please show your Order ID at the counter.</p>
                        <a href="order_details.php?code=<?= urlencode($order['order_code']) ?>">View Order</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<?php include '../includes/footer.php'; ?>

</body>
</html>
