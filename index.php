<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';

// Show up to 3 available items as "Popular Meals"
$stmt = $conn->query("SELECT * FROM food_items WHERE status = 'available' ORDER BY id ASC LIMIT 3");
$popular = $stmt->fetchAll();

$base = '';
$active = 'home';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SmartCanteen | Home</title>
<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="css/home.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="bgimage">
    <div class="overlay">
        <h1>SmartCanteen</h1>
        <p>Skip the queue. Order online. Collect with your Order ID.</p>
        <a href="pages/menu.php" class="btn">Order Now</a>
    </div>
</div>

<div class="featured">
    <h2>Popular Meals</h2>

    <?php if (count($popular) === 0): ?>
        <p style="text-align:center;color:#777;">No menu items available right now — please check back soon.</p>
    <?php else: ?>
        <div class="food-container">
            <?php foreach ($popular as $item): ?>
                <div class="food-card">
                    <img src="images/<?= h($item['image']) ?: 'header.jpg' ?>" alt="<?= h($item['name']) ?>">
                    <h3><?= h($item['name']) ?></h3>
                    <p>Rs. <?= number_format($item['price'], 2) ?></p>
                    <a href="pages/menu.php" class="btn">Order</a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<div class="why-us">
    <div class="item">
        <h4>⏱ Save Time</h4>
        <p>Order ahead online and skip the canteen queue entirely.</p>
    </div>
    <div class="item">
        <h4>📱 Track Your Order</h4>
        <p>Follow your order status from Pending to Ready for Pickup.</p>
    </div>
    <div class="item">
        <h4>🧾 Organized Records</h4>
        <p>Every order is safely stored so you can review it anytime.</p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>
