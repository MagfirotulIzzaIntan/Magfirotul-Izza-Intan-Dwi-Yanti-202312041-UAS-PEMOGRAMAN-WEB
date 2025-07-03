<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
  header('Location: ../auth/login.php');
  exit;
}
include '../config/db.php';

$daily_reports = $pdo->query("
    SELECT DATE(created_at) AS day, COUNT(*) AS total_orders, SUM(total_amount) AS total_revenue
    FROM orders
    GROUP BY day
    ORDER BY day DESC
")->fetchAll();

$reports = $pdo->query("
    SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, COUNT(*) AS total_orders, SUM(total_amount) AS total_revenue
    FROM orders
    GROUP BY month
    ORDER BY month DESC
")->fetchAll();

// Laporan tahunan
$yearly_reports = $pdo->query("
    SELECT YEAR(created_at) AS year, COUNT(*) AS total_orders, SUM(total_amount) AS total_revenue
    FROM orders
    GROUP BY year
    ORDER BY year DESC
")->fetchAll();

// Produk terlaris (top 10)
$best_sellers = $pdo->query("
    SELECT p.name, SUM(oi.quantity) AS total_sold, p.stock
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    GROUP BY oi.product_id
    ORDER BY total_sold DESC
    LIMIT 10
")->fetchAll();

// Pola pembelian: total order & total belanja per pelanggan
$customer_patterns = $pdo->query("
    SELECT u.username, COUNT(o.id) AS total_orders, SUM(o.total_amount) AS total_spent
    FROM users u
    JOIN orders o ON u.id = o.user_id
    GROUP BY u.id
    ORDER BY total_spent DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Laporan Penjualan - Admin Panel</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
   background: url('https://i.pinimg.com/736x/26/06/f3/2606f3d4293852d28a246ad3cc67d0b8.jpg') no-repeat center center fixed !important;
  background-size: cover !important;
  font-family: Arial, sans-serif;
  color: white;
}

    .container {
      max-width: 900px;
      margin-top: 20px;
    }

    .table {
      background: #4d0000;
    }

    .table thead {
      background: #b30000;
    }

    a,
    a:hover {
      color: #fff;
      text-decoration: none;
    }

    @media (max-width: 991.98px) {
      .sidebar {
        left: -255px;
        width: 250px;
        min-height: 100vh;
        position: fixed;
        top: 0;
        z-index: 1030;
      }

      .sidebar.open {
        left: 0;
      }

      .main {
        margin-left: 0;
        padding: 23px 6px 22px 6px;
      }

      .sidebar-toggle {
        display: block;
      }
    }

    @media (max-width: 575.98px) {
      .dashboard-header i {
        font-size: 1.7em;
      }
    }
  </style>
  <script async src="https://www.googletagmanager.com/gtag/js?id=UA-XXXXXXXXX-X"></script>
  <script>
    window.dataLayer = window.dataLayer || [];

    function gtag() {
      dataLayer.push(arguments);
    }
    gtag('js', new Date());
    gtag('config', 'UA-XXXXXXXXX-X');
  </script>
</head>

<body>
  <?php include '../includes/header.php'; ?>
  <div class="container">
    <h1>Laporan Penjualan</h1>
    <a href="admin_dashboard.php" class="btn btn-light mb-3">Kembali ke Dashboard</a>

    <!-- Laporan Bulanan -->
    <h2>Laporan Penjualan Bulanan</h2>
    <table class="table table-bordered table-hover text-white align-middle">
      <thead>
        <tr>
          <th>Bulan</th>
          <th>Total Pesanan</th>
          <th>Total Pendapatan</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($reports as $r): ?>
          <tr>
            <td><?= htmlspecialchars($r['month']) ?></td>
            <td><?= $r['total_orders'] ?></td>
            <td>Rp <?= number_format($r['total_revenue'], 2) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <!-- Laporan Harian -->
    <h2>Laporan Penjualan Harian</h2>
    <table class="table table-bordered table-hover text-white align-middle">
      <thead>
        <tr>
          <th>Tanggal</th>
          <th>Total Pesanan</th>
          <th>Total Pendapatan</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($daily_reports as $r): ?>
          <tr>
            <td><?= htmlspecialchars($r['day']) ?></td>
            <td><?= $r['total_orders'] ?></td>
            <td>Rp <?= number_format($r['total_revenue'], 2) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <!-- Laporan Tahunan -->
    <h2>Laporan Penjualan Tahunan</h2>
    <table class="table table-bordered table-hover text-white align-middle">
      <thead>
        <tr>
          <th>Tahun</th>
          <th>Total Pesanan</th>
          <th>Total Pendapatan</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($yearly_reports as $r): ?>
          <tr>
            <td><?= htmlspecialchars($r['year']) ?></td>
            <td><?= $r['total_orders'] ?></td>
            <td>Rp <?= number_format($r['total_revenue'], 2) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <!-- Produk Terlaris -->
    <h2>Produk Terlaris & Stok Tersisa</h2>
    <table class="table table-bordered table-hover text-white align-middle">
      <thead>
        <tr>
          <th>Produk</th>
          <th>Terjual</th>
          <th>Stok Tersisa</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($best_sellers as $p): ?>
          <tr>
            <td><?= htmlspecialchars($p['name']) ?></td>
            <td><?= $p['total_sold'] ?></td>
            <td><?= $p['stock'] ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <!-- Pola Pembelian Pelanggan -->
    <h2>Pola Pembelian Pelanggan</h2>
    <table class="table table-bordered table-hover text-white align-middle">
      <thead>
        <tr>
          <th>Pelanggan</th>
          <th>Total Pesanan</th>
          <th>Total Belanja</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($customer_patterns as $c): ?>
          <tr>
            <td><?= htmlspecialchars($c['username']) ?></td>
            <td><?= $c['total_orders'] ?></td>
            <td>Rp <?= number_format($c['total_spent'], 2) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</body>

</html>