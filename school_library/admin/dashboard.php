<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/functions.php";
require_admin();

function get_count($conn, $sql) {
  $r = $conn->query($sql);
  $row = $r ? $r->fetch_assoc() : null;
  return (int)($row['c'] ?? 0);
}
function get_sum($conn, $sql) {
  $r = $conn->query($sql);
  $row = $r ? $r->fetch_assoc() : null;
  return (float)($row['s'] ?? 0);
}

/* LOANS basic stats (if you already have loans system) */
$totalLoans = get_count($conn, "SELECT COUNT(*) c FROM loans");
$overdueLoans = get_count($conn, "SELECT COUNT(*) c FROM loans WHERE status='overdue'");

/* NEW: Orders stats */
$totalOrders = get_count($conn, "SELECT COUNT(*) c FROM book_orders");
$pendingOrders = get_count($conn, "SELECT COUNT(*) c FROM book_orders WHERE status='pending'");

/* NEW: Purchases stats */
$totalPurchases = get_count($conn, "SELECT COUNT(*) c FROM book_purchases");
$totalSales = get_sum($conn, "SELECT COALESCE(SUM(total_amount),0) s FROM book_purchases");

/* Recent orders */
$recentOrders = $conn->query("
  SELECT o.*, u.full_name, u.email, b.title, b.author
  FROM book_orders o
  JOIN users u ON u.id=o.user_id
  JOIN books b ON b.id=o.book_id
  ORDER BY o.requested_at DESC
  LIMIT 5
");

/* Recent purchases */
$recentPurchases = $conn->query("
  SELECT p.*, u.full_name, u.email, b.title, b.author
  FROM book_purchases p
  JOIN users u ON u.id=p.user_id
  JOIN books b ON b.id=p.book_id
  ORDER BY p.purchased_at DESC
  LIMIT 5
");

require_once __DIR__ . "/../includes/header.php";
?>

<style>
  :root{
    --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --success-gradient: linear-gradient(135deg, #0ba360 0%, #3cba92 100%);
    --warning-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    --info-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
  }

  .admin-wrap{
    min-height: calc(100vh - 80px);
    background: linear-gradient(135deg,#f8fafc,#f1f5f9);
    padding: 20px;
  }
  .hero{
    background: rgba(255,255,255,.92);
    border: 1px solid rgba(255,255,255,.35);
    backdrop-filter: blur(18px);
    border-radius: 24px;
    padding: 26px;
    box-shadow: 0 20px 60px rgba(0,0,0,.08);
    position: relative;
    overflow: hidden;
  }
  .hero:before{
    content:'';
    position:absolute; inset:0 0 auto 0;
    height:6px;
    background: var(--primary-gradient);
  }

  .stats{
    display:grid;
    grid-template-columns: repeat(auto-fit,minmax(230px,1fr));
    gap:16px;
    margin-top: 18px;
  }
  .stat{
    background:#fff;
    border-radius: 20px;
    padding: 20px;
    border:1px solid rgba(0,0,0,.06);
    box-shadow: 0 12px 32px rgba(0,0,0,.06);
    position:relative;
    overflow:hidden;
    transition:.25s;
  }
  .stat:hover{ transform: translateY(-6px); box-shadow: 0 22px 44px rgba(0,0,0,.10); }
  .stat:before{ content:''; position:absolute; top:0; left:0; width:100%; height:6px; }
  .s1:before{ background: var(--primary-gradient); }
  .s2:before{ background: var(--warning-gradient); }
  .s3:before{ background: var(--success-gradient); }
  .s4:before{ background: var(--info-gradient); }

  .icon{
    width:52px;height:52px;border-radius:16px;
    display:flex;align-items:center;justify-content:center;
    font-size:22px;
    margin-bottom: 12px;
  }
  .s1 .icon{ background: rgba(102,126,234,.12); color:#667eea; }
  .s2 .icon{ background: rgba(245,87,108,.12); color:#f5576c; }
  .s3 .icon{ background: rgba(11,163,96,.12); color:#0ba360; }
  .s4 .icon{ background: rgba(79,172,254,.12); color:#4facfe; }

  .num{ font-size: 34px; font-weight: 900; color:#2d3748; line-height:1; }
  .lbl{ font-size: 13px; font-weight: 800; color:#718096; text-transform: uppercase; letter-spacing:.5px; margin-top: 8px; }

  .grid2{ display:grid; grid-template-columns: 1fr 1fr; gap:16px; margin-top: 18px; }
  @media (max-width: 992px){ .grid2{ grid-template-columns: 1fr; } }

  .panel{
    background:#fff;
    border-radius: 24px;
    border:1px solid rgba(0,0,0,.06);
    box-shadow: 0 16px 40px rgba(0,0,0,.08);
    padding: 22px;
  }
  .rowitem{
    background:#f8fafc;
    border:1px solid transparent;
    border-radius: 16px;
    padding: 12px 14px;
    margin-bottom: 10px;
    display:flex;
    justify-content: space-between;
    gap: 12px;
    transition: .2s;
  }
  .rowitem:hover{ background:#fff; border-color:#e2e8f0; transform: translateX(4px); }
  .title{ font-weight: 900; color:#2d3748; }
  .meta{ font-size:12px; color:#718096; }
  .badge-soft{ padding: 6px 12px; border-radius:999px; font-weight:900; font-size:12px; white-space:nowrap; }
  .b-pending{ background: rgba(255,193,7,.18); color:#a16207; }
  .b-approved{ background: rgba(25,135,84,.14); color:#0f5132; }
  .b-rejected{ background: rgba(220,53,69,.14); color:#842029; }

  .quick{
    display:grid;
    grid-template-columns: repeat(auto-fit,minmax(220px,1fr));
    gap:12px;
    margin-top: 16px;
  }
  .qbtn{
    text-decoration:none;
    background:#fff;
    border:1px solid #e2e8f0;
    border-radius: 18px;
    padding: 16px;
    display:flex;
    gap: 12px;
    color:#2d3748;
    transition:.2s;
  }
  .qbtn:hover{ transform: translateY(-4px); box-shadow: 0 16px 32px rgba(0,0,0,.10); border-color:#667eea; color:#667eea; }
  .qicon{
    width:46px;height:46px;border-radius:14px;
    display:flex;align-items:center;justify-content:center;
    font-size:20px;
    background: rgba(102,126,234,.12);
    color:#667eea;
  }
</style>

<div class="admin-wrap">

  <div class="hero">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
      <div>
        <h2 class="fw-bold mb-1">Admin Dashboard</h2>
        <div class="text-muted">Manage loans, orders, and purchases.</div>
      </div>
      <div class="d-flex gap-2 flex-wrap">
        <a href="/school_library/admin/orders.php" class="btn btn-dark">📦 Manage Orders</a>
        <a href="/school_library/admin/purchases.php" class="btn btn-outline-dark">🛒 View Purchases</a>
      </div>
    </div>

    <div class="stats">
      <div class="stat s1">
        <div class="icon">📚</div>
        <div class="num"><?= (int)$totalLoans ?></div>
        <div class="lbl">Total Loans</div>
      </div>

      <div class="stat s2">
        <div class="icon">⚠️</div>
        <div class="num"><?= (int)$overdueLoans ?></div>
        <div class="lbl">Overdue Loans</div>
      </div>

      <div class="stat s3">
        <div class="icon">📦</div>
        <div class="num"><?= (int)$pendingOrders ?></div>
        <div class="lbl">Pending Orders</div>
        <div class="small text-muted mt-1">Total orders: <?= (int)$totalOrders ?></div>
      </div>

      <div class="stat s4">
        <div class="icon">🛒</div>
        <div class="num"><?= (int)$totalPurchases ?></div>
        <div class="lbl">Purchases</div>
        <div class="small text-muted mt-1">Sales: Rs. <?= number_format((float)$totalSales, 2) ?></div>
      </div>
    </div>

    <div class="quick">
      <a class="qbtn" href="/school_library/admin/orders.php"><div class="qicon">📦</div><div><div class="fw-bold">Orders</div><div class="small text-muted">Approve / Reject</div></div></a>
      <a class="qbtn" href="/school_library/admin/purchases.php"><div class="qicon">🛒</div><div><div class="fw-bold">Purchases</div><div class="small text-muted">All purchase history</div></div></a>
      <a class="qbtn" href="/school_library/admin/books.php"><div class="qicon">📘</div><div><div class="fw-bold">Books</div><div class="small text-muted">Manage catalog</div></div></a>
      <a class="qbtn" href="/school_library/admin/users.php"><div class="qicon">👥</div><div><div class="fw-bold">Users</div><div class="small text-muted">Manage members</div></div></a>
    </div>
  </div>

  <div class="grid2">
    <div class="panel">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
          <h5 class="fw-bold mb-0">📦 Recent Orders</h5>
          <div class="text-muted small">Latest 5</div>
        </div>
        <a class="btn btn-outline-dark rounded-pill" href="/school_library/admin/orders.php">View all</a>
      </div>

      <?php if($recentOrders && $recentOrders->num_rows>0): ?>
        <?php while($o=$recentOrders->fetch_assoc()): ?>
          <?php
            $s = $o['status'];
            $cls = $s==='approved'?'b-approved':($s==='rejected'?'b-rejected':'b-pending');
          ?>
          <div class="rowitem">
            <div>
              <div class="title"><?= e($o['title']) ?> <span class="text-muted">×<?= (int)$o['qty'] ?></span></div>
              <div class="meta"><?= e($o['full_name']) ?> (<?= e($o['email']) ?>) • <?= e($o['requested_at']) ?></div>
            </div>
            <span class="badge-soft <?= $cls ?>"><?= e($s) ?></span>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="text-muted">No orders yet.</div>
      <?php endif; ?>
    </div>

    <div class="panel">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
          <h5 class="fw-bold mb-0">🛒 Recent Purchases</h5>
          <div class="text-muted small">Latest 5</div>
        </div>
        <a class="btn btn-outline-dark rounded-pill" href="/school_library/admin/purchases.php">View all</a>
      </div>

      <?php if($recentPurchases && $recentPurchases->num_rows>0): ?>
        <?php while($p=$recentPurchases->fetch_assoc()): ?>
          <div class="rowitem">
            <div>
              <div class="title"><?= e($p['title']) ?> <span class="text-muted">×<?= (int)$p['qty'] ?></span></div>
              <div class="meta"><?= e($p['full_name']) ?> • Rs. <?= number_format((float)$p['total_amount'],2) ?> • <?= e($p['purchased_at']) ?></div>
            </div>
            <span class="badge-soft b-approved">paid</span>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="text-muted">No purchases yet.</div>
      <?php endif; ?>
    </div>
  </div>

</div>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>
