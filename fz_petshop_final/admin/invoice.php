<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header('Location: ../auth/login.php');
    exit;
}
include '../config/db.php';

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT o.*, u.username, u.email, u.phone, u.alamat FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id=?");
$stmt->execute([$id]);
$order = $stmt->fetch();

if (!$order) {
    echo "<div class='alert alert-danger'>Pesanan tidak ditemukan.</div>";
    exit;
}

$stmt = $pdo->prepare("SELECT oi.*, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
$stmt->execute([$order['id']]);
$items = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <title>Invoice #<?= htmlspecialchars($order['order_code']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            background: #800000;
            color: #fff;
        }

        .invoice-box {
            max-width: 800px;
            margin: 30px auto;
            padding: 30px;
            border: 1px solid #b30000;
            background: #330000;
            color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .table {
            background: #4d0000;
            color: #fff;
        }

        .table thead {
            background: #b30000;
        }

        .table th,
        .table td {
            vertical-align: middle;
        }

        .text-end {
            text-align: right;
        }

        .btn,
        .btn:focus {
            border-radius: 0;
            box-shadow: none;
        }

        @media print {
            .no-print {
                display: none;
            }

            body {
                background: #fff !important;
                color: #000 !important;
            }

            .invoice-box {
                background: #fff !important;
                color: #000 !important;
                border: none;
                box-shadow: none;
            }

            .table {
                background: #fff !important;
                color: #000 !important;
            }

            .table thead {
                background: #eee !important;
                color: #000 !important;
            }
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
    <div class="invoice-box">
        <h2 class="mb-4">INVOICE</h2>
        <div class="mb-3">
            <strong>Kode Pesanan:</strong> <?= htmlspecialchars($order['order_code']) ?><br>
            <strong>Tanggal:</strong> <?= $order['created_at'] ?><br>
            <strong>Status:</strong> <?= htmlspecialchars($order['status']) ?>
        </div>
        <div class="mb-3">
            <strong>Pelanggan:</strong><br>
            <?= htmlspecialchars($order['username']) ?><br>
            <?= htmlspecialchars($order['email']) ?><br>
            <?= htmlspecialchars($order['phone']) ?><br>
            <?= htmlspecialchars($order['alamat']) ?>
        </div>
        <table class="table table-bordered">
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
                        <td>Rp <?= number_format($item['price'], 2) ?></td>
                        <td>Rp <?= number_format($item['quantity'] * $item['price'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3" class="text-end">Total</th>
                    <th>Rp <?= number_format($order['total_amount'], 2) ?></th>
                </tr>
            </tfoot>
        </table>
        <button class="btn btn-primary no-print" onclick="window.print()">Cetak</button>
        <a href="orders.php" class="btn btn-secondary no-print">Kembali</a>
    </div>
</body>

</html>