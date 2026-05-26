<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/functions.php";
require_user();

$uid = (int) current_user()['id'];

$stmt = $conn->prepare("
  SELECT o.*, b.title, b.author
  FROM book_orders o
  JOIN books b ON b.id=o.book_id
  WHERE o.user_id=?
  ORDER BY o.requested_at DESC
");
$stmt->bind_param("i",$uid);
$stmt->execute();
$orders = $stmt->get_result();

require_once __DIR__ . "/../includes/header.php";
?>
<div class="card p-4">
  <div class="d-flex justify-content-between align-items-center">
    <h5 class="fw-bold mb-0">📦 My Orders</h5>
    <a href="/school_library/user/catalog.php" class="btn btn-outline-dark">Browse Books</a>
  </div>
  <hr>

  <div class="table-responsive">
    <table class="table table-striped align-middle">
      <thead class="table-light">
        <tr><th>Book</th><th>Qty</th><th>Status</th><th>Requested</th><th>Admin Note</th></tr>
      </thead>
      <tbody>
      <?php if($orders->num_rows==0): ?>
        <tr><td colspan="5" class="text-center text-muted py-4">No orders yet.</td></tr>
      <?php else: ?>
        <?php while($o=$orders->fetch_assoc()): ?>
          <?php
            $s=$o['status'];
            $badge = ($s==='approved')?'bg-success':(($s==='rejected')?'bg-danger':(($s==='cancelled')?'bg-secondary':'bg-warning text-dark'));
          ?>
          <tr>
            <td><div class="fw-bold"><?= e($o['title']) ?></div><div class="small text-muted"><?= e($o['author']) ?></div></td>
            <td><?= (int)$o['qty'] ?></td>
            <td><span class="badge <?= $badge ?>"><?= e($s) ?></span></td>
            <td><?= e($o['requested_at']) ?></td>
            <td><?= e($o['admin_note'] ?? '-') ?></td>
          </tr>
        <?php endwhile; ?>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require_once __DIR__ . "/../includes/footer.php"; ?>
