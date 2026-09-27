<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_login('login.php');

if (is_admin()) {
    header("Location: ../admin/dashboard.php");
    exit;
}

// Handle quantity updates / removals posted from this page.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $food_item_id = (int)($_POST['food_item_id'] ?? 0);

    if (isset($_POST['update']) && isset($_SESSION['cart'][$food_item_id])) {
        $qty = max(1, min(20, (int)$_POST['quantity']));
        $_SESSION['cart'][$food_item_id]['quantity'] = $qty;
    }

    if (isset($_POST['remove']) && isset($_SESSION['cart'][$food_item_id])) {
        unset($_SESSION['cart'][$food_item_id]);
    }

    header("Location: cart.php");
    exit;
}

// Build the cart view by joining session cart with live food_items data.
$cartItems = [];
$total = 0;

if (!empty($_SESSION['cart'])) {
    $ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $conn->prepare("SELECT * FROM food_items WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    $foodById = [];
    foreach ($stmt->fetchAll() as $row) {
        $foodById[$row['id']] = $row;
    }

    foreach ($_SESSION['cart'] as $id => $entry) {
        if (!isset($foodById[$id])) {
            continue; // item was deleted by admin since being added
        }
        $food = $foodById[$id];
        $subtotal = $food['price'] * $entry['quantity'];
        $total += $subtotal;
        $cartItems[] = [
            'id' => $id,
            'name' => $food['name'],
            'price' => $food['price'],
            'image' => $food['image'],
            'quantity' => $entry['quantity'],
            'subtotal' => $subtotal,
            'available' => $food['status'] === 'available',
        ];
    }
}

$base = '../';
$active = 'cart';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Cart | SmartCanteen</title>
<link rel="stylesheet" href="../css/style.css">
<link rel="stylesheet" href="../css/cart.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

<?php include '../includes/header.php'; ?>

<main class="cart-page">
    <h1>My Cart</h1>
    <p class="page-description">Review your items before checking out.</p>

    <?php if (count($cartItems) === 0): ?>
        <div class="empty-cart">
            <p>Your cart is empty.</p>
            <br>
            <a href="menu.php" class="btn">Browse the Menu</a>
        </div>
    <?php else: ?>
        <div class="cart-container">
            <div class="cart-items">
                <?php foreach ($cartItems as $item): ?>
                    <div class="cart-item">
                        <img src="../images/<?= h($item['image']) ?: 'header.jpg' ?>" alt="<?= h($item['name']) ?>">
                        <div class="cart-item-details">
                            <h3><?= h($item['name']) ?></h3>
                            <p>Rs. <?= number_format($item['price'], 2) ?> each</p>
                            <?php if (!$item['available']): ?>
                                <p style="color:#dc2626;font-size:12px;">No longer available &mdash; please remove.</p>
                            <?php endif; ?>
                            <form class="qty-form" method="POST" action="cart.php">
                                <input type="hidden" name="food_item_id" value="<?= (int)$item['id'] ?>">
                                <input type="number" name="quantity" value="<?= (int)$item['quantity'] ?>" min="1" max="20">
                                <button type="submit" name="update">Update</button>
                            </form>
                        </div>
                        <div class="cart-item-total">
                            <p>Rs. <?= number_format($item['subtotal'], 2) ?></p>
                            <form method="POST" action="cart.php">
                                <input type="hidden" name="food_item_id" value="<?= (int)$item['id'] ?>">
                                <button type="submit" name="remove" class="remove-btn">Remove</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="cart-summary">
                <h2>Order Summary</h2>
                <div class="summary-row">
                    <span>Items</span>
                    <span><?= count($cartItems) ?></span>
                </div>
                <div class="summary-total">
                    <span>Total</span>
                    <span>Rs. <?= number_format($total, 2) ?></span>
                </div>
                <form method="POST" action="place_order.php">
                    <button type="submit" class="btn btn-block" style="margin-top:20px;">Place Order</button>
                </form>
            </div>
        </div>
    <?php endif; ?>
</main>

<?php include '../includes/footer.php'; ?>

</body>
</html>
