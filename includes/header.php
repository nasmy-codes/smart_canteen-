<?php
$base   = $base ?? '';
$active = $active ?? '';
$cart_count = 0;
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $ci) {
        $cart_count += $ci['quantity'];
    }
}
?>
<header class="site-header">
    <nav class="navbar">
        <div class="logo">
            <a href="<?= $base ?>index.php">🍽 SmartCanteen</a>
        </div>
        <ul>
            <li><a href="<?= $base ?>index.php" class="<?= $active === 'home' ? 'active' : '' ?>">Home</a></li>
            <li><a href="<?= $base ?>pages/menu.php" class="<?= $active === 'menu' ? 'active' : '' ?>">Menu</a></li>
            <?php if (is_logged_in() && !is_admin()): ?>
                <li><a href="<?= $base ?>pages/my_orders.php" class="<?= $active === 'orders' ? 'active' : '' ?>">My Orders</a></li>
                <li><a href="<?= $base ?>pages/cart.php" class="cart-link <?= $active === 'cart' ? 'active' : '' ?>">
                        Cart <?php if ($cart_count > 0): ?><span class="cart-badge"><?= $cart_count ?></span><?php endif; ?>
                    </a></li>
                <li><a href="<?= $base ?>pages/logout.php">Logout (<?= h($_SESSION['username']) ?>)</a></li>
            <?php elseif (is_admin()): ?>
                <li><a href="<?= $base ?>admin/dashboard.php">Admin Panel</a></li>
                <li><a href="<?= $base ?>pages/logout.php">Logout (<?= h($_SESSION['username']) ?>)</a></li>
            <?php else: ?>
                <li><a href="<?= $base ?>pages/login.php" class="<?= $active === 'login' ? 'active' : '' ?>">Login</a></li>
                <li><a href="<?= $base ?>pages/register.php" class="<?= $active === 'register' ? 'active' : '' ?>">Register</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>
