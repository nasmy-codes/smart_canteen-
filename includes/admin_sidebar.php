<?php

$admin_active = $admin_active ?? '';
?>
<aside class="admin-sidebar">
    <h3>Admin Panel</h3>
    <a href="dashboard.php" class="<?= $admin_active === 'dashboard' ? 'active' : '' ?>">📊 Dashboard</a>
    <a href="manage_items.php" class="<?= $admin_active === 'items' ? 'active' : '' ?>">🍛 Manage Food Items</a>
    <a href="manage_orders.php" class="<?= $admin_active === 'orders' ? 'active' : '' ?>">🧾 Manage Orders</a>
    <a href="sales_report.php" class="<?= $admin_active === 'reports' ? 'active' : '' ?>">📈 Sales Reports</a>
</aside>
