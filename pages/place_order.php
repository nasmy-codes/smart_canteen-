<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_login('login.php');

if (is_admin()) {
    header("Location: ../admin/dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit;
}

// Re-fetch live prices/availability so nothing can be tampered with client-side.
$ids = array_keys($_SESSION['cart']);
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$stmt = $conn->prepare("SELECT * FROM food_items WHERE id IN ($placeholders) AND status = 'available'");
$stmt->execute($ids);
$foodById = [];
foreach ($stmt->fetchAll() as $row) {
    $foodById[$row['id']] = $row;
}

$lineItems = [];
$total = 0;
foreach ($_SESSION['cart'] as $id => $entry) {
    if (!isset($foodById[$id])) continue;
    $food = $foodById[$id];
    $subtotal = $food['price'] * $entry['quantity'];
    $total += $subtotal;
    $lineItems[] = [
        'food_item_id' => $id,
        'name'         => $food['name'],
        'price'        => $food['price'],
        'quantity'     => $entry['quantity'],
        'subtotal'     => $subtotal,
    ];
}

if (count($lineItems) === 0) {
    header("Location: cart.php");
    exit;
}

try {
    $conn->beginTransaction();

    // Insert with a temporary unique code, then rewrite it using the new order id.
    $tmpCode = 'TMP' . uniqid();
    $stmt = $conn->prepare(
        "INSERT INTO orders (order_code, user_id, total_amount, status)
         VALUES (:code, :user_id, :total, 'Pending')"
    );
    $stmt->execute([
        ':code'    => $tmpCode,
        ':user_id' => current_user_id(),
        ':total'   => $total,
    ]);

    $orderId = (int)$conn->lastInsertId();
    $orderCode = 'ORD' . date('Y') . str_pad($orderId, 4, '0', STR_PAD_LEFT);

    $conn->prepare("UPDATE orders SET order_code = :code WHERE id = :id")
         ->execute([':code' => $orderCode, ':id' => $orderId]);

    $itemStmt = $conn->prepare(
        "INSERT INTO order_items (order_id, food_item_id, item_name, price, quantity, subtotal)
         VALUES (:order_id, :food_item_id, :name, :price, :quantity, :subtotal)"
    );
    foreach ($lineItems as $li) {
        $itemStmt->execute([
            ':order_id'     => $orderId,
            ':food_item_id' => $li['food_item_id'],
            ':name'         => $li['name'],
            ':price'        => $li['price'],
            ':quantity'     => $li['quantity'],
            ':subtotal'     => $li['subtotal'],
        ]);
    }

    $conn->commit();

    // Order placed - empty the cart.
    $_SESSION['cart'] = [];

    header("Location: order_confirmation.php?code=" . urlencode($orderCode));
    exit;

} catch (Exception $e) {
    $conn->rollBack();
    header("Location: cart.php?error=order_failed");
    exit;
}
