<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
  header('Location: ../auth/login.php');
  exit;
}
include '../config/db.php';

$action = $_GET['action'] ?? 'list';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'edit') {
  $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
  $stmt->execute([$_POST['status'], $_POST['id']]);
  header('Location: orders.php');
  exit;
}

if ($action === 'delete') {
  $stmt = $pdo->prepare("DELETE FROM orders WHERE id=?");
  $stmt->execute([$_GET['id']]);
  header('Location: orders.php');
  exit;
}

if ($action == 'list') {
  $orders = $pdo->query("SELECT o.*, u.username FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.id DESC")->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Manajemen Pesanan - Admin Panel</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
   background: url('https://i.pinimg.com/736x/90/d7/2d/90d72def312c78c45d02682feb6c3acc.jpg') no-repeat center center fixed !important;
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

    .form-select,
    .btn,
    .form-control {
      border-radius: 0;
    }

    .form-control,
    .form-select {
      background: #330000;
      color: #fff;
      border: 1px solid #b30000;
    }

    .form-control:focus,
    .form-select:focus {
      background: #4d0000;
      color: #fff;
      border-color: #ff4d4d;
      box-shadow: none;
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
</head>

<body>
  <?php include '../includes/header.php'; ?>
  <div class="container">
    <h1>Manajemen Pesanan</h1>
    <a href="admin_dashboard.php" class="btn btn-light mb-3">Kembali ke Dashboard</a>
    <a href="orders.php" class="btn btn-secondary mb-3">Daftar Pesanan</a>
    <?php if ($action == 'list'): ?>
      <table class="table table-bordered table-hover text-white align-middle">
        <thead>
          <tr>
            <th>ID</th>
            <th>Kode Pesanan</th>
            <th>User</th>
            <th>Total</th>
            <th>Status</th>
            <th>Tanggal</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($orders as $order): ?>
            <tr>
              <td><?= $order['id'] ?></td>
              <td><?= htmlspecialchars($order['order_code']) ?></td>
              <td><?= htmlspecialchars($order['username']) ?></td>
              <td><?= number_format($order['total_amount'], 2) ?></td>
              <td><?= htmlspecialchars($order['status']) ?></td>
              <td><?= $order['created_at'] ?></td>
              <td>
                <a href="orders.php?action=edit&id=<?= $order['id'] ?>" class="btn btn-sm btn-warning text-dark">Edit</a>
                <a href="orders.php?action=detail&id=<?= $order['id'] ?>" class="btn btn-sm btn-info">Detail</a>
                <a href="invoice.php?id=<?= $order['id'] ?>" target="_blank" class="btn btn-sm btn-secondary">Cetak Invoice</a>
                <a href="orders.php?action=delete&id=<?= $order['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus pesanan?')">Hapus</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

    <?php elseif ($action == 'edit'):
      $stmt = $pdo->prepare("SELECT * FROM orders WHERE id=?");
      $stmt->execute([$_GET['id']]);
      $order = $stmt->fetch();
      if (!$order) {
        echo "<div class='alert alert-danger'>Pesanan tidak ditemukan.</div>";
        exit;
      }
    ?>
      <form method="POST" class="mb-5">
        <input type="hidden" name="id" value="<?= $order['id'] ?>">
        <div class="mb-3">
          <label class="form-label">Status Pesanan</label>
          <select name="status" class="form-select" required>
            <option value="Menunggu Pembayaran" <?= $order['status'] == 'Menunggu Pembayaran' ? 'selected' : '' ?>>Menunggu Pembayaran</option>
            <option value="Diproses" <?= $order['status'] == 'Diproses' ? 'selected' : '' ?>>Diproses</option>
            <option value="Dikirim" <?= $order['status'] == 'Dikirim' ? 'selected' : '' ?>>Dikirim</option>
            <option value="Selesai" <?= $order['status'] == 'Selesai' ? 'selected' : '' ?>>Selesai</option>
            <option value="Dibatalkan" <?= $order['status'] == 'Dibatalkan' ? 'selected' : '' ?>>Dibatalkan</option>
          </select>
        </div>
        <button type="submit" class="btn btn-success">Update Status</button>
        <a href="orders.php" class="btn btn-secondary">Batal</a>
      </form>
    <?php elseif ($action == 'detail'):
      // Detail pesanan
      $stmt = $pdo->prepare("SELECT * FROM orders WHERE id=?");
      $stmt->execute([$_GET['id']]);
      $order = $stmt->fetch();
      if (!$order) {
        echo "<div class='alert alert-danger'>Pesanan tidak ditemukan.</div>";
        exit;
      }
      // Ambil produk dalam pesanan
      $stmt = $pdo->prepare("SELECT oi.*, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
      $stmt->execute([$order['id']]);
      $items = $stmt->fetchAll();
    ?>
      <h3>Detail Pesanan</h3>
      <p><strong>Kode Pesanan:</strong> <?= htmlspecialchars($order['order_code']) ?></p>
      <p><strong>Status:</strong> <?= htmlspecialchars($order['status']) ?></p>
      <p><strong>Tanggal:</strong> <?= $order['created_at'] ?></p>
      <p><strong>Total:</strong> Rp <?= number_format($order['total_amount'], 2) ?></p>
      <h5>Produk Dipesan:</h5>
      <table class="table table-bordered table-sm text-white">
        <thead>
          <tr>
            <th>Produk</th>
            <th>Qty</th>
            <th>Harga</th>
            <th>Subtotal</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($items as $item): ?>
            <tr>
              <td><?= htmlspecialchars($item['name']) ?></td>
              <td><?= $item['quantity'] ?></td>
              <td><?= number_format($item['price'], 2) ?></td>
              <td><?= number_format($item['quantity'] * $item['price'], 2) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <a href="orders.php" class="btn btn-secondary">Kembali</a>
    <?php endif; ?>
  </div>
</body>

</html>