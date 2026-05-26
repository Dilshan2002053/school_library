<?php
require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/functions.php";
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>📚 School Library</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="/school_library/assets/css/style.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand fw-bold" href="/school_library/index.php">📚 School Library</a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav ms-auto">

        <?php if (!is_logged_in()): ?>
          <li class="nav-item">
            <a class="nav-link" href="/school_library/login.php">Login</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/school_library/register.php">Register</a>
          </li>

        <?php else: ?>
          <?php if (current_user()['role'] === 'admin'): ?>
            <li class="nav-item"><a class="nav-link" href="/school_library/admin/dashboard.php">Admin</a></li>
            <li class="nav-item"><a class="nav-link" href="/school_library/admin/books.php">Books</a></li>
            <li class="nav-item"><a class="nav-link" href="/school_library/admin/loans.php">Issue</a></li>
            <li class="nav-item"><a class="nav-link" href="/school_library/admin/returns.php">Return</a></li>
          <?php else: ?>
            <li class="nav-item"><a class="nav-link" href="/school_library/user/dashboard.php">Dashboard</a></li>
            <li class="nav-item"><a class="nav-link" href="/school_library/user/catalog.php">Catalog</a></li>
            
          <?php endif; ?>

          <li class="nav-item">
            <span class="nav-link text-warning">
              <?= e(current_user()['full_name']) ?> (<?= e(current_user()['role']) ?>)
            </span>
          </li>

          <li class="nav-item">
            <a class="nav-link" href="/school_library/logout.php">Logout</a>
          </li>
        <?php endif; ?>

      </ul>
    </div>
  </div>
</nav>

<div class="container py-4">
  <?php flash(); ?>
