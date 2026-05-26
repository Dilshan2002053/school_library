<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/functions.php";
require_user();

$uid = (int) current_user()['id'];
$book_id = (int)($_GET['id'] ?? 0);
if ($book_id <= 0) { header("Location: /school_library/user/catalog.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $qty = (int)($_POST['qty'] ?? 1);
  if ($qty <= 0) $qty = 1;

  $stmt = $conn->prepare("INSERT INTO book_orders(user_id, book_id, qty, status) VALUES(?,?,?,'pending')");
  $stmt->bind_param("iii", $uid, $book_id, $qty);
  $stmt->execute();

  set_flash('success', 'Order placed ✅ Waiting admin approval.');
  header("Location: /school_library/user/my_orders.php");
  exit;
}

$stmt = $conn->prepare("SELECT id,title,author FROM books WHERE id=? LIMIT 1");
$stmt->bind_param("i",$book_id);
$stmt->execute();
$book = $stmt->get_result()->fetch_assoc();

require_once __DIR__ . "/../includes/header.php";
?>
<div class="card p-4">
  <h5 class="fw-bold mb-1">📦 Place Order</h5>
  <div class="text-muted mb-3"><?= e($book['title']) ?> — <?= e($book['author']) ?></div>

  <form method="post">
    <label class="form-label fw-semibold">Quantity</label>
    <input type="number" min="1" value="1" name="qty" class="form-control" style="max-width:200px">
    <button class="btn btn-dark mt-3">Submit Order</button>
    <a class="btn btn-outline-dark mt-3" href="/school_library/user/book_details.php?id=<?= (int)$book_id ?>">Cancel</a>
  </form>
</div>
<?php require_once __DIR__ . "/../includes/footer.php"; ?>
