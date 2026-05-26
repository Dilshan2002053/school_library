<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/functions.php";
require_admin();

/* Change role */
if (isset($_POST['role_user_id'])) {
  $uid = (int)$_POST['role_user_id'];
  $role = ($_POST['role'] ?? 'user') === 'admin' ? 'admin' : 'user';

  // prevent admin deleting/changing self? (optional)
  $st = $conn->prepare("UPDATE users SET role=? WHERE id=?");
  $st->bind_param("si",$role,$uid);
  $st->execute();
  set_flash('success','Role updated ✅');
  header("Location: /school_library/admin/users.php");
  exit;
}

/* Delete user */
if (isset($_GET['delete'])) {
  $uid = (int)$_GET['delete'];
  if ($uid > 0) {
    $st = $conn->prepare("DELETE FROM users WHERE id=?");
    $st->bind_param("i",$uid);
    $st->execute();
    set_flash('success','User deleted ✅');
  }
  header("Location: /school_library/admin/users.php");
  exit;
}

$q = trim($_GET['q'] ?? '');
if ($q !== '') {
  $like = "%$q%";
  $st = $conn->prepare("SELECT id,full_name,email,role,created_at FROM users WHERE full_name LIKE ? OR email LIKE ? ORDER BY id DESC");
  $st->bind_param("ss",$like,$like);
  $st->execute();
  $users = $st->get_result();
} else {
  $users = $conn->query("SELECT id,full_name,email,role,created_at FROM users ORDER BY id DESC");
}

require_once __DIR__ . "/../includes/header.php";
?>

<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h4 class="fw-bold mb-0">👥 Users</h4>
    <a class="btn btn-outline-dark" href="/school_library/admin/dashboard.php">← Dashboard</a>
  </div>

  <?php if (has_flash()): ?>
    <div class="alert alert-<?= e(get_flash_type()) ?> mt-3"><?= e(get_flash_message()) ?></div>
  <?php endif; ?>

  <div class="card p-3 mt-3">
    <form class="d-flex gap-2" method="get">
      <input class="form-control" name="q" placeholder="Search name/email" value="<?= e($q) ?>">
      <button class="btn btn-dark">Search</button>
      <?php if($q!==''): ?><a class="btn btn-outline-dark" href="/school_library/admin/users.php">Clear</a><?php endif; ?>
    </form>
  </div>

  <div class="card p-4 mt-3">
    <div class="table-responsive">
      <table class="table table-striped align-middle">
        <thead>
          <tr><th>User</th><th>Role</th><th>Created</th><th class="text-end">Action</th></tr>
        </thead>
        <tbody>
        <?php while($u=$users->fetch_assoc()): ?>
          <tr>
            <td>
              <div class="fw-bold"><?= e($u['full_name']) ?></div>
              <div class="small text-muted"><?= e($u['email']) ?></div>
            </td>
            <td>
              <form method="post" class="d-flex gap-2 align-items-center">
                <input type="hidden" name="role_user_id" value="<?= (int)$u['id'] ?>">
                <select class="form-select form-select-sm" name="role" style="max-width:150px">
                  <option value="user" <?= $u['role']==='user'?'selected':'' ?>>user</option>
                  <option value="admin" <?= $u['role']==='admin'?'selected':'' ?>>admin</option>
                </select>
                <button class="btn btn-sm btn-primary">Update</button>
              </form>
            </td>
            <td class="small text-muted"><?= e($u['created_at'] ?? '-') ?></td>
            <td class="text-end">
              <a class="btn btn-sm btn-danger"
                 href="/school_library/admin/users.php?delete=<?= (int)$u['id'] ?>"
                 onclick="return confirm('Delete this user?')">Delete</a>
            </td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>
