<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/functions.php";
require_user();

$book_id = (int)($_GET['id'] ?? 0);
if ($book_id <= 0) { header("Location: /school_library/user/catalog.php"); exit; }

$stmt = $conn->prepare("SELECT b.*, c.name category
                        FROM books b
                        LEFT JOIN categories c ON c.id=b.category_id
                        WHERE b.id=? LIMIT 1");
$stmt->bind_param("i",$book_id);
$stmt->execute();
$book = $stmt->get_result()->fetch_assoc();

if (!$book) {
  set_flash('danger','Book not found.');
  header("Location: /school_library/user/catalog.php");
  exit;
}

require_once __DIR__ . "/../includes/header.php";
?>
<div class="row g-4">
  <div class="col-md-8">
    <div class="card p-4">
      <h4 class="fw-bold mb-1"><?= e($book['title']) ?></h4>
      <div class="text-muted mb-2">by <?= e($book['author']) ?></div>
      <div class="small text-muted">ISBN: <?= e($book['isbn']) ?></div>
      <hr>

      <div class="row g-3">
        <div class="col-md-6"><div class="small text-muted">Category</div><div class="fw-semibold"><?= e($book['category'] ?? '-') ?></div></div>
        <div class="col-md-6"><div class="small text-muted">Shelf</div><div class="fw-semibold"><?= e($book['shelf'] ?? '-') ?></div></div>
        <div class="col-md-6"><div class="small text-muted">Available Copies</div><div class="fw-semibold"><?= (int)$book['copies_available'] ?></div></div>
        <div class="col-md-6"><div class="small text-muted">Price</div><div class="fw-semibold">Rs. <?= number_format((float)$book['price'],2) ?></div></div>
      </div>

      <div class="mt-4 d-flex flex-wrap gap-2">
        <a href="/school_library/user/catalog.php" class="btn btn-outline-dark">← Back</a>
        <a href="/school_library/user/order_book.php?id=<?= (int)$book['id'] ?>" class="btn btn-outline-primary">📦 Order</a>
        <a href="/school_library/user/buy_book.php?id=<?= (int)$book['id'] ?>" class="btn btn-success">🛒 Buy</a>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card p-4">
      <h6 class="fw-bold">Quick Links</h6>
      <a class="btn btn-dark w-100 mb-2" href="/school_library/user/my_orders.php">📦 My Orders</a>
      <a class="btn btn-outline-dark w-100" href="/school_library/user/purchase_history.php">🛒 Purchase History</a>
      <div class="alert alert-info mt-3 mb-0 small">Borrowing books is handled by admin issue system.</div>
    </div>
  </div>
</div>
<?php require_once __DIR__ . "/../includes/footer.php"; ?>
