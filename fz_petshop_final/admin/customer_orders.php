<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header('Location: ../auth/login.php');
    exit;
}
include '../config/db.php';

$customer_id = $_GET['id'] ?? 0;

// Ambil data pelanggan
$stmt = $pdo->prepare("SELECT username, email FROM users WHERE id=?");
$stmt->execute([$customer_id]);
$customer = $stmt->fetch();

if (!$customer) {
    echo "<div class='alert alert-danger'>Pelanggan tidak ditemukan.</div>";
    exit;
}

// Ambil riwayat pesanan pelanggan
$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id=? ORDER BY created_at DESC");
$stmt->execute([$customer_id]);
$orders = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT COUNT(*) as total_orders, SUM(total_amount) as total_spent FROM orders WHERE user_id=?");
$stmt->execute([$customer_id]);
$pattern = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Riwayat Pembelian - <?= htmlspecialchars($customer['username']) ?></title>
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

        .btn {
            border-radius: 0;
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
    <div class="container">
        <h2>Riwayat Pembelian</h2>
        <div class="mb-3">
            <strong>Nama:</strong> <?= htmlspecialchars($customer['username']) ?><br>
            <strong>Email:</strong> <?= htmlspecialchars($customer['email']) ?><br>
            <strong>Total Pesanan:</strong> <?= $pattern['total_orders'] ?? 0 ?><br>
            <strong>Total Belanja:</strong> Rp <?= number_format($pattern['total_spent'] ?? 0, 2) ?>
        </div>
        <a href="customers.php" class="btn btn-light mb-3">Kembali ke Daftar Pelanggan</a>
        <table class="table table-bordered table-hover text-white align-middle">
            <thead>
                <tr>
                    <th>Kode Pesanan</th>
                    <th>Tanggal</th>
                    <th>Status</th>
                    <th>Total</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted">Belum ada pembelian.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= htmlspecialchars($order['order_code']) ?></td>
                            <td><?= $order['created_at'] ?></td>
                            <td><?= htmlspecialchars($order['status']) ?></td>
                            <td>Rp <?= number_format($order['total_amount'], 2) ?></td>
                            <td>
                                <a href="orders.php?action=detail&id=<?= $order['id'] ?>" class="btn btn-sm btn-info">Detail</a>
                                <a href="invoice.php?id=<?= $order['id'] ?>" target="_blank" class="btn btn-sm btn-secondary">Cetak Invoice</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>

</html>