<?php
// includes/auth.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function is_logged_in(): bool {
    return isset($_SESSION['user']);
}

function current_user() {
    return $_SESSION['user'] ?? null;
}

function require_login() {
    if (!is_logged_in()) {
        header("Location: /school_library/login.php");
        exit;
    }
}

function require_admin() {
    require_login();
    if (current_user()['role'] !== 'admin') {
        header("Location: /school_library/user/dashboard.php");
        exit;
    }
}

function require_user() {
    require_login();
    if (current_user()['role'] !== 'user') {
        header("Location: /school_library/admin/dashboard.php");
        exit;
    }
}
