<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/functions.php";

require_user();
require_once __DIR__ . "/../includes/header.php";

$uid = (int) current_user()['id'];

/* -----------------------
   LOANS (Borrow system)
------------------------*/
$myIssued = $conn->prepare("SELECT COUNT(*) c FROM loans WHERE user_id=? AND status IN('issued','overdue')");
$myIssued->bind_param("i",$uid);
$myIssued->execute();
$issued = (int)($myIssued->get_result()->fetch_assoc()['c'] ?? 0);

$myReturned = $conn->prepare("SELECT COUNT(*) c FROM loans WHERE user_id=? AND status='returned'");
$myReturned->bind_param("i",$uid);
$myReturned->execute();
$returned = (int)($myReturned->get_result()->fetch_assoc()['c'] ?? 0);

$overdue = $conn->prepare("SELECT COUNT(*) c FROM loans WHERE user_id=? AND status='overdue'");
$overdue->bind_param("i",$uid);
$overdue->execute();
$overdueCount = (int)($overdue->get_result()->fetch_assoc()['c'] ?? 0);

$recentLoans = $conn->prepare("
    SELECT l.id, l.issue_date, l.due_date, l.return_date, l.status, l.fine,
           b.title, b.author
    FROM loans l
    JOIN books b ON l.book_id = b.id
    WHERE l.user_id = ?
    ORDER BY l.issue_date DESC
    LIMIT 5
");
$recentLoans->bind_param("i",$uid);
$recentLoans->execute();
$recent = $recentLoans->get_result();

$fines = $conn->prepare("SELECT COALESCE(SUM(fine),0) total FROM loans WHERE user_id=? AND fine > 0");
$fines->bind_param("i",$uid);
$fines->execute();
$totalFines = (float)($fines->get_result()->fetch_assoc()['total'] ?? 0);

/* -----------------------
   NEW: SAVED BOOKS
------------------------*/
$savedStmt = $conn->prepare("SELECT COUNT(*) c FROM user_saved_books WHERE user_id=?");
$savedStmt->bind_param("i",$uid);
$savedStmt->execute();
$savedCount = (int)($savedStmt->get_result()->fetch_assoc()['c'] ?? 0);

/* -----------------------
   NEW: ORDERS
------------------------*/
$pendingOrdersStmt = $conn->prepare("SELECT COUNT(*) c FROM book_orders WHERE user_id=? AND status='pending'");
$pendingOrdersStmt->bind_param("i",$uid);
$pendingOrdersStmt->execute();
$pendingOrders = (int)($pendingOrdersStmt->get_result()->fetch_assoc()['c'] ?? 0);

$recentOrdersStmt = $conn->prepare("
  SELECT o.id, o.qty, o.status, o.requested_at, b.title, b.author
  FROM book_orders o
  JOIN books b ON b.id=o.book_id
  WHERE o.user_id=?
  ORDER BY o.requested_at DESC
  LIMIT 5
");
$recentOrdersStmt->bind_param("i",$uid);
$recentOrdersStmt->execute();
$recentOrders = $recentOrdersStmt->get_result();

/* -----------------------
   NEW: PURCHASES
------------------------*/
$purchasesCountStmt = $conn->prepare("SELECT COUNT(*) c FROM book_purchases WHERE user_id=?");
$purchasesCountStmt->bind_param("i",$uid);
$purchasesCountStmt->execute();
$totalPurchases = (int)($purchasesCountStmt->get_result()->fetch_assoc()['c'] ?? 0);

$purchasesSumStmt = $conn->prepare("SELECT COALESCE(SUM(total_amount),0) total FROM book_purchases WHERE user_id=?");
$purchasesSumStmt->bind_param("i",$uid);
$purchasesSumStmt->execute();
$totalSpent = (float)($purchasesSumStmt->get_result()->fetch_assoc()['total'] ?? 0);

$recentPurchStmt = $conn->prepare("
  SELECT p.id, p.qty, p.price_each, p.total_amount, p.purchased_at, b.title, b.author
  FROM book_purchases p
  JOIN books b ON b.id=p.book_id
  WHERE p.user_id=?
  ORDER BY p.purchased_at DESC
  LIMIT 5
");
$recentPurchStmt->bind_param("i",$uid);
$recentPurchStmt->execute();
$recentPurchases = $recentPurchStmt->get_result();
?>

<style>
  :root {
    --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --success-gradient: linear-gradient(135deg, #0ba360 0%, #3cba92 100%);
    --warning-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    --info-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    --purple-gradient: linear-gradient(135deg, #a855f7 0%, #ec4899 100%);
    --dark-gradient: linear-gradient(135deg, #111827 0%, #334155 100%);
  }

  .dashboard-wrapper {
    min-height: calc(100vh - 80px);
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    padding: 20px;
  }

  .welcome-card {
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(255, 255, 255, 0.8));
    backdrop-filter: blur(20px);
    border-radius: 24px;
    border: 1px solid rgba(255, 255, 255, 0.3);
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.08);
    padding: 30px;
    margin-bottom: 30px;
    position: relative;
    overflow: hidden;
  }
  .welcome-card::before {
    content: '';
    position: absolute; top:0; left:0;
    width: 100%; height: 6px;
    background: var(--primary-gradient);
  }

  .stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
  }

  .stat-card {
    background: white;
    border-radius: 20px;
    padding: 22px;
    border: 1px solid rgba(0, 0, 0, 0.06);
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.06);
    transition: all 0.25s ease;
    position: relative;
    overflow: hidden;
  }
  .stat-card:hover { transform: translateY(-6px); box-shadow: 0 22px 45px rgba(0,0,0,.12); }
  .stat-card::before {
    content:'';
    position:absolute; top:0; left:0;
    width:100%; height:6px;
  }

  .stat-icon {
    width: 56px; height: 56px;
    border-radius: 16px;
    display:flex; align-items:center; justify-content:center;
    font-size: 26px;
    margin-bottom: 14px;
  }

  .stat-number {
    font-size: 34px;
    font-weight: 800;
    line-height: 1.1;
    margin: 6px 0;
    color: #111827;
  }
  .stat-label { color:#64748b; font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: .6px; }
  .stat-sub { color:#94a3b8; font-size: 13px; }

  .issued::before{ background: var(--primary-gradient); }
  .returned::before{ background: var(--success-gradient); }
  .overdue::before{ background: var(--warning-gradient); }
  .fines::before{ background: var(--info-gradient); }
  .orders::before{ background: var(--purple-gradient); }
  .purchases::before{ background: var(--dark-gradient); }
  .saved::before{ background: var(--info-gradient); }

  .issued .stat-icon { background: rgba(102,126,234,.12); color:#667eea; }
  .returned .stat-icon{ background: rgba(11,163,96,.12); color:#0ba360; }
  .overdue .stat-icon{ background: rgba(245,87,108,.12); color:#f5576c; }
  .fines .stat-icon{ background: rgba(79,172,254,.12); color:#4facfe; }
  .orders .stat-icon{ background: rgba(168,85,247,.12); color:#a855f7; }
  .purchases .stat-icon{ background: rgba(17,24,39,.08); color:#111827; }
  .saved .stat-icon{ background: rgba(79,172,254,.12); color:#4facfe; }

  .card-box {
    background: white;
    border-radius: 24px;
    border: 1px solid rgba(0, 0, 0, 0.06);
    box-shadow: 0 16px 40px rgba(0, 0, 0, 0.08);
    padding: 26px;
    margin-top: 20px;
  }

  .item {
    display:flex; align-items:center; gap: 16px;
    padding: 16px;
    border-radius: 16px;
    background: #f8fafc;
    margin-bottom: 12px;
    border: 1px solid transparent;
    transition: .2s ease;
  }
  .item:hover{ background:#fff; border-color:#e2e8f0; transform: translateX(4px); }

  .pill { padding: 6px 12px; border-radius: 999px; font-size: 12px; font-weight: 700; }
  .pill-pending { background: rgba(234,179,8,.15); color: #a16207; }
  .pill-approved { background: rgba(34,197,94,.12); color: #15803d; }
  .pill-rejected { background: rgba(239,68,68,.12); color: #b91c1c; }
  .pill-issued { background: rgba(102,126,234,.12); color: #4f46e5; }
  .pill-overdue { background: rgba(245,87,108,.12); color: #e11d48; }
  .pill-returned { background: rgba(11,163,96,.12); color: #15803d; }

  .quick-actions{
    display:grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 18px;
    margin-top: 28px;
  }
  .action-btn{
    display:flex; gap: 14px; align-items:center;
    padding: 18px;
    border-radius: 20px;
    background:#fff;
    border: 1px solid #e2e8f0;
    text-decoration:none;
    color:#111827;
    transition: .2s ease;
    position:relative;
    overflow:hidden;
  }
  .action-btn:hover{
    transform: translateY(-5px);
    box-shadow: 0 16px 32px rgba(0,0,0,.1);
    border-color: #667eea;
    color: #667eea;
  }
  .action-icon{
    width: 48px; height: 48px;
    border-radius: 14px;
    display:flex; align-items:center; justify-content:center;
    font-size: 22px;
    background: rgba(102,126,234,.12);
    color:#667eea;
  }
</style>

<div class="dashboard-wrapper">
  <div class="welcome-card">
    <div class="row align-items-center">
      <div class="col-md-8">
        <h1 class="fw-bold display-6 mb-2">
          Welcome back, <span class="text-primary"><?php echo e(current_user()['full_name']); ?>!</span>
        </h1>
        <p class="text-muted mb-4">Borrow, order, purchase and track your library activity.</p>
        <div class="d-flex gap-2 flex-wrap">
          <span class="badge bg-primary rounded-pill px-3 py-2">📚 Library Member</span>
          <span class="badge bg-light text-dark border rounded-pill px-3 py-2">📧 <?php echo e(current_user()['email']); ?></span>
        </div>
      </div>
      <div class="col-md-4 text-end">
        <div style="width: 100px; height: 100px; background: var(--primary-gradient); color: white; font-size: 48px; border-radius: 24px; display: inline-flex; align-items: center; justify-content: center;">
          👤
        </div>
      </div>
    </div>
  </div>

  <!-- Stats -->
  <div class="stats-grid">
    <div class="stat-card issued">
      <div class="stat-icon">📖</div>
      <div class="stat-number"><?php echo (int)$issued; ?></div>
      <div class="stat-label">Current Loans</div>
      <div class="stat-sub">Borrowed now</div>
    </div>

    <div class="stat-card returned">
      <div class="stat-icon">✅</div>
      <div class="stat-number"><?php echo (int)$returned; ?></div>
      <div class="stat-label">Returned</div>
      <div class="stat-sub">Completed loans</div>
    </div>

    <div class="stat-card overdue">
      <div class="stat-icon">⚠️</div>
      <div class="stat-number"><?php echo (int)$overdueCount; ?></div>
      <div class="stat-label">Overdue</div>
      <div class="stat-sub">Need action</div>
    </div>

    <div class="stat-card fines">
      <div class="stat-icon">💰</div>
      <div class="stat-number">Rs. <?php echo number_format($totalFines, 2); ?></div>
      <div class="stat-label">Fines</div>
      <div class="stat-sub">Total fines</div>
    </div>

    <div class="stat-card orders">
      <div class="stat-icon">📦</div>
      <div class="stat-number"><?php echo (int)$pendingOrders; ?></div>
      <div class="stat-label">Pending Orders</div>
      <div class="stat-sub">Waiting approval</div>
    </div>

    <div class="stat-card purchases">
      <div class="stat-icon">🛒</div>
      <div class="stat-number"><?php echo (int)$totalPurchases; ?></div>
      <div class="stat-label">Purchases</div>
      <div class="stat-sub">Total bought</div>
    </div>

    <div class="stat-card purchases">
      <div class="stat-icon">💳</div>
      <div class="stat-number">Rs. <?php echo number_format($totalSpent, 2); ?></div>
      <div class="stat-label">Total Spent</div>
      <div class="stat-sub">Purchase amount</div>
    </div>

    <div class="stat-card saved">
      <div class="stat-icon">⭐</div>
      <div class="stat-number"><?php echo (int)$savedCount; ?></div>
      <div class="stat-label">Saved Books</div>
      <div class="stat-sub">Your favorites</div>
    </div>
  </div>

  <!-- Recent Loans -->
  <div class="card-box">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div>
        <h4 class="fw-bold mb-1">Recent Loans</h4>
        <div class="text-muted">Latest borrowed books</div>
      </div>
      <a href="/school_library/user/my_loans.php" class="btn btn-outline-primary rounded-pill px-4">View All →</a>
    </div>

    <?php if ($recent->num_rows > 0): ?>
      <?php while($loan = $recent->fetch_assoc()): ?>
        <?php
          $pill = $loan['status']==='issued' ? 'pill-issued' : ($loan['status']==='overdue' ? 'pill-overdue' : 'pill-returned');
        ?>
        <div class="item">
          <div style="width:46px;height:46px;border-radius:14px;background:rgba(102,126,234,.12);display:flex;align-items:center;justify-content:center;font-size:22px;">📘</div>
          <div style="flex:1;">
            <div class="fw-bold"><?php echo e($loan['title']); ?></div>
            <div class="small text-muted"><?php echo e($loan['author']); ?> • Issued: <?php echo e($loan['issue_date']); ?> • Due: <?php echo e($loan['due_date']); ?></div>
          </div>
          <div class="pill <?php echo $pill; ?>"><?php echo e($loan['status']); ?></div>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <div class="text-center text-muted py-4">No loans yet.</div>
    <?php endif; ?>
  </div>

  <!-- Recent Orders -->
  <div class="card-box">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div>
        <h4 class="fw-bold mb-1">Recent Orders</h4>
        <div class="text-muted">Your latest order requests</div>
      </div>
      <a href="/school_library/user/my_orders.php" class="btn btn-outline-primary rounded-pill px-4">View All →</a>
    </div>

    <?php if ($recentOrders->num_rows > 0): ?>
      <?php while($o = $recentOrders->fetch_assoc()): ?>
        <?php
          $s = $o['status'];
          $pill = $s==='pending' ? 'pill-pending' : ($s==='approved' ? 'pill-approved' : 'pill-rejected');
        ?>
        <div class="item">
          <div style="width:46px;height:46px;border-radius:14px;background:rgba(168,85,247,.12);display:flex;align-items:center;justify-content:center;font-size:22px;">📦</div>
          <div style="flex:1;">
            <div class="fw-bold"><?php echo e($o['title']); ?></div>
            <div class="small text-muted"><?php echo e($o['author']); ?> • Qty: <?php echo (int)$o['qty']; ?> • <?php echo e($o['requested_at']); ?></div>
          </div>
          <div class="pill <?php echo $pill; ?>"><?php echo e($s); ?></div>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <div class="text-center text-muted py-4">No orders yet.</div>
    <?php endif; ?>
  </div>

  <!-- Recent Purchases -->
  <div class="card-box">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div>
        <h4 class="fw-bold mb-1">Recent Purchases</h4>
        <div class="text-muted">Your latest bought books</div>
      </div>
      <a href="/school_library/user/purchase_history.php" class="btn btn-outline-primary rounded-pill px-4">View All →</a>
    </div>

    <?php if ($recentPurchases->num_rows > 0): ?>
      <?php while($p = $recentPurchases->fetch_assoc()): ?>
        <div class="item">
          <div style="width:46px;height:46px;border-radius:14px;background:rgba(17,24,39,.08);display:flex;align-items:center;justify-content:center;font-size:22px;">🛒</div>
          <div style="flex:1;">
            <div class="fw-bold"><?php echo e($p['title']); ?></div>
            <div class="small text-muted">
              <?php echo e($p['author']); ?> • Qty: <?php echo (int)$p['qty']; ?> • Total: Rs. <?php echo number_format((float)$p['total_amount'],2); ?>
              • <?php echo e($p['purchased_at']); ?>
            </div>
          </div>
          <div class="pill pill-approved">paid</div>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <div class="text-center text-muted py-4">No purchases yet.</div>
    <?php endif; ?>
  </div>

  <!-- Quick Actions -->
  <div class="quick-actions">
    <a href="/school_library/user/catalog.php" class="action-btn">
      <div class="action-icon">🔍</div>
      <div>
        <div class="fw-bold">Browse Catalog</div>
        <div class="small text-muted">Find books</div>
      </div>
    </a>

    <a href="/school_library/user/saved_books.php" class="action-btn">
      <div class="action-icon">⭐</div>
      <div>
        <div class="fw-bold">Saved Books</div>
        <div class="small text-muted">Your favorites</div>
      </div>
    </a>

    <a href="/school_library/user/my_orders.php" class="action-btn">
      <div class="action-icon">📦</div>
      <div>
        <div class="fw-bold">My Orders</div>
        <div class="small text-muted">Order history</div>
      </div>
    </a>

    <a href="/school_library/user/purchase_history.php" class="action-btn">
      <div class="action-icon">🛒</div>
      <div>
        <div class="fw-bold">Purchase History</div>
        <div class="small text-muted">Bought books</div>
      </div>
    </a>
  </div>
</div>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>
