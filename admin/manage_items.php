<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_admin();

$error = '';
$success = '';

// ---------- Handle form submissions ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['action']) && $_POST['action'] === 'save') {
        $id          = (int)($_POST['id'] ?? 0);
        $name        = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price       = (float)($_POST['price'] ?? 0);
        $category    = trim($_POST['category'] ?? '');
        $image       = trim($_POST['image'] ?? '');
        $status      = ($_POST['status'] ?? 'available') === 'available' ? 'available' : 'unavailable';

        if ($name === '' || $category === '' || $price <= 0) {
            $error = 'Please fill in name, category and a valid price.';
        } else {
            if ($id > 0) {
                $stmt = $conn->prepare(
                    "UPDATE food_items SET name=:name, description=:desc, price=:price,
                     category=:cat, image=:img, status=:status WHERE id=:id"
                );
                $stmt->execute([
                    ':name' => $name, ':desc' => $description, ':price' => $price,
                    ':cat' => $category, ':img' => $image, ':status' => $status, ':id' => $id,
                ]);
                $success = 'Item updated successfully.';
            } else {
                $stmt = $conn->prepare(
                    "INSERT INTO food_items (name, description, price, category, image, status)
                     VALUES (:name, :desc, :price, :cat, :img, :status)"
                );
                $stmt->execute([
                    ':name' => $name, ':desc' => $description, ':price' => $price,
                    ':cat' => $category, ':img' => $image, ':status' => $status,
                ]);
                $success = 'Item added successfully.';
            }
        }
    }

    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $conn->prepare("DELETE FROM food_items WHERE id = :id")->execute([':id' => $id]);
        $success = 'Item deleted.';
    }
}

// ---------- Load item to edit (if any) ----------
$editItem = null;
if (isset($_GET['edit'])) {
    $stmt = $conn->prepare("SELECT * FROM food_items WHERE id = :id");
    $stmt->execute([':id' => (int)$_GET['edit']]);
    $editItem = $stmt->fetch();
}

$allItems = $conn->query("SELECT * FROM food_items ORDER BY category, name")->fetchAll();

$base = '../';
$admin_active = 'items';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Food Items | SmartCanteen Admin</title>
<link rel="stylesheet" href="../css/style.css">
<link rel="stylesheet" href="../css/admin.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

<?php include '../includes/header.php'; ?>

<div class="admin-wrap">
    <?php include '../includes/admin_sidebar.php'; ?>

    <main class="admin-main">
        <h1>Manage Food Items</h1>
        <p class="subtitle">Add, edit or remove items from the canteen menu.</p>

        <?php if ($error): ?><div class="alert alert-error"><?= h($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= h($success) ?></div><?php endif; ?>

        <div class="admin-panel">
            <h2><?= $editItem ? 'Edit Item' : 'Add New Item' ?></h2>
            <form method="POST" action="manage_items.php">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="id" value="<?= $editItem ? (int)$editItem['id'] : 0 ?>">

                <div class="two-col-form">
                    <div class="form-group">
                        <label>Item Name</label>
                        <input type="text" name="name" required value="<?= h($editItem['name'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Category</label>
                        <input type="text" name="category" required placeholder="e.g. Main Meals, Short Eats, Beverages"
                               value="<?= h($editItem['category'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Price (Rs.)</label>
                        <input type="number" step="0.01" min="0" name="price" required
                               value="<?= $editItem['price'] ?? '' ?>">
                    </div>
                    <div class="form-group">
                        <label>Image filename (in /images)</label>
                        <input type="text" name="image" placeholder="e.g. chicken-rice.jpg"
                               value="<?= h($editItem['image'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status">
                            <option value="available" <?= (($editItem['status'] ?? 'available') === 'available') ? 'selected' : '' ?>>Available</option>
                            <option value="unavailable" <?= (($editItem['status'] ?? '') === 'unavailable') ? 'selected' : '' ?>>Unavailable</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <input type="text" name="description" value="<?= h($editItem['description'] ?? '') ?>">
                    </div>
                </div>

                <button type="submit" class="btn"><?= $editItem ? 'Update Item' : 'Add Item' ?></button>
                <?php if ($editItem): ?>
                    <a href="manage_items.php" class="btn btn-outline">Cancel</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="admin-panel">
            <h2>All Menu Items (<?= count($allItems) ?>)</h2>
            <table class="data-table">
                <thead>
                    <tr><th>Image</th><th>Name</th><th>Category</th><th>Price</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php if (count($allItems) === 0): ?>
                        <tr><td colspan="6">No items yet. Add your first one above.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($allItems as $item): ?>
                        <tr>
                            <td><img class="thumb" src="../images/<?= h($item['image']) ?: 'header.jpg' ?>" alt=""></td>
                            <td><?= h($item['name']) ?></td>
                            <td><?= h($item['category']) ?></td>
                            <td>Rs. <?= number_format($item['price'], 2) ?></td>
                            <td>
                                <span class="badge-<?= $item['status'] ?>"><?= h(ucfirst($item['status'])) ?></span>
                            </td>
                            <td class="action-links">
                                <a href="manage_items.php?edit=<?= (int)$item['id'] ?>">Edit</a>
                                <form method="POST" action="manage_items.php" style="display:inline;"
                                      onsubmit="return confirm('Delete this item permanently?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= (int)$item['id'] ?>">
                                    <button type="submit" class="danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<?php include '../includes/footer.php'; ?>

</body>
</html>
