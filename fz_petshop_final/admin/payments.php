<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
  header('Location: ../auth/login.php');
  exit;
}
include '../config/db.php';

$action = $_GET['action'] ?? 'list';

// Handle CREATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'create') {
  // Validasi sederhana
  $order_id = $_POST['order_id'] ?? '';
  $method = $_POST['method'] ?? '';
  $status = $_POST['status'] ?? '';
  $amount = $_POST['amount'] ?? '';
  $paid_at = $_POST['paid_at'] ?? '';

  if ($order_id && $method && $status && $amount !== '') {
    $stmt = $pdo->prepare("INSERT INTO payments (order_id, method, status, amount, paid_at) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$order_id, $method, $status, $amount, $paid_at]);
    header('Location: payments.php');
    exit;
  } else {
    $error_create = "Semua field wajib diisi!";
  }
}

// Handle EDIT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'edit') {
  $stmt = $pdo->prepare("UPDATE payments SET method = ?, status = ?, amount = ?, paid_at = ? WHERE id = ?");
  $stmt->execute([
    $_POST['method'],
    $_POST['status'],
    $_POST['amount'],
    $_POST['paid_at'],
    $_POST['id']
  ]);
  header('Location: payments.php');
  exit;
}

// Handle DELETE
if ($action === 'delete') {
  $stmt = $pdo->prepare("DELETE FROM payments WHERE id=?");
  $stmt->execute([$_GET['id']]);
  header('Location: payments.php');
  exit;
}

// Data untuk list
$payments = $pdo->query("SELECT p.*, o.order_code FROM payments p LEFT JOIN orders o ON p.order_id = o.id ORDER BY p.paid_at DESC")->fetchAll();
// Data orders untuk form create
$orders = $pdo->query("SELECT id, order_code FROM orders ORDER BY order_code ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Manajemen Pembayaran - Admin Panel</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
   background: url('https://i.pinimg.com/736x/e3/11/90/e31190096b08e2ed1e2edd5734125f24.jpg') no-repeat center center fixed !important;
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
    <h1>Manajemen Pembayaran</h1>
    <a href="admin_dashboard.php" class="btn btn-light mb-3">Kembali ke Dashboard</a>
    <a href="payments.php" class="btn btn-secondary mb-3">Daftar Pembayaran</a>
    <?php if ($action == 'list'): ?>
      <a href="payments.php?action=create" class="btn btn-primary mb-3">Tambah Pembayaran</a>
      <table class="table table-bordered table-hover text-white align-middle">
        <thead>
          <tr>
            <th>ID</th>
            <th>Kode Pesanan</th>
            <th>Metode</th>
            <th>Status</th>
            <th>Jumlah</th>
            <th>Tanggal Bayar</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($payments as $p): ?>
            <tr>
              <td><?= $p['id'] ?></td>
              <td><?= htmlspecialchars($p['order_code']) ?></td>
              <td><?= htmlspecialchars($p['method']) ?></td>
              <td><?= htmlspecialchars($p['status']) ?></td>
              <td><?= number_format($p['amount'], 2) ?></td>
              <td><?= $p['paid_at'] ?></td>
              <td>
                <a href="payments.php?action=edit&id=<?= $p['id'] ?>" class="btn btn-sm btn-warning text-dark">Edit</a>
                <a href="payments.php?action=delete&id=<?= $p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus pembayaran?')">Hapus</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

    <?php elseif ($action == 'create'): ?>
      <h3 class="mb-4">Tambah Pembayaran</h3>
      <?php if (!empty($error_create)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error_create) ?></div>
      <?php endif; ?>
      <form method="POST" class="mb-5">
        <input type="hidden" name="action" value="create">
        <div class="mb-3">
          <label class="form-label">Pesanan</label>
          <select name="order_id" class="form-select" required>
            <option value="">--Pilih Pesanan--</option>
            <?php foreach ($orders as $o): ?>
              <option value="<?= $o['id'] ?>"><?= htmlspecialchars($o['order_code']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Metode Pembayaran</label>
          <select name="method" class="form-select" required>
            <option value="Bank Transfer">Bank Transfer</option>
            <option value="E-Wallet">E-Wallet</option>
            <option value="COD">COD</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Status</label>
          <select name="status" class="form-select" required>
            <option value="Pending">Pending</option>
            <option value="Lunas">Lunas</option>
            <option value="Gagal">Gagal</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Jumlah</label>
          <input type="number" step="0.01" name="amount" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Tanggal Bayar</label>
          <input type="datetime-local" name="paid_at" class="form-control">
        </div>
        <button type="submit" class="btn btn-success">Tambah</button>
        <a href="payments.php" class="btn btn-secondary">Batal</a>
      </form>

    <?php elseif ($action == 'edit'):
      $stmt = $pdo->prepare("SELECT * FROM payments WHERE id=?");
      $stmt->execute([$_GET['id']]);
      $payment = $stmt->fetch();
      if (!$payment) {
        echo "<div class='alert alert-danger'>Pembayaran tidak ditemukan.</div>";
        exit;
      }
    ?>
      <h3 class="mb-4">Edit Pembayaran</h3>
      <form method="POST" class="mb-5">
        <input type="hidden" name="id" value="<?= $payment['id'] ?>">
        <div class="mb-3">
          <label class="form-label">Metode Pembayaran</label>
          <select name="method" class="form-select" required>
            <option value="Bank Transfer" <?= $payment['method'] == 'Bank Transfer' ? 'selected' : '' ?>>Bank Transfer</option>
            <option value="E-Wallet" <?= $payment['method'] == 'E-Wallet' ? 'selected' : '' ?>>E-Wallet</option>
            <option value="COD" <?= $payment['method'] == 'COD' ? 'selected' : '' ?>>COD</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Status</label>
          <select name="status" class="form-select" required>
            <option value="Pending" <?= $payment['status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
            <option value="Lunas" <?= $payment['status'] == 'Lunas' ? 'selected' : '' ?>>Lunas</option>
            <option value="Gagal" <?= $payment['status'] == 'Gagal' ? 'selected' : '' ?>>Gagal</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Jumlah</label>
          <input type="number" step="0.01" name="amount" class="form-control" required value="<?= $payment['amount'] ?>">
        </div>
        <div class="mb-3">
          <label class="form-label">Tanggal Bayar</label>
          <input type="datetime-local" name="paid_at" class="form-control" value="<?= date('Y-m-d\TH:i', strtotime($payment['paid_at'])) ?>">
        </div>
        <button type="submit" class="btn btn-success">Update</button>
        <a href="payments.php" class="btn btn-secondary">Batal</a>
      </form>
    <?php endif; ?>
  </div>
</body>

</html>