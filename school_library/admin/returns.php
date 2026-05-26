<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/functions.php";
require_admin();

if (isset($_GET['return'])) {
    $loan_id = (int)$_GET['return'];
    try {
        $stmt = $conn->prepare("CALL sp_return_book(?)");
        $stmt->bind_param("i", $loan_id);
        $stmt->execute();
        set_flash('success', 'Returned successfully (fine calculated).');
    } catch (Exception $e) {
        set_flash('danger', 'Return failed: ' . $e->getMessage());
    }
    header("Location: /school_library/admin/returns.php");
    exit;
}

require_once __DIR__ . "/../includes/header.php";
$issued = $conn->query("SELECT l.id, b.title, u.full_name, l.issue_date, l.due_date
                        FROM loans l
                        JOIN books b ON b.id=l.book_id
                        JOIN users u ON u.id=l.user_id
                        WHERE l.status IN('issued','overdue')
                        ORDER BY l.due_date ASC");
$history = $conn->query("SELECT l.id, b.title, u.full_name, l.return_date, l.fine
                         FROM loans l
                         JOIN books b ON b.id=l.book_id
                         JOIN users u ON u.id=l.user_id
                         WHERE l.status='returned'
                         ORDER BY l.id DESC LIMIT 20");
?>
<div class="row g-4">
  <div class="col-md-6">
    <div class="card p-4">
      <h5 class="fw-bold">Return Book</h5>
      <table class="table table-striped align-middle">
        <thead><tr><th>Loan</th><th>Book</th><th>User</th><th>Due</th><th>Action</th></tr></thead>
        <tbody>
        <?php while($r=$issued->fetch_assoc()): ?>
          <tr>
            <td><?= (int)$r['id'] ?></td>
            <td><?= e($r['title']) ?></td>
            <td><?= e($r['full_name']) ?></td>
            <td><?= e($r['due_date']) ?></td>
            <td><a class="btn btn-sm btn-dark" href="?return=<?= (int)$r['id'] ?>">Return</a></td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="col-md-6">
    <div class="card p-4">
      <h5 class="fw-bold">Recent Returns</h5>
      <table class="table table-striped align-middle">
        <thead><tr><th>Loan</th><th>Book</th><th>User</th><th>Return Date</th><th>Fine</th></tr></thead>
        <tbody>
        <?php while($h=$history->fetch_assoc()): ?>
          <tr>
            <td><?= (int)$h['id'] ?></td>
            <td><?= e($h['title']) ?></td>
            <td><?= e($h['full_name']) ?></td>
            <td><?= e($h['return_date']) ?></td>
            <td>Rs. <?= e($h['fine']) ?></td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php require_once __DIR__ . "/../includes/footer.php"; ?>
