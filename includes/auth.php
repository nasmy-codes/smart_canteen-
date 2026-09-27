<?php

function is_logged_in(): bool {
    return isset($_SESSION['user_id']);
}

function is_admin(): bool {
    return is_logged_in() && $_SESSION['role'] === 'admin';
}

function current_user_id() {
    return $_SESSION['user_id'] ?? null;
}

function require_login(string $redirect = 'login.php'): void {
    if (!is_logged_in()) {
        header("Location: $redirect");
        exit;
    }
}

function require_admin(string $redirect = '../pages/login.php'): void {
    if (!is_admin()) {
        header("Location: $redirect");
        exit;
    }
}
function h(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}
