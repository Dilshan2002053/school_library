<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/functions.php";
require_user();

$uid = (int) current_user()['id'];

/* Filters */
$status = trim($_GET['status'] ?? 'all');
$q = trim($_GET['q'] ?? '');

/* Build query */
$sql = "
  SELECT l.id, l.issue_date, l.due_date, l.return_date, l.status, l.fine,
         b.id AS book_id, b.title, b.author, b.isbn, b.shelf
  FROM loans l
  JOIN books b ON b.id = l.book_id
  WHERE l.user_id = ?
";
$params = [$uid];
$types = "i";

if ($status !== 'all' && in_array($status, ['issued','overdue','returned'], true)) {
  $sql .= " AND l.status = ?";
  $params[] = $status;
  $types .= "s";
}

if ($q !== '') {
  $sql .= " AND (b.title LIKE ? OR b.author LIKE ? OR b.isbn LIKE ?)";
  $like = "%$q%";
  $params[] = $like; $params[] = $like; $params[] = $like;
  $types .= "sss";
}

$sql .= " ORDER BY l.issue_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$rows = $stmt->get_result();

/* Stats */
$st = $conn->prepare("SELECT
  SUM(CASE WHEN status IN('issued','overdue') THEN 1 ELSE 0 END) AS active_count,
  SUM(CASE WHEN status='returned' THEN 1 ELSE 0 END) AS returned_count,
  SUM(CASE WHEN status='overdue' THEN 1 ELSE 0 END) AS overdue_count,
  COALESCE(SUM(CASE WHEN fine>0 THEN fine ELSE 0 END),0) AS fine_total
  FROM loans WHERE user_id=?");
$st->bind_param("i",$uid);
$st->execute();
$stats = $st->get_result()->fetch_assoc();

require_once __DIR__ . "/../includes/header.php";
?>

<style>
  :root{
    --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --success-gradient: linear-gradient(135deg, #0ba360 0%, #3cba92 100%);
    --warning-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    --info-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
  }

  .wrap{
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
    margin-bottom: 18px;
  }
  .hero:before{
    content:'';
    position:absolute; top:0; left:0;
    width:100%; height:6px;
    background: var(--primary-gradient);
  }

  .stats{
    display:grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 14px;
    margin-top: 14px;
  }
  .stat{
    background:#fff;
    border-radius: 20px;
    padding: 18px;
    border: 1px solid rgba(0,0,0,.06);
    box-shadow: 0 12px 32px rgba(0,0,0,.06);
    position: relative;
    overflow: hidden;
    transition: .25s;
  }
  .stat:hover{ transform: translateY(-6px); box-shadow: 0 22px 44px rgba(0,0,0,.10); }
  .stat:before{ content:''; position:absolute; top:0; left:0; width:100%; height:6px; }
  .s1:before{ background: var(--primary-gradient); }
  .s2:before{ background: var(--success-gradient); }
  .s3:before{ background: var(--warning-gradient); }
  .s4:before{ background: var(--info-gradient); }

  .icon{
    width:52px;height:52px;border-radius:16px;
    display:flex;align-items:center;justify-content:center;
    font-size:22px;margin-bottom: 10px;
  }
  .s1 .icon{ background: rgba(102,126,234,.12); color:#667eea; }
  .s2 .icon{ background: rgba(11,163,96,.12); color:#0ba360; }
  .s3 .icon{ background: rgba(245,87,108,.12); color:#f5576c; }
  .s4 .icon{ background: rgba(79,172,254,.12); color:#4facfe; }

  .num{ font-size: 34px; font-weight: 900; color:#2d3748; line-height:1; }
  .lbl{ font-size: 13px; font-weight: 900; color:#718096; text-transform: uppercase; letter-spacing:.5px; margin-top: 8px; }

  .filter{
    background:#fff;
    border-radius: 20px;
    padding: 16px;
    border: 1px solid rgba(0,0,0,.06);
    box-shadow: 0 12px 32px rgba(0,0,0,.06);
    margin-bottom: 18px;
  }

  .list-card{
    background:#fff;
    border-radius: 24px;
    padding: 18px;
    border: 1px solid rgba(0,0,0,.06);
    box-shadow: 0 16px 40px rgba(0,0,0,.08);
  }

  .loan{
    background:#f8fafc;
    border: 1px solid transparent;
    border-radius: 18px;
    padding: 14px;
    display:flex;
    gap: 14px;
    align-items: center;
    transition: .2s;
    margin-bottom: 12px;
  }
  .loan:hover{ background:#fff; border-color:#e2e8f0; transform: translateX(4px); }

  .cover{
    width: 56px;
    height: 74px;
    border-radius: 14px;
    color:#fff;
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight: 900;
    font-size: 22px;
    flex-shrink: 0;
  }

  .title{ font-weight: 900; color:#2d3748; font-size: 16px; }
  .meta{ color:#718096; font-size: 13px; }

  .pill{
    padding: 6px 12px;
    border-radius: 999px;
    font-weight: 900;
    font-size: 12px;
    white-space: nowrap;
  }
  .p-issued{ background: rgba(102,126,234,.12); color:#4c51bf; }
  .p-overdue{ background: rgba(245,87,108,.12); color:#b91c1c; }
  .p-returned{ background: rgba(11,163,96,.12); color:#0f5132; }

  .fine{
    background: rgba(255,193,7,.18);
    color:#a16207;
  }

  .btn-soft{
    border:1px solid #e2e8f0;
    background:#fff;
    border-radius: 14px;
    padding: 10px 14px;
    font-weight: 900;
    text-decoration:none;
    color:#2d3748;
    transition:.2s;
    display:inline-flex;
    gap:8px;
    align-items:center;
    justify-content:center;
  }
  .btn-soft:hover{ transform: translateY(-2px); box-shadow: 0 14px 28px rgba(0,0,0,.10); border-color:#667eea; color:#667eea; }

  .empty{
    text-align:center;
    padding: 60px 20px;
    color:#94a3b8;
  }
</style>

<div class="wrap">

  <div class="hero">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
      <div>
        <h3 class="fw-bold mb-1">📋 My Loans</h3>
        <div class="text-muted">See your issued, overdue, and returned books in one place.</div>
      </div>
      <div class="d-flex gap-2 flex-wrap">
        <a class="btn-soft" href="/school_library/user/dashboard.php">🏠 Dashboard</a>
        <a class="btn-soft" href="/school_library/user/catalog.php">🔍 Catalog</a>
      </div>
    </div>

    <div class="stats">
      <div class="stat s1">
        <div class="icon">📖</div>
        <div class="num"><?= (int)($stats['active_count'] ?? 0) ?></div>
        <div class="lbl">Active Loans</div>
      </div>
      <div class="stat s2">
        <div class="icon">✅</div>
        <div class="num"><?= (int)($stats['returned_count'] ?? 0) ?></div>
        <div class="lbl">Returned</div>
      </div>
      <div class="stat s3">
        <div class="icon">⚠️</div>
        <div class="num"><?= (int)($stats['overdue_count'] ?? 0) ?></div>
        <div class="lbl">Overdue</div>
      </div>
      <div class="stat s4">
        <div class="icon">💰</div>
        <div class="num">Rs. <?= number_format((float)($stats['fine_total'] ?? 0),2) ?></div>
        <div class="lbl">Total Fines</div>
      </div>
    </div>
  </div>

  <div class="filter">
    <form class="row g-2 align-items-end" method="get">
      <div class="col-md-6">
        <label class="form-label fw-semibold">Search</label>
        <input class="form-control" name="q" placeholder="Search title / author / ISBN..." value="<?= e($q) ?>">
      </div>

      <div class="col-md-3">
        <label class="form-label fw-semibold">Status</label>
        <select class="form-select" name="status">
          <option value="all" <?= $status==='all'?'selected':'' ?>>All</option>
          <option value="issued" <?= $status==='issued'?'selected':'' ?>>Issued</option>
          <option value="overdue" <?= $status==='overdue'?'selected':'' ?>>Overdue</option>
          <option value="returned" <?= $status==='returned'?'selected':'' ?>>Returned</option>
        </select>
      </div>

      <div class="col-md-3 d-grid">
        <button class="btn btn-primary">Apply</button>
      </div>

      <?php if($q!=='' || ($status!=='all' && $status!=='')): ?>
        <div class="col-12">
          <a class="btn btn-outline-dark btn-sm" href="/school_library/user/my_loans.php">Clear</a>
        </div>
      <?php endif; ?>
    </form>
  </div>

  <div class="list-card">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="fw-bold mb-0">📚 Loan List</h5>
      <span class="text-muted small">Sorted by latest issued</span>
    </div>

    <?php if($rows->num_rows > 0): ?>
      <?php
        $colors = [
          'linear-gradient(135deg, #667eea, #764ba2)',
          'linear-gradient(135deg, #4facfe, #00f2fe)',
          'linear-gradient(135deg, #f093fb, #f5576c)',
          'linear-gradient(135deg, #0ba360, #3cba92)',
          'linear-gradient(135deg, #a855f7, #ec4899)'
        ];
        $i = 0;
      ?>

      <?php while($r = $rows->fetch_assoc()): ?>
        <?php
          $i++;
          $bg = $colors[$i % count($colors)];
          $st = $r['status'] ?? '';
          $pill = $st==='issued' ? 'p-issued' : ($st==='overdue' ? 'p-overdue' : 'p-returned');

          $issue = $r['issue_date'] ? date('M d, Y', strtotime($r['issue_date'])) : '-';
          $due = $r['due_date'] ? date('M d, Y', strtotime($r['due_date'])) : '-';
          $ret = $r['return_date'] ? date('M d, Y', strtotime($r['return_date'])) : null;

          $fine = (float)($r['fine'] ?? 0);
        ?>

        <div class="loan">
          <div class="cover" style="background: <?= $bg ?>;">
            <?= strtoupper(substr($r['title'],0,1)) ?>
          </div>

          <div class="flex-grow-1">
            <div class="title"><?= e($r['title']) ?></div>
            <div class="meta">✍️ <?= e($r['author']) ?> • ISBN: <?= e($r['isbn']) ?> • Shelf: <?= e($r['shelf'] ?? '-') ?></div>

            <div class="meta mt-2">
              <b>Issued:</b> <?= e($issue) ?> &nbsp; | &nbsp;
              <?php if($ret): ?>
                <b>Returned:</b> <?= e($ret) ?>
              <?php else: ?>
                <b>Due:</b> <?= e($due) ?>
              <?php endif; ?>
            </div>
          </div>

          <div class="d-flex flex-column align-items-end gap-2">
            <span class="pill <?= $pill ?>"><?= e(ucfirst($st)) ?></span>

            <?php if($fine > 0): ?>
              <span class="pill fine">Fine: Rs. <?= number_format($fine,2) ?></span>
            <?php endif; ?>

            <a class="btn-soft" href="/school_library/user/book_details.php?id=<?= (int)$r['book_id'] ?>">👁️ View Book</a>
          </div>
        </div>
      <?php endwhile; ?>

    <?php else: ?>
      <div class="empty">
        <div style="font-size:60px;opacity:.25;">📚</div>
        <h5 class="fw-bold mt-2">No loans found</h5>
        <div class="text-muted">Try changing filters, or browse the catalog.</div>
        <a class="btn btn-primary rounded-pill mt-3 px-4" href="/school_library/user/catalog.php">Browse Catalog →</a>
      </div>
    <?php endif; ?>
  </div>

</div>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>
