<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/functions.php";
require_admin();

/* Issue book */
if (isset($_POST['issue'])) {
  $user_id = (int)($_POST['user_id'] ?? 0);
  $book_id = (int)($_POST['book_id'] ?? 0);
  $days = max(1, (int)($_POST['days'] ?? 7));

  // check available
  $st = $conn->prepare("SELECT copies_available FROM books WHERE id=?");
  $st->bind_param("i",$book_id);
  $st->execute();
  $avail = (int)($st->get_result()->fetch_assoc()['copies_available'] ?? 0);

  if ($avail <= 0) {
    set_flash('danger','No copies available.');
    header("Location: /school_library/admin/loans.php");
    exit;
  }

  $issue_date = date('Y-m-d');
  $due_date = date('Y-m-d', strtotime("+$days days"));

  $st = $conn->prepare("INSERT INTO loans(user_id,book_id,issue_date,due_date,status,fine) VALUES(?,?,?,?, 'issued', 0)");
  $st->bind_param("iiss",$user_id,$book_id,$issue_date,$due_date);
  $st->execute();

  // decrease available
  $st = $conn->prepare("UPDATE books SET copies_available = copies_available - 1 WHERE id=?");
  $st->bind_param("i",$book_id);
  $st->execute();

  set_flash('success','Book issued ✅');
  header("Location: /school_library/admin/loans.php");
  exit;
}

/* Return */
if (isset($_GET['return'])) {
  $loan_id = (int)$_GET['return'];

  // get loan
  $st = $conn->prepare("SELECT book_id, due_date FROM loans WHERE id=? AND status IN('issued','overdue')");
  $st->bind_param("i",$loan_id);
  $st->execute();
  $loan = $st->get_result()->fetch_assoc();

  if ($loan) {
    $book_id = (int)$loan['book_id'];
    $due = $loan['due_date'];

    $return_date = date('Y-m-d');

    // fine calc (Rs. 10 per day late)
    $fine = 0;
    if ($due && strtotime($return_date) > strtotime($due)) {
      $daysLate = (int) floor((strtotime($return_date) - strtotime($due)) / 86400);
      $fine = max(0, $daysLate * 10);
    }

    $st = $conn->prepare("UPDATE loans SET return_date=?, status='returned', fine=? WHERE id=?");
    $st->bind_param("sdi",$return_date,$fine,$loan_id);
    $st->execute();

    // increase available
    $st = $conn->prepare("UPDATE books SET copies_available = copies_available + 1 WHERE id=?");
    $st->bind_param("i",$book_id);
    $st->execute();

    set_flash('success',"Returned ✅ Fine: Rs. ".number_format($fine,2));
  }

  header("Location: /school_library/admin/loans.php");
  exit;
}

/* Auto mark overdue (optional) */
$conn->query("UPDATE loans SET status='overdue' WHERE status='issued' AND due_date < CURDATE()");

$users = $conn->query("SELECT id, full_name, email FROM users WHERE role='user' ORDER BY full_name ASC");
$books = $conn->query("SELECT id, title, author, copies_available FROM books ORDER BY title ASC");

$list = $conn->query("
  SELECT l.*, u.full_name, u.email, b.title, b.author
  FROM loans l
  JOIN users u ON u.id=l.user_id
  JOIN books b ON b.id=l.book_id
  ORDER BY l.issue_date DESC
");

require_once __DIR__ . "/../includes/header.php";
?>

<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h4 class="fw-bold mb-0">📌 Loans Management</h4>
    <a class="btn btn-outline-dark" href="/school_library/admin/dashboard.php">← Dashboard</a>
  </div>

  <?php if (has_flash()): ?>
    <div class="alert alert-<?= e(get_flash_type()) ?> mt-3"><?= e(get_flash_message()) ?></div>
  <?php endif; ?>

  <div class="card p-4 mt-3">
    <h6 class="fw-bold mb-3">Issue Book</h6>
    <form method="post" class="row g-2">
      <input type="hidden" name="issue" value="1">

      <div class="col-md-4">
        <label class="form-label fw-semibold">User</label>
        <select class="form-select" name="user_id" required>
          <option value="">-- Select user --</option>
          <?php while($u=$users->fetch_assoc()): ?>
            <option value="<?= (int)$u['id'] ?>"><?= e($u['full_name']) ?> (<?= e($u['email']) ?>)</option>
          <?php endwhile; ?>
        </select>
      </div>

      <div class="col-md-5">
        <label class="form-label fw-semibold">Book</label>
        <select class="form-select" name="book_id" required>
          <option value="">-- Select book --</option>
          <?php while($b=$books->fetch_assoc()): ?>
            <option value="<?= (int)$b['id'] ?>">
              <?= e($b['title']) ?> - <?= e($b['author']) ?> (Avail: <?= (int)$b['copies_available'] ?>)
            </option>
          <?php endwhile; ?>
        </select>
      </div>

      <div class="col-md-2">
        <label class="form-label fw-semibold">Days</label>
        <input type="number" class="form-control" name="days" value="7" min="1">
      </div>

      <div class="col-md-1 d-grid">
        <label class="form-label">&nbsp;</label>
        <button class="btn btn-dark">Issue</button>
      </div>
    </form>
  </div>

  <div class="card p-4 mt-3">
    <h6 class="fw-bold mb-3">All Loans</h6>
    <div class="table-responsive">
      <table class="table table-striped align-middle">
        <thead class="table-light">
          <tr>
            <th>User</th><th>Book</th><th>Issue</th><th>Due</th><th>Return</th><th>Status</th><th>Fine</th><th class="text-end">Action</th>
          </tr>
        </thead>
        <tbody>
        <?php while($r=$list->fetch_assoc()): ?>
          <tr>
            <td>
              <div class="fw-bold"><?= e($r['full_name']) ?></div>
              <div class="small text-muted"><?= e($r['email']) ?></div>
            </td>
            <td>
              <div class="fw-bold"><?= e($r['title']) ?></div>
              <div class="small text-muted"><?= e($r['author']) ?></div>
            </td>
            <td><?= e($r['issue_date']) ?></td>
            <td><?= e($r['due_date']) ?></td>
            <td><?= e($r['return_date'] ?? '-') ?></td>
            <td><span class="badge bg-<?= $r['status']==='overdue'?'danger':($r['status']==='returned'?'success':'primary') ?>">
              <?= e($r['status']) ?></span></td>
            <td>Rs. <?= number_format((float)$r['fine'],2) ?></td>
            <td class="text-end">
              <?php if(in_array($r['status'],['issued','overdue'],true)): ?>
                <a class="btn btn-sm btn-success" href="/school_library/admin/loans.php?return=<?= (int)$r['id'] ?>"
                   onclick="return confirm('Return this book?')">Return</a>
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

</div>
<?php require_once __DIR__ . "/../includes/footer.php"; ?>
