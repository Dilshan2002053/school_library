<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/functions.php";
require_user();

$uid = (int) current_user()['id'];
$q = trim($_GET['q'] ?? '');

if ($q !== '') {
    $like = "%$q%";
    $stmt = $conn->prepare("
        SELECT b.*, c.name AS category
        FROM books b
        LEFT JOIN categories c ON c.id = b.category_id
        WHERE b.title LIKE ? OR b.author LIKE ? OR b.isbn LIKE ?
        ORDER BY b.title ASC
    ");
    $stmt->bind_param("sss", $like, $like, $like);
    $stmt->execute();
    $books = $stmt->get_result();
} else {
    $books = $conn->query("
        SELECT b.*, c.name AS category
        FROM books b
        LEFT JOIN categories c ON c.id = b.category_id
        ORDER BY b.title ASC
    ");
}

require_once __DIR__ . "/../includes/header.php";
?>

<style>
  :root {
    --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --success-gradient: linear-gradient(135deg, #0ba360 0%, #3cba92 100%);
    --warning-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    --info-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
  }

  .catalog-wrapper {
    min-height: calc(100vh - 80px);
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    padding: 20px;
  }

  .header-card {
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(255, 255, 255, 0.8));
    backdrop-filter: blur(20px);
    border-radius: 24px;
    border: 1px solid rgba(255, 255, 255, 0.3);
    box-shadow:
      0 20px 60px rgba(0, 0, 0, 0.08),
      0 8px 32px rgba(0, 0, 0, 0.04),
      inset 0 1px 0 rgba(255, 255, 255, 0.6);
    padding: 30px;
    margin-bottom: 30px;
    position: relative;
    overflow: hidden;
  }

  .header-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0;
    width: 100%;
    height: 6px;
    background: var(--primary-gradient);
  }

  .top-actions {
    display:flex;
    gap:12px;
    flex-wrap:wrap;
    justify-content:flex-end;
  }

  .chip-btn {
    border:none;
    padding: 12px 18px;
    border-radius: 14px;
    font-weight:700;
    text-decoration:none;
    display:inline-flex;
    align-items:center;
    gap:8px;
    transition:.2s;
    box-shadow: 0 8px 24px rgba(0,0,0,.08);
  }
  .chip-btn:hover { transform: translateY(-2px); }

  .chip-orders { background: rgba(102,126,234,.12); color:#4c51bf; }
  .chip-purchases { background: rgba(11,163,96,.12); color:#0f5132; }
  .chip-saved { background: rgba(240,147,251,.14); color:#842029; }

  .search-card {
    background: white;
    border-radius: 20px;
    padding: 25px;
    border: 1px solid rgba(0, 0, 0, 0.06);
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.06);
    margin-bottom: 25px;
  }

  .search-form {
    display: flex;
    gap: 15px;
    align-items: center;
  }

  .search-input {
    flex: 1;
    padding: 16px 24px;
    border-radius: 16px;
    border: 2px solid #e2e8f0;
    background: #f8fafc;
    font-size: 16px;
    transition: all 0.3s ease;
  }

  .search-input:focus {
    border-color: #667eea;
    background: white;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.15);
    transform: translateY(-2px);
  }

  .search-btn {
    background: var(--primary-gradient);
    border: none;
    color: white;
    padding: 16px 32px;
    border-radius: 16px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 8px 24px rgba(102, 126, 234, 0.3);
  }

  .search-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 32px rgba(102, 126, 234, 0.4);
  }

  .books-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 25px;
  }

  .book-card {
    background: white;
    border-radius: 20px;
    border: 1px solid rgba(0, 0, 0, 0.06);
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.08);
    overflow: hidden;
    transition: all 0.35s ease;
    position: relative;
  }

  .book-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 24px 48px rgba(0, 0, 0, 0.15);
  }

  .book-cover {
    height: 180px;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:64px;
    color:#fff;
    position:relative;
    overflow:hidden;
  }

  .book-content { padding: 22px; }

  .book-title {
    font-size: 20px;
    font-weight: 800;
    color: #2d3748;
    margin-bottom: 8px;
    line-height: 1.35;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }

  .book-author {
    color: #718096;
    font-size: 15px;
    margin-bottom: 12px;
    display:flex; gap:6px; align-items:center;
  }

  .book-meta {
    display:flex;
    flex-wrap:wrap;
    gap:10px;
    margin-bottom: 14px;
  }

  .meta-item {
    display:flex;
    align-items:center;
    gap:6px;
    padding:6px 12px;
    background:#f8fafc;
    border-radius:12px;
    font-size:13px;
    color:#4a5568;
  }

  .price-tag{
    display:flex;
    align-items:center;
    justify-content:space-between;
    background: rgba(79,172,254,.10);
    border: 1px solid rgba(79,172,254,.18);
    padding: 10px 12px;
    border-radius: 14px;
    margin-bottom: 14px;
  }
  .price-tag b { font-size: 16px; }
  .price-tag .small { color:#475569; font-weight:700; }

  .availability-badge {
    display:inline-flex;
    align-items:center;
    gap:6px;
    padding:8px 14px;
    border-radius:999px;
    font-size: 13px;
    font-weight: 800;
  }

  .available { background: rgba(11,163,96,.12); color:#0ba360; }
  .unavailable { background: rgba(245,87,108,.12); color:#f5576c; }

  .book-actions, .book-actions-2 {
    display:grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    margin-top: 10px;
  }

  .btn-action {
    border:none;
    padding: 12px 12px;
    border-radius: 14px;
    font-weight: 800;
    text-decoration:none;
    text-align:center;
    display:flex;
    align-items:center;
    justify-content:center;
    gap:8px;
    transition:.2s;
  }
  .btn-action:hover { transform: translateY(-2px); }

  .btn-view { background: var(--primary-gradient); color:#fff; }
  .btn-order { background: rgba(102,126,234,.12); color:#4c51bf; border:1px solid rgba(102,126,234,.25); }
  .btn-buy { background: rgba(11,163,96,.12); color:#0f5132; border:1px solid rgba(11,163,96,.25); }
  .btn-save { background: rgba(240,147,251,.14); color:#842029; border:1px solid rgba(240,147,251,.25); }

  .no-books {
    text-align:center;
    padding: 80px 20px;
    grid-column: 1 / -1;
  }
  .no-books-icon { font-size:64px; opacity:.25; margin-bottom: 16px; }

  .stats-bar {
    display:flex; gap: 12px; flex-wrap:wrap; align-items:center; margin-bottom: 14px;
  }
  .stat-badge {
    display:inline-flex;
    align-items:center;
    gap:8px;
    padding:10px 16px;
    background:#fff;
    border-radius: 14px;
    border:1px solid #e2e8f0;
    font-size: 14px;
    font-weight: 800;
    color:#475569;
  }

  .info-banner {
    background: linear-gradient(135deg, rgba(79, 172, 254, 0.1), rgba(0, 242, 254, 0.1));
    border: 1px solid rgba(79, 172, 254, 0.2);
    border-radius: 16px;
    padding: 20px;
    margin-top: 30px;
  }
</style>

<div class="catalog-wrapper">

  <div class="header-card">
    <div class="row align-items-center">
      <div class="col-md-8">
        <h1 class="fw-bold display-6 mb-2">Book Catalog</h1>
        <p class="text-muted mb-0">Search and choose a book. You can <b>Order</b> or <b>Buy</b> directly.</p>
      </div>
      <div class="col-md-4">
        <div class="top-actions">
          <a class="chip-btn chip-orders" href="/school_library/user/my_orders.php">📦 My Orders</a>
          <a class="chip-btn chip-purchases" href="/school_library/user/purchase_history.php">🛒 Purchases</a>
          <a class="chip-btn chip-saved" href="/school_library/user/saved_books.php">⭐ Saved</a>
        </div>
      </div>
    </div>
  </div>

  <div class="search-card">
    <div class="stats-bar">
      <span class="stat-badge">📚 Total Books: <?php echo (int)$books->num_rows; ?></span>

      <?php if ($q !== ''): ?>
        <span class="stat-badge">🔍 Search: "<?php echo htmlspecialchars($q); ?>"</span>
        <a href="/school_library/user/catalog.php" class="stat-badge" style="text-decoration:none;background:#f8fafc;">❌ Clear</a>
      <?php endif; ?>
    </div>

    <form class="search-form" method="get">
      <input class="search-input" name="q" placeholder="Search by title, author, or ISBN..." value="<?= e($q) ?>" autocomplete="off">
      <button type="submit" class="search-btn">🔍 Search</button>
    </form>
  </div>

  <?php if ($books && $books->num_rows > 0): ?>
    <div class="books-grid">
      <?php
      $colors = [
        'linear-gradient(135deg, #667eea, #764ba2)',
        'linear-gradient(135deg, #4facfe, #00f2fe)',
        'linear-gradient(135deg, #f093fb, #f5576c)',
        'linear-gradient(135deg, #0ba360, #3cba92)',
        'linear-gradient(135deg, #a855f7, #ec4899)',
        'linear-gradient(135deg, #f97316, #f59e0b)',
        'linear-gradient(135deg, #8b5cf6, #a78bfa)',
        'linear-gradient(135deg, #10b981, #34d399)'
      ];
      $colorIndex = 0;

      while($b = $books->fetch_assoc()):
        $color = $colors[$colorIndex % count($colors)];
        $colorIndex++;

        $available = ((int)($b['copies_available'] ?? 0) > 0);
        $price = (float)($b['price'] ?? 0); // ✅ FIXED (real per-book price)
      ?>
        <div class="book-card">
          <div class="book-cover" style="background: <?php echo $color; ?>">
            <?php echo strtoupper(substr($b['title'] ?? 'B', 0, 1)); ?>
          </div>

          <div class="book-content">
            <div class="book-title" title="<?= e($b['title'] ?? '') ?>"><?= e($b['title'] ?? '') ?></div>

            <div class="book-author">
              <span>✍️</span><span><?= e($b['author'] ?? '') ?></span>
            </div>

            <div class="book-meta">
              <div class="meta-item" title="ISBN"><span>📄</span><span><?= e($b['isbn'] ?? '-') ?></span></div>
              <div class="meta-item" title="Category"><span>🏷️</span><span><?= e($b['category'] ?? 'Uncategorized') ?></span></div>
              <div class="meta-item" title="Shelf"><span>📚</span><span><?= e($b['shelf'] ?? 'N/A') ?></span></div>
            </div>

            <div class="price-tag">
              <div class="small">💲 Price</div>
              <b>Rs. <?php echo number_format($price, 2); ?></b>
            </div>

            <div class="<?php echo $available ? 'available' : 'unavailable'; ?> availability-badge">
              <span><?php echo $available ? '✓' : '✗'; ?></span>
              <span><?php echo $available ? 'Available' : 'Unavailable'; ?></span>
              <span>(<?php echo (int)($b['copies_available'] ?? 0); ?>)</span>
            </div>

            <div class="book-actions">
              <a class="btn-action btn-view" href="/school_library/user/book_details.php?id=<?= (int)$b['id'] ?>">👁️ View</a>
              <a class="btn-action btn-save" href="/school_library/user/save_book.php?id=<?= (int)$b['id'] ?>" title="Save book">⭐ Save</a>
            </div>

            <div class="book-actions-2">
              <a class="btn-action btn-order" href="/school_library/user/order_book.php?id=<?= (int)$b['id'] ?>">📦 Order</a>
              <a class="btn-action btn-buy" href="/school_library/user/buy_book.php?id=<?= (int)$b['id'] ?>">🛒 Buy</a>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  <?php else: ?>
    <div class="no-books">
      <div class="no-books-icon">📚</div>
      <h3 class="mb-2">No Books Found</h3>
      <p class="text-muted mb-0">
        <?php if ($q !== ''): ?>
          No books match your search for "<?php echo htmlspecialchars($q); ?>"
        <?php else: ?>
          No books are currently available in the catalog.
        <?php endif; ?>
      </p>
    </div>
  <?php endif; ?>

  <div class="info-banner">
    <div class="d-flex align-items-center gap-3">
      <div style="font-size: 24px;">💡</div>
      <div>
        <div class="fw-bold mb-1">How to Borrow Books</div>
        <div class="text-muted">
          Borrowing is handled by admin (issue/return). Users can Order/Buy from catalog.
        </div>
      </div>
    </div>
  </div>

</div>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>
