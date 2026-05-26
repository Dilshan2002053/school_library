<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/functions.php";
require_user();

$uid = (int) current_user()['id'];

$stmt = $conn->prepare("
  SELECT p.*, b.title, b.author
  FROM book_purchases p
  JOIN books b ON b.id=p.book_id
  WHERE p.user_id=?
  ORDER BY p.purchased_at DESC
");
$stmt->bind_param("i",$uid);
$stmt->execute();
$rows = $stmt->get_result();

require_once __DIR__ . "/../includes/header.php";
?>
<div class="card p-4">
  <div class="d-flex justify-content-between align-items-center">
    <h5 class="fw-bold mb-0">🛒 Purchase History</h5>
    <a href="/school_library/user/catalog.php" class="btn btn-outline-dark">Buy More</a>
  </div>
  <hr>

  <div class="table-responsive">
    <table class="table table-striped align-middle">
      <thead class="table-light">
        <tr><th>Book</th><th>Qty</th><th>Price</th><th>Total</th><th>Date</th></tr>
      </thead>
      <tbody>
      <?php if($rows->num_rows==0): ?>
        <tr><td colspan="5" class="text-center text-muted py-4">No purchases yet.</td></tr>
      <?php else: ?>
        <?php while($p=$rows->fetch_assoc()): ?>
          <tr>
            <td><div class="fw-bold"><?= e($p['title']) ?></div><div class="small text-muted"><?= e($p['author']) ?></div></td>
            <td><?= (int)$p['qty'] ?></td>
            <td>Rs. <?= number_format((float)$p['price_each'],2) ?></td>
            <td><b>Rs. <?= number_format((float)$p['total_amount'],2) ?></b></td>
            <td><?= e($p['purchased_at']) ?></td>
          </tr>
        <?php endwhile; ?>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require_once __DIR__ . "/../includes/footer.php"; ?>
