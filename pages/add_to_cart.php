<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';

if (!is_logged_in() || is_admin()) {
    header("Location: menu.php?msg=login_required");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: menu.php");
    exit;
}

$food_item_id = (int)($_POST['food_item_id'] ?? 0);
$quantity     = max(1, min(20, (int)($_POST['quantity'] ?? 1)));

// Confirm the item exists and is available before adding it.
$stmt = $conn->prepare("SELECT id FROM food_items WHERE id = :id AND status = 'available'");
$stmt->execute([':id' => $food_item_id]);

if ($stmt->fetch()) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    if (isset($_SESSION['cart'][$food_item_id])) {
        $_SESSION['cart'][$food_item_id]['quantity'] =
            min(20, $_SESSION['cart'][$food_item_id]['quantity'] + $quantity);
    } else {
        $_SESSION['cart'][$food_item_id] = ['quantity' => $quantity];
    }

    header("Location: menu.php?msg=added");
} else {
    header("Location: menu.php");
}
exit;
