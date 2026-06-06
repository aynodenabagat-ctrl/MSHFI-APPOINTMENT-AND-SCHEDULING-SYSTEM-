<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

function requireRole(array|string $role): void {
    requireLogin();
    $allowed = is_array($role) ? $role : [$role];
    if (!in_array($_SESSION['user_role'], $allowed, true)) {
        header('Location: ' . BASE_URL . '/index.php');
        exit;
    }
}

function generateCsrfToken(): string {
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf_token'];
}

function validateCsrfToken(string $token): bool {
    return hash_equals($_SESSION['_csrf_token'] ?? '', $token);
}

function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

function getCurrentUserRole() {
    return $_SESSION['user_role'] ?? null;
}
