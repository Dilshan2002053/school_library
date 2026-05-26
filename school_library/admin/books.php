<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/functions.php";
require_admin();

/* Categories */
$cats = $conn->query("SELECT * FROM categories ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);

/* Delete */
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM books WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        set_flash('success', 'Book deleted ✅');
    }
    header("Location: /school_library/admin/books.php");
    exit;
}

/* Add / Update */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $isbn = trim($_POST['isbn'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $publisher = trim($_POST['publisher'] ?? '');
    $year = (int)($_POST['publish_year'] ?? 0);
    $shelf = trim($_POST['shelf'] ?? '');
    $category_id = ($_POST['category_id'] ?? '') === '' ? null : (int)$_POST['category_id'];
    $total = max(1, (int)($_POST['copies_total'] ?? 1));
    $price = (float)($_POST['price'] ?? 0);

    if ($isbn === '' || $title === '' || $author === '') {
        set_flash('danger', 'ISBN, Title, and Author are required.');
        header("Location: /school_library/admin/books.php");
        exit;
    }

    if ($id > 0) {
        /* keep available <= total */
        $s = $conn->prepare("SELECT copies_available FROM books WHERE id=?");
        $s->bind_param("i",$id);
        $s->execute();
        $row = $s->get_result()->fetch_assoc();
        $oldAvail = (int)($row['copies_available'] ?? 0);
        $newAvail = min($oldAvail, $total);

        $stmt = $conn->prepare("
            UPDATE books
            SET isbn=?, title=?, author=?, publisher=?, publish_year=?, shelf=?, category_id=?, copies_total=?, copies_available=?, price=?
            WHERE id=?
        ");
        // s s s s i s i i i d i  => "ssssisiiidi"
        $stmt->bind_param(
            "ssssisiiidi",
            $isbn, $title, $author, $publisher, $year, $shelf,
            $category_id, $total, $newAvail, $price, $id
        );
        $stmt->execute();

        set_flash('success', 'Book updated successfully ✅');
        header("Location: /school_library/admin/books.php");
        exit;

    } else {
        $avail = $total;

        $stmt = $conn->prepare("
            INSERT INTO books(isbn,title,author,publisher,publish_year,shelf,category_id,copies_total,copies_available,price)
            VALUES(?,?,?,?,?,?,?,?,?,?)
        ");
        // s s s s i s i i i d  => "ssssisiiid"
        $stmt->bind_param(
            "ssssisiiid",
            $isbn, $title, $author, $publisher, $year, $shelf,
            $category_id, $total, $avail, $price
        );
        $stmt->execute();

        set_flash('success', 'Book added successfully ✅');
        header("Location: /school_library/admin/books.php");
        exit;
    }
}

/* Fetch books list */
$books = $conn->query("
    SELECT b.*, c.name AS category
    FROM books b
    LEFT JOIN categories c ON c.id=b.category_id
    ORDER BY b.title ASC
");

require_once __DIR__ . "/../includes/header.php";
?>

<style>
  :root{
    --primary-gradient: linear-gradient(135deg,#667eea,#764ba2);
    --success-gradient: linear-gradient(135deg,#0ba360,#3cba92);
  }
  .wrap{ background: linear-gradient(135deg,#f8fafc,#f1f5f9); min-height:calc(100vh - 80px); padding:20px; }
  .cardx{
    background: rgba(255,255,255,.95);
    border:1px solid rgba(0,0,0,.06);
    border-radius: 22px;
    box-shadow: 0 16px 40px rgba(0,0,0,.08);
    padding: 22px;
    margin-bottom: 16px;
    position: relative;
    overflow: hidden;
  }
  .cardx:before{ content:''; position:absolute; top:0; left:0; width:100%; height:6px; background: var(--primary-gradient); }
  .btn-grad{
    background: var(--primary-gradient);
    color:#fff; border:none;
    border-radius: 14px;
    padding: 10px 16px;
    font-weight: 800;
  }
  .btn-grad:hover{ opacity:.95; color:#fff; }
  .table thead th{ white-space: nowrap; }
  .badge-soft{
    padding: 6px 12px; border-radius: 999px; font-weight: 900; font-size: 12px; white-space: nowrap;
    background: rgba(11,163,96,.12); color:#0f5132;
  }
</style>

<div class="wrap">

  <div class="cardx">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
      <div>
        <h4 class="fw-bold mb-1">📘 Manage Books</h4>
        <div class="text-muted">Add, edit, delete books + set price for buying.</div>
      </div>
      <div class="d-flex gap-2 flex-wrap">
        <a class="btn btn-outline-dark" href="/school_library/admin/dashboard.php">← Dashboard</a>
        <a class="btn btn-outline-dark" href="/school_library/admin/orders.php">📦 Orders</a>
        <a class="btn btn-outline-dark" href="/school_library/admin/purchases.php">🛒 Purchases</a>
      </div>
    </div>
  </div>

  <?php if (has_flash()): ?>
    <div class="alert alert-<?= e(get_flash_type()) ?> alert-dismissible fade show rounded-4" role="alert">
      <?= e(get_flash_message()) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <!-- Add / Edit Form -->
  <div class="cardx">
    <h5 class="fw-bold mb-3">➕ Add / Edit Book</h5>

    <form method="post" class="row g-3" id="bookForm">
      <input type="hidden" name="id" id="book_id" value="0">

      <div class="col-md-3">
        <label class="form-label fw-semibold">ISBN *</label>
        <input class="form-control" name="isbn" id="isbn" required>
      </div>

      <div class="col-md-5">
        <label class="form-label fw-semibold">Title *</label>
        <input class="form-control" name="title" id="title" required>
      </div>

      <div class="col-md-4">
        <label class="form-label fw-semibold">Author *</label>
        <input class="form-control" name="author" id="author" required>
      </div>

      <div class="col-md-4">
        <label class="form-label fw-semibold">Publisher</label>
        <input class="form-control" name="publisher" id="publisher">
      </div>

      <div class="col-md-2">
        <label class="form-label fw-semibold">Year</label>
        <input type="number" class="form-control" name="publish_year" id="publish_year" min="0">
      </div>

      <div class="col-md-2">
        <label class="form-label fw-semibold">Shelf</label>
        <input class="form-control" name="shelf" id="shelf">
      </div>

      <div class="col-md-2">
        <label class="form-label fw-semibold">Copies Total *</label>
        <input type="number" class="form-control" name="copies_total" id="copies_total" min="1" value="1" required>
      </div>

      <div class="col-md-2">
        <label class="form-label fw-semibold">Price (Rs.)</label>
        <input type="number" class="form-control" name="price" id="price" step="0.01" min="0" value="0">
      </div>

      <div class="col-md-4">
        <label class="form-label fw-semibold">Category</label>
        <select class="form-select" name="category_id" id="category_id">
          <option value="">-- None --</option>
          <?php foreach($cats as $c): ?>
            <option value="<?= (int)$c['id'] ?>"><?= e($c['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-12 d-flex gap-2 flex-wrap">
        <button class="btn-grad" type="submit">💾 Save Book</button>
        <button class="btn btn-outline-dark" type="button" id="resetBtn">Reset</button>
      </div>
    </form>
  </div>

  <!-- Books List -->
  <div class="cardx">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="fw-bold mb-0">📚 Books List</h5>
      <span class="badge-soft">Total: <?= (int)$books->num_rows ?></span>
    </div>

    <div class="table-responsive">
      <table class="table table-striped align-middle">
        <thead class="table-light">
          <tr>
            <th>Title</th>
            <th>Author</th>
            <th>ISBN</th>
            <th>Category</th>
            <th>Copies</th>
            <th>Available</th>
            <th>Price</th>
            <th class="text-end">Action</th>
          </tr>
        </thead>
        <tbody>
        <?php while($b = $books->fetch_assoc()): ?>
          <tr>
            <td class="fw-bold"><?= e($b['title']) ?></td>
            <td><?= e($b['author']) ?></td>
            <td><?= e($b['isbn']) ?></td>
            <td><?= e($b['category'] ?? '-') ?></td>
            <td><?= (int)$b['copies_total'] ?></td>
            <td><span class="badge bg-success"><?= (int)$b['copies_available'] ?></span></td>
            <td>Rs. <?= number_format((float)$b['price'],2) ?></td>

            <td class="text-end">
              <button
                class="btn btn-sm btn-primary"
                onclick='fillEdit(<?= json_encode($b, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT) ?>)'>
                Edit
              </button>
              <a class="btn btn-sm btn-danger"
                 href="/school_library/admin/books.php?delete=<?= (int)$b['id'] ?>"
                 onclick="return confirm('Delete this book?')">
                Delete
              </a>
            </td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>

    <div class="alert alert-info mb-0">
      ✅ Note: When you reduce total copies, available copies will auto-adjust (available cannot be more than total).
    </div>
  </div>

</div>

<script>
  function fillEdit(b){
    document.getElementById('book_id').value = b.id || 0;
    document.getElementById('isbn').value = b.isbn || '';
    document.getElementById('title').value = b.title || '';
    document.getElementById('author').value = b.author || '';
    document.getElementById('publisher').value = b.publisher || '';
    document.getElementById('publish_year').value = b.publish_year || '';
    document.getElementById('shelf').value = b.shelf || '';
    document.getElementById('copies_total').value = b.copies_total || 1;
    document.getElementById('price').value = b.price || 0;

    // category_id can be null
    document.getElementById('category_id').value = (b.category_id === null) ? '' : b.category_id;

    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  document.getElementById('resetBtn').addEventListener('click', function(){
    document.getElementById('bookForm').reset();
    document.getElementById('book_id').value = 0;
    document.getElementById('copies_total').value = 1;
    document.getElementById('price').value = 0;
  });
</script>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>
