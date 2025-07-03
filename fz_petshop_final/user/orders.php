<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role_id'], [2, 3, 4])) {
  header('Location: ../auth/login.php');
  exit;
}
$user_id = $_SESSION['user_id'];

// Ambil daftar pesanan milik user yang login saja
$orders = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$orders->execute([$user_id]);
$orders = $orders->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Pesanan Saya - FZ Petshop</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
      background: #800000;
      color: #fff;
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
  </style>
</head>

<body>
  <div class="container">
    <h1>Pesanan Saya</h1>
    <?php if (count($orders) === 0): ?>
      <div class="alert alert-warning text-dark">Belum ada pesanan.</div>
    <?php else: ?>
      <table class="table table-bordered table-hover text-white align-middle">
        <thead>
          <tr>
            <th>Kode Pesanan</th>
            <th>Total</th>
            <th>Status</th>
            <th>Tanggal</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($orders as $order): ?>
            <tr>
              <td><?= htmlspecialchars($order['order_code']) ?></td>
              <td>Rp <?= number_format($order['total_amount'], 0, ',', '.') ?></td>
              <td><?= htmlspecialchars($order['status']) ?></td>
              <td><?= $order['created_at'] ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
    <a href="index.php" class="btn btn-light mt-3"><i class="bi bi-arrow-left"></i> Kembali ke Dashboard</a>
  </div>
</body>

</html>