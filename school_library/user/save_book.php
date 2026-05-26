<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/functions.php";
require_user();

$uid = (int) current_user()['id'];
$book_id = (int)($_GET['id'] ?? 0);

if ($book_id <= 0) {
    header("Location: /school_library/user/catalog.php");
    exit;
}

$stmt = $conn->prepare("INSERT IGNORE INTO user_saved_books(user_id, book_id) VALUES(?,?)");
$stmt->bind_param("ii", $uid, $book_id);
$stmt->execute();

set_flash('success', 'Book saved successfully ⭐');
header("Location: /school_library/user/book_details.php?id=".$book_id);
exit;
