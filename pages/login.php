<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';

// Already logged in? Send them where they belong.
if (is_logged_in()) {
    header("Location: " . (is_admin() ? "../admin/dashboard.php" : "menu.php"));
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Please enter both username and password.';
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Prevent session fixation
            session_regenerate_id(true);

            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'];

            if ($user['role'] === 'admin') {
                header("Location: ../admin/dashboard.php");
            } else {
                header("Location: menu.php");
            }
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    }
}

$base = '../';
$active = 'login';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login | SmartCanteen</title>
<link rel="stylesheet" href="../css/style.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

<?php include '../includes/header.php'; ?>

<div class="form-wrap">
    <div class="form-card">
        <h2>Login to SmartCanteen</h2>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= h($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Enter your username" required
                       value="<?= h($_POST['username'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
            </div>
            <button type="submit" class="btn btn-block">Login</button>
        </form>

        <p class="form-footer-link">Don't have an account? <a href="register.php">Register here</a></p>
        <p class="form-footer-link" style="margin-top:6px;font-size:12px;color:#999;">
        </p>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

</body>
</html>
