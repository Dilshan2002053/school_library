<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/functions.php";
require_user();

$uid = (int) current_user()['id'];
$book_id = (int)($_GET['id'] ?? 0);

$stmt = $conn->prepare("DELETE FROM user_saved_books WHERE user_id=? AND book_id=?");
$stmt->bind_param("ii", $uid, $book_id);
$stmt->execute();

set_flash('success', 'Removed from saved books.');
header("Location: /school_library/user/saved_books.php");
exit;
