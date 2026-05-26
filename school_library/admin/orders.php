<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/functions.php";
require_admin();

if (isset($_GET['approve'])) {
  $id=(int)$_GET['approve'];
  $stmt=$conn->prepare("UPDATE book_orders SET status='approved', approved_at=NOW() WHERE id=?");
  $stmt->bind_param("i",$id);
  $stmt->execute();
  set_flash('success','Order approved.');
  header("Location: /school_library/admin/orders.php");
  exit;
}

if (isset($_GET['reject'])) {
  $id=(int)$_GET['reject'];
  $stmt=$conn->prepare("UPDATE book_orders SET status='rejected', approved_at=NOW() WHERE id=?");
  $stmt->bind_param("i",$id);
  $stmt->execute();
  set_flash('success','Order rejected.');
  header("Location: /school_library/admin/orders.php");
  exit;
}

$rows = $conn->query("
  SELECT o.*, u.full_name, u.email, b.title, b.author
  FROM book_orders o
  JOIN users u ON u.id=o.user_id
  JOIN books b ON b.id=o.book_id
  ORDER BY o.requested_at DESC
");

require_once __DIR__ . "/../includes/header.php";
?>
<div class="card p-4">
  <h5 class="fw-bold mb-3">📦 Book Orders</h5>

  <div class="table-responsive">
    <table class="table table-striped align-middle">
      <thead class="table-light">
        <tr><th>User</th><th>Book</th><th>Qty</th><th>Status</th><th>Requested</th><th class="text-end">Action</th></tr>
      </thead>
      <tbody>
      <?php while($r=$rows->fetch_assoc()): ?>
        <tr>
          <td><div class="fw-bold"><?= e($r['full_name']) ?></div><div class="small text-muted"><?= e($r['email']) ?></div></td>
          <td><div class="fw-bold"><?= e($r['title']) ?></div><div class="small text-muted"><?= e($r['author']) ?></div></td>
          <td><?= (int)$r['qty'] ?></td>
          <td><span class="badge bg-dark"><?= e($r['status']) ?></span></td>
          <td><?= e($r['requested_at']) ?></td>
          <td class="text-end">
            <?php if($r['status']==='pending'): ?>
              <a class="btn btn-sm btn-success" href="?approve=<?= (int)$r['id'] ?>">Approve</a>
              <a class="btn btn-sm btn-danger" href="?reject=<?= (int)$r['id'] ?>">Reject</a>
            <?php else: ?>
              <span class="text-muted small">Done</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require_once __DIR__ . "/../includes/footer.php"; ?>
