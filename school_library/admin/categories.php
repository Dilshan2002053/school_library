<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/functions.php";
require_admin();

/* Delete */
if (isset($_GET['delete'])) {
  $id = (int)$_GET['delete'];
  if ($id > 0) {
    $st = $conn->prepare("DELETE FROM categories WHERE id=?");
    $st->bind_param("i",$id);
    $st->execute();
    set_flash('success','Category deleted ✅');
  }
  header("Location: /school_library/admin/categories.php");
  exit;
}

/* Add / Update */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = (int)($_POST['id'] ?? 0);
  $name = trim($_POST['name'] ?? '');

  if ($name === '') {
    set_flash('danger','Category name required');
    header("Location: /school_library/admin/categories.php");
    exit;
  }

  if ($id > 0) {
    $st = $conn->prepare("UPDATE categories SET name=? WHERE id=?");
    $st->bind_param("si",$name,$id);
    $st->execute();
    set_flash('success','Category updated ✅');
  } else {
    $st = $conn->prepare("INSERT INTO categories(name) VALUES(?)");
    $st->bind_param("s",$name);
    $st->execute();
    set_flash('success','Category added ✅');
  }

  header("Location: /school_library/admin/categories.php");
  exit;
}

$cats = $conn->query("SELECT * FROM categories ORDER BY name ASC");

require_once __DIR__ . "/../includes/header.php";
?>

<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h4 class="fw-bold mb-0">🏷️ Categories</h4>
    <div class="d-flex gap-2 flex-wrap">
      <a class="btn btn-outline-dark" href="/school_library/admin/dashboard.php">← Dashboard</a>
      <a class="btn btn-outline-dark" href="/school_library/admin/books.php">📘 Books</a>
    </div>
  </div>

  <?php if (has_flash()): ?>
    <div class="alert alert-<?= e(get_flash_type()) ?> mt-3"><?= e(get_flash_message()) ?></div>
  <?php endif; ?>

  <div class="card p-4 mt-3">
    <h6 class="fw-bold mb-3">Add / Edit Category</h6>
    <form method="post" class="row g-2" id="catForm">
      <input type="hidden" name="id" id="cat_id" value="0">
      <div class="col-md-8">
        <input class="form-control" name="name" id="cat_name" placeholder="Category name" required>
      </div>
      <div class="col-md-4 d-flex gap-2">
        <button class="btn btn-dark w-100">Save</button>
        <button class="btn btn-outline-dark w-100" type="button" id="resetBtn">Reset</button>
      </div>
    </form>
  </div>

  <div class="card p-4 mt-3">
    <h6 class="fw-bold mb-3">All Categories</h6>
    <div class="table-responsive">
      <table class="table table-striped align-middle">
        <thead><tr><th>Name</th><th class="text-end">Action</th></tr></thead>
        <tbody>
        <?php while($c=$cats->fetch_assoc()): ?>
          <tr>
            <td class="fw-bold"><?= e($c['name']) ?></td>
            <td class="text-end">
              <button class="btn btn-sm btn-primary" onclick='editCat(<?= json_encode($c) ?>)'>Edit</button>
              <a class="btn btn-sm btn-danger"
                 href="/school_library/admin/categories.php?delete=<?= (int)$c['id'] ?>"
                 onclick="return confirm('Delete this category?')">Delete</a>
            </td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
function editCat(c){
  document.getElementById('cat_id').value = c.id;
  document.getElementById('cat_name').value = c.name;
  window.scrollTo({top:0,behavior:'smooth'});
}
document.getElementById('resetBtn').onclick = () => {
  document.getElementById('cat_id').value = 0;
  document.getElementById('cat_name').value = '';
};
</script>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>
