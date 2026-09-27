<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';

// Group all available items by category
$stmt = $conn->query("SELECT * FROM food_items WHERE status = 'available' ORDER BY category ASC, name ASC");
$items = $stmt->fetchAll();

$categories = [];
foreach ($items as $item) {
    $categories[$item['category']][] = $item;
}

$base = '../';
$active = 'menu';

function slug($text) {
    return strtolower(preg_replace('/[^a-z0-9]+/i', '-', trim($text)));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Food Menu | SmartCanteen</title>
<link rel="stylesheet" href="../css/style.css">
<link rel="stylesheet" href="../css/menu.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

<?php include '../includes/header.php'; ?>

<div class="menu-page">
    <div class="page-header">
        <h1>Our Menu</h1>
        <p>Fresh food, made to order. Add items to your cart and check out online.</p>
    </div>

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'login_required'): ?>
        <div class="alert alert-error">Please <a href="login.php">login</a> to add items to your cart.</div>
    <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'added'): ?>
        <div class="alert alert-success">Item added to your cart! <a href="cart.php">View Cart &rarr;</a></div>
    <?php endif; ?>

    <?php if (count($categories) === 0): ?>
        <p class="empty-msg">No menu items are available right now. Please check back soon.</p>
    <?php else: ?>

        <div class="category-tabs">
            <?php foreach (array_keys($categories) as $cat): ?>
                <a href="#<?= slug($cat) ?>"><?= h($cat) ?></a>
            <?php endforeach; ?>
        </div>

        <?php foreach ($categories as $cat => $catItems): ?>
            <div class="menu-category" id="<?= slug($cat) ?>">
                <h2><?= h($cat) ?></h2>
                <div class="food-container">
                    <?php foreach ($catItems as $item): ?>
                        <div class="food-card">
                            <img src="../images/<?= h($item['image']) ?: 'header.jpg' ?>" alt="<?= h($item['name']) ?>">
                            <div class="food-info">
                                <h3><?= h($item['name']) ?></h3>
                                <p class="desc"><?= h($item['description']) ?></p>
                                <p class="price">Rs. <?= number_format($item['price'], 2) ?></p>

                                <?php if (is_logged_in() && !is_admin()): ?>
                                    <form action="add_to_cart.php" method="POST">
                                        <input type="hidden" name="food_item_id" value="<?= (int)$item['id'] ?>">
                                        <input type="number" name="quantity" value="1" min="1" max="20">
                                        <button type="submit">Add to Cart</button>
                                    </form>
                                <?php elseif (is_admin()): ?>
                                    <a href="../admin/manage_items.php" class="btn btn-small btn-outline">Manage in Admin</a>
                                <?php else: ?>
                                    <a href="login.php" class="btn btn-small btn-block">Login to Order</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>

    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>

</body>
</html>
