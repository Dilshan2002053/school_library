<?php
// includes/functions.php
// ✅ Single clean version (no duplicates)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* Escape output */
function e($str): string {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

/* -----------------------------
   Flash Messages (FULL SYSTEM)
------------------------------*/
function set_flash(string $type, string $msg): void {
    $_SESSION['flash'] = [
        'type' => $type,
        'msg'  => $msg
    ];
}

function has_flash(): bool {
    return !empty($_SESSION['flash']) && !empty($_SESSION['flash']['msg']);
}

function get_flash_type(): string {
    return $_SESSION['flash']['type'] ?? 'info';
}

function get_flash_message(): string {
    return $_SESSION['flash']['msg'] ?? '';
}

function clear_flash(): void {
    unset($_SESSION['flash']);
}

/* Print flash and auto-clear */
function flash(): void {
    if (!has_flash()) return;

    $type = get_flash_type();
    $msg  = get_flash_message();
    clear_flash();

    echo '<div class="alert alert-' . e($type) . ' alert-dismissible fade show rounded-4" role="alert">';
    echo e($msg);
    echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
    echo '</div>';
}
?>
