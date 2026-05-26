<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/functions.php";
require_user();

$uid = (int) current_user()['id'];
$book_id = (int)($_GET['id'] ?? 0);
if ($book_id <= 0) { header("Location: /school_library/user/catalog.php"); exit; }

$stmt = $conn->prepare("SELECT id,title,author,price FROM books WHERE id=? LIMIT 1");
$stmt->bind_param("i",$book_id);
$stmt->execute();
$book = $stmt->get_result()->fetch_assoc();
if (!$book) { set_flash('danger','Book not found'); header("Location: /school_library/user/catalog.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $qty = (int)($_POST['qty'] ?? 1);
  if ($qty <= 0) $qty = 1;

  $price = (float)$book['price'];
  $total = $price * $qty;

  $ins = $conn->prepare("INSERT INTO book_purchases(user_id, book_id, qty, price_each, total_amount) VALUES(?,?,?,?,?)");
  $ins->bind_param("iiidd", $uid, $book_id, $qty, $price, $total);
  $ins->execute();

  set_flash('success','Purchase successful ✅');
  header("Location: /school_library/user/purchase_history.php");
  exit;
}

require_once __DIR__ . "/../includes/header.php";
?>
<div class="card p-4">
  <h5 class="fw-bold mb-1">🛒 Buy Book</h5>
  <div class="text-muted mb-2"><?= e($book['title']) ?> — <?= e($book['author']) ?></div>
  <div class="alert alert-info">Price: <b>Rs. <?= number_format((float)$book['price'],2) ?></b></div>

  <form method="post">
    <label class="form-label fw-semibold">Quantity</label>
    <input type="number" min="1" value="1" name="qty" class="form-control" style="max-width:200px">
    <button class="btn btn-success mt-3">Confirm Purchase</button>
    <a class="btn btn-outline-dark mt-3" href="/school_library/user/book_details.php?id=<?= (int)$book_id ?>">Cancel</a>
  </form>
</div>
<?php require_once __DIR__ . "/../includes/footer.php"; ?>
