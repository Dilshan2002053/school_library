<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/functions.php";
require_admin();

/* Filters */
$q = trim($_GET['q'] ?? '');
$from = trim($_GET['from'] ?? '');
$to = trim($_GET['to'] ?? '');

/* Build query */
$sql = "
  SELECT p.*, u.full_name, u.email, b.title, b.author
  FROM book_purchases p
  JOIN users u ON u.id = p.user_id
  JOIN books b ON b.id = p.book_id
  WHERE 1=1
";
$params = [];
$types = "";

if ($q !== '') {
  $sql .= " AND (u.full_name LIKE ? OR u.email LIKE ? OR b.title LIKE ? OR b.author LIKE ?)";
  $like = "%$q%";
  $params[] = $like; $params[] = $like; $params[] = $like; $params[] = $like;
  $types .= "ssss";
}

if ($from !== '') {
  $sql .= " AND DATE(p.purchased_at) >= ?";
  $params[] = $from;
  $types .= "s";
}

if ($to !== '') {
  $sql .= " AND DATE(p.purchased_at) <= ?";
  $params[] = $to;
  $types .= "s";
}

$sql .= " ORDER BY p.purchased_at DESC";

/* Prepare */
$stmt = $conn->prepare($sql);
if ($types !== "") {
  $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$rows = $stmt->get_result();

/* Totals (based on filters too) */
$totalSql = "
  SELECT COALESCE(SUM(p.total_amount),0) totalSales,
         COALESCE(SUM(p.qty),0) totalQty,
         COUNT(*) totalRows
  FROM book_purchases p
  JOIN users u ON u.id = p.user_id
  JOIN books b ON b.id = p.book_id
  WHERE 1=1
";
$totalParams = [];
$totalTypes = "";

if ($q !== '') {
  $totalSql .= " AND (u.full_name LIKE ? OR u.email LIKE ? OR b.title LIKE ? OR b.author LIKE ?)";
  $like = "%$q%";
  $totalParams[] = $like; $totalParams[] = $like; $totalParams[] = $like; $totalParams[] = $like;
  $totalTypes .= "ssss";
}
if ($from !== '') { $totalSql .= " AND DATE(p.purchased_at) >= ?"; $totalParams[] = $from; $totalTypes .= "s"; }
if ($to !== '')   { $totalSql .= " AND DATE(p.purchased_at) <= ?"; $totalParams[] = $to; $totalTypes .= "s"; }

$tstmt = $conn->prepare($totalSql);
if ($totalTypes !== "") {
  $tstmt->bind_param($totalTypes, ...$totalParams);
}
$tstmt->execute();
$totals = $tstmt->get_result()->fetch_assoc();

require_once __DIR__ . "/../includes/header.php";
?>

<style>
  :root{
    --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --success-gradient: linear-gradient(135deg, #0ba360 0%, #3cba92 100%);
    --info-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
  }
  .wrap{
    min-height: calc(100vh - 80px);
    background: linear-gradient(135deg,#f8fafc,#f1f5f9);
    padding: 20px;
  }
  .topcard{
    background: rgba(255,255,255,.92);
    border: 1px solid rgba(255,255,255,.35);
    backdrop-filter: blur(18px);
    border-radius: 24px;
    padding: 22px;
    box-shadow: 0 20px 60px rgba(0,0,0,.08);
    position: relative;
    overflow: hidden;
    margin-bottom: 16px;
  }
  .topcard:before{
    content:'';
    position:absolute; top:0; left:0;
    width:100%; height:6px;
    background: var(--info-gradient);
  }
  .stats{
    display:grid;
    grid-template-columns: repeat(auto-fit,minmax(230px,1fr));
    gap:14px;
    margin-top: 14px;
  }
  .stat{
    background:#fff;
    border-radius: 18px;
    padding: 18px;
    border:1px solid rgba(0,0,0,.06);
    box-shadow: 0 12px 32px rgba(0,0,0,.06);
  }
  .num{ font-size: 30px; font-weight: 900; color:#2d3748; }
  .lbl{ font-size: 13px; font-weight: 800; color:#718096; text-transform: uppercase; letter-spacing:.5px; }

  .filtercard{
    background:#fff;
    border-radius: 20px;
    padding: 16px;
    border:1px solid rgba(0,0,0,.06);
    box-shadow: 0 12px 32px rgba(0,0,0,.06);
    margin-bottom: 16px;
  }
  .tablecard{
    background:#fff;
    border-radius: 24px;
    padding: 18px;
    border:1px solid rgba(0,0,0,.06);
    box-shadow: 0 16px 40px rgba(0,0,0,.08);
  }
  .pill{
    background: rgba(11,163,96,.12);
    color:#0f5132;
    padding: 6px 12px;
    border-radius: 999px;
    font-weight: 900;
    font-size: 12px;
    white-space: nowrap;
  }
</style>

<div class="wrap">

  <div class="topcard">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
      <div>
        <h4 class="fw-bold mb-1">🛒 Purchases (All Users)</h4>
        <div class="text-muted">Search, filter, and view purchase details.</div>
      </div>
      <div class="d-flex gap-2 flex-wrap">
        <a class="btn btn-outline-dark" href="/school_library/admin/dashboard.php">← Dashboard</a>
        <a class="btn btn-dark" href="/school_library/admin/orders.php">📦 Orders</a>
      </div>
    </div>

    <div class="stats">
      <div class="stat">
        <div class="num"><?= (int)($totals['totalRows'] ?? 0) ?></div>
        <div class="lbl">Total Purchases</div>
      </div>
      <div class="stat">
        <div class="num"><?= (int)($totals['totalQty'] ?? 0) ?></div>
        <div class="lbl">Total Books Sold</div>
      </div>
      <div class="stat">
        <div class="num">Rs. <?= number_format((float)($totals['totalSales'] ?? 0), 2) ?></div>
        <div class="lbl">Total Sales</div>
      </div>
    </div>
  </div>

  <div class="filtercard">
    <form class="row g-2 align-items-end" method="get">
      <div class="col-md-5">
        <label class="form-label fw-semibold">Search</label>
        <input class="form-control" name="q" placeholder="User name, email, book title, author..." value="<?= e($q) ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label fw-semibold">From</label>
        <input type="date" class="form-control" name="from" value="<?= e($from) ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label fw-semibold">To</label>
        <input type="date" class="form-control" name="to" value="<?= e($to) ?>">
      </div>
      <div class="col-md-1 d-grid">
        <button class="btn btn-primary">Go</button>
      </div>

      <?php if($q!=='' || $from!=='' || $to!==''): ?>
        <div class="col-12">
          <a class="btn btn-outline-dark btn-sm" href="/school_library/admin/purchases.php">Clear Filters</a>
        </div>
      <?php endif; ?>
    </form>
  </div>

  <div class="tablecard">
    <div class="table-responsive">
      <table class="table table-striped align-middle">
        <thead class="table-light">
          <tr>
            <th>User</th>
            <th>Book</th>
            <th>Qty</th>
            <th>Price</th>
            <th>Total</th>
            <th>Date</th>
          </tr>
        </thead>

        <tbody>
        <?php if($rows->num_rows==0): ?>
          <tr><td colspan="6" class="text-center text-muted py-4">No purchases found.</td></tr>
        <?php else: ?>
          <?php while($r=$rows->fetch_assoc()): ?>
            <tr>
              <td>
                <div class="fw-bold"><?= e($r['full_name']) ?></div>
                <div class="small text-muted"><?= e($r['email']) ?></div>
              </td>
              <td>
                <div class="fw-bold"><?= e($r['title']) ?></div>
                <div class="small text-muted"><?= e($r['author']) ?></div>
              </td>
              <td><?= (int)$r['qty'] ?></td>
              <td>Rs. <?= number_format((float)$r['price_each'],2) ?></td>
              <td><span class="pill">Rs. <?= number_format((float)$r['total_amount'],2) ?></span></td>
              <td><?= e($r['purchased_at']) ?></td>
            </tr>
          <?php endwhile; ?>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>
