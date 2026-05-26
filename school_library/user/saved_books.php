<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/functions.php";
require_user();

$uid = (int) current_user()['id'];

$stmt = $conn->prepare("
  SELECT b.id, b.title, b.author, b.isbn, b.copies_available, s.note, s.created_at
  FROM user_saved_books s
  JOIN books b ON b.id = s.book_id
  WHERE s.user_id=?
  ORDER BY s.created_at DESC
");
$stmt->bind_param("i", $uid);
$stmt->execute();
$saved = $stmt->get_result();

require_once __DIR__ . "/../includes/header.php";
?>

<div class="card p-4">
  <div class="d-flex justify-content-between align-items-center">
    <div>
      <h5 class="fw-bold mb-0">⭐ My Saved Books</h5>
      <div class="text-muted small">Your favorite / selected books list</div>
    </div>
    <a href="/school_library/user/catalog.php" class="btn btn-outline-dark">← Back to Catalog</a>
  </div>

  <hr>

  <div class="table-responsive">
    <table class="table table-striped align-middle">
      <thead class="table-light">
        <tr>
          <th>Book</th>
          <th>Available</th>
          <th>My Note</th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php if ($saved->num_rows == 0): ?>
        <tr><td colspan="4" class="text-center text-muted py-4">No saved books yet.</td></tr>
      <?php else: ?>
        <?php while($r=$saved->fetch_assoc()): ?>
          <tr>
            <td>
              <div class="fw-bold"><?= e($r['title']) ?></div>
              <div class="small text-muted"><?= e($r['author']) ?> | ISBN: <?= e($r['isbn']) ?></div>
            </td>
            <td>
              <span class="badge <?= ((int)$r['copies_available']>0?'bg-success':'bg-danger') ?>">
                <?= (int)$r['copies_available'] ?>
              </span>
            </td>
            <td><?= e($r['note'] ?? '-') ?></td>
            <td class="text-end">
              <a class="btn btn-sm btn-outline-primary" href="/school_library/user/book_details.php?id=<?= (int)$r['id'] ?>">View</a>
              <a class="btn btn-sm btn-outline-danger" href="/school_library/user/unsave_book.php?id=<?= (int)$r['id'] ?>">Remove</a>
            </td>
          </tr>
        <?php endwhile; ?>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>
