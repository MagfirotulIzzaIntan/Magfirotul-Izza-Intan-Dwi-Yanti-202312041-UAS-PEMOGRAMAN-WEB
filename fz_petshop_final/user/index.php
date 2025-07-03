<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role_id'], [2, 3, 4])) {
  header('Location: ../auth/login.php');
  exit;
}
$username = $_SESSION['username'];
$role_id = $_SESSION['role_id'];

// Role label
$role_label = "Member";
if ($role_id == 3) $role_label = "Premium";
elseif ($role_id == 4) $role_label = "VIP";
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Dashboard - FZ Petshop</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <style>
    body {
      background: linear-gradient(135deg, #800000, #b30000);
      color: white;
      font-family: 'Segoe UI', sans-serif;
      margin: 0;
      min-height: 100vh;
      overflow-x: hidden;
    }

    .sidebar {
      background-color: #4d0000;
      height: 100vh;
      width: 260px;
      padding: 20px 0 20px 0;
      overflow-y: auto;
      position: fixed;
      top: 0;
      left: 0;
      z-index: 1040;
      transition: left .3s;
    }

    .sidebar h4 {
      text-align: center;
      margin-bottom: 28px;
      font-weight: bold;
      color: #ffc0cb;
      letter-spacing: 1.5px;
    }

    .sidebar .profile {
      text-align: center;
      margin-bottom: 18px;
    }

    .sidebar .profile i {
      font-size: 2.6em;
      color: #ff69b4;
      margin-bottom: 5px;
    }

    .sidebar .profile .username {
      font-size: 1.09em;
      font-weight: 600;
      margin-top: 2px;
      color: #fff;
    }

    .sidebar .profile .role-label {
      font-size: .96em;
      color: #ffb6d5;
      font-style: italic;
      margin-bottom: 2px;
      letter-spacing: 1px;
    }

    .sidebar a.menu-link {
      display: flex;
      align-items: center;
      color: white;
      padding: 10px 24px;
      margin-bottom: 8px;
      border-radius: 7px;
      text-decoration: none;
      font-weight: 600;
      transition: background-color 0.25s, color .18s;
      font-size: 1.07em;
    }

    .sidebar a.menu-link:hover,
    .sidebar a.menu-link.active {
      background-color: #b30000;
      color: #fff;
    }

    .sidebar a.menu-link i {
      margin-right: 13px;
      font-size: 20px;
    }

    .sidebar .logout-link {
      margin-top: 30px;
      border-top: 1.5px solid #ffc0cb44;
      padding-top: 19px;
    }

    main {
      margin-left: 260px;
      padding: 45px 38px 32px 38px;
      min-height: 100vh;
      color: #fff;
      background: linear-gradient(120deg, #800000 80%, #b30000);
      transition: margin-left 0.3s;
    }

    .dashboard-header {
      display: flex;
      align-items: center;
      gap: 18px;
      margin-bottom: 30px;
    }

    .dashboard-header i {
      font-size: 2.2em;
      color: #ff69b4;
    }

    .dashboard-header .welcome {
      font-size: 1.2em;
      font-weight: bold;
      color: #ff69b4;
      margin-bottom: 3px;
    }

    .dashboard-info {
      margin-top: 20px;
      background: #4d0000;
      padding: 28px 22px;
      border-radius: 12px;
      font-size: 1.05em;
      color: #fff;
      box-shadow: 0 1px 7px rgba(255, 105, 180, .08);
    }

    .quick-actions {
      margin-top: 12px;
      margin-bottom: 32px;
      display: flex;
      flex-wrap: wrap;
      gap: 18px;
    }

    .quick-action-card {
      background: #b30000;
      border-radius: 12px;
      color: #fff;
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 18px 24px;
      min-width: 170px;
      box-shadow: 0 1px 8px rgba(128, 0, 0, .08);
      font-weight: 600;
      transition: background .16s, transform .10s;
      text-decoration: none;
    }

    .quick-action-card:hover,
    .quick-action-card:focus {
      background: #ff69b4;
      color: #800000;
      transform: scale(1.04);
      text-decoration: none;
    }

    .quick-action-card i {
      font-size: 1.4em;
      color: #fff;
      transition: color .16s;
    }

    .quick-action-card:hover i {
      color: #800000;
    }

    @media (max-width: 991.98px) {
      .sidebar {
        left: -260px;
        width: 250px;
        position: fixed;
        z-index: 1055;
      }

      .sidebar.open {
        left: 0;
      }

      main {
        margin-left: 0;
        padding: 24px 6px 22px 6px;
      }

      .dashboard-header i {
        font-size: 1.7em;
      }
    }

    .sidebar-toggle {
      display: none;
      position: fixed;
      top: 15px;
      left: 15px;
      background: #b30000;
      color: #fff;
      border: none;
      border-radius: 8px;
      padding: 8px 12px;
      z-index: 1060;
      font-size: 1.2em;
    }

    @media (max-width: 991.98px) {
      .sidebar-toggle {
        display: block;
      }
    }
  </style>
</head>

<body>

  <button class="sidebar-toggle" type="button" onclick="toggleSidebar()" aria-label="Buka menu"><i class="bi bi-list"></i></button>
  <div class="sidebar" role="navigation" aria-label="Sidebar menu" id="sidebar">
    <h4>🐾 FZ Petshop</h4>
    <div class="profile">
      <i class="bi bi-person-circle"></i>
      <div class="username"><?= htmlspecialchars($username) ?></div>
      <div class="role-label"><?= $role_label ?></div>
    </div>
    <a href="index.php" class="menu-link active"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="katalog.php" class="menu-link"><i class="bi bi-shop-window"></i> Katalog</a>
    <a href="products.php" class="menu-link"><i class="bi bi-box-seam"></i> Produk</a>
    <!-- <a href="orders.php" class="menu-link"><i class="bi bi-receipt"></i> Pesanan</a> -->
    <a href="blogs.php" class="menu-link"><i class="bi bi-journal-text"></i> Blog</a>
    <div class="logout-link">
      <a href="../auth/logout.php" class="menu-link"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </div>
  </div>

  <main tabindex="0" aria-label="Konten utama dashboard">
    <div class="dashboard-header">
      <i class="bi bi-speedometer2"></i>
      <div>
        <div class="welcome">Selamat datang, <?= htmlspecialchars($username) ?>!</div>
        <div>Anda login sebagai <span class="fw-bold"><?= $role_label ?></span>.</div>
      </div>
    </div>

    <div class="quick-actions">
      <div class="quick-action-card">
        <i class="bi bi-shop-window"></i> Katalog
      </div>
      <div class="quick-action-card">
        <i class="bi bi-box-seam"></i> Produk
      </div>
      <div class="quick-action-card">
        <i class="bi bi-journal-text"></i> Blog
      </div>
    </div>

    <div class="dashboard-info">
      <h5 style="color:#ff69b4;font-weight:bold;">Petunjuk</h5>
      <ul class="mb-0">
        <li>Gunakan menu di samping untuk mengelola katalog, produk, dan blog.</li>
        <li>Nikmati pengalaman berbelanja dan dapatkan promo menarik khusus <?= $role_label ?>.</li>
        <li>Pastikan selalu logout setelah selesai menggunakan sistem.</li>
      </ul>
    </div>
  </main>

  <script>
    function toggleSidebar() {
      var sidebar = document.getElementById('sidebar');
      sidebar.classList.toggle('open');
    }
    // Tutup sidebar saat klik di luar (mobile)
    document.addEventListener('click', function(e) {
      var sidebar = document.getElementById('sidebar');
      var btn = document.querySelector('.sidebar-toggle');
      if (window.innerWidth <= 991 && sidebar.classList.contains('open')) {
        if (!sidebar.contains(e.target) && !btn.contains(e.target)) {
          sidebar.classList.remove('open');
        }
      }
    });
  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>