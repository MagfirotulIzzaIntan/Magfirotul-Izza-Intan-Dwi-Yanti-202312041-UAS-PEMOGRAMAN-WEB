<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header('Location: ../auth/login.php');
    exit;
}
include '../config/db.php';

$action = $_GET['action'] ?? 'list';

// Tambah/Update shipping
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'create') {
        $stmt = $pdo->prepare("INSERT INTO shippings (order_id, courier, tracking_number, cost, status, estimated_delivery) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['order_id'],
            $_POST['courier'],
            $_POST['tracking_number'],
            $_POST['cost'],
            $_POST['status'],
            $_POST['estimated_delivery']
        ]);
        header('Location: shippings.php');
        exit;
    }
    if ($action === 'edit') {
        $stmt = $pdo->prepare("UPDATE shippings SET courier=?, tracking_number=?, cost=?, status=?, estimated_delivery=? WHERE id=?");
        $stmt->execute([
            $_POST['courier'],
            $_POST['tracking_number'],
            $_POST['cost'],
            $_POST['status'],
            $_POST['estimated_delivery'],
            $_POST['id']
        ]);
        header('Location: shippings.php');
        exit;
    }
}

// Hapus shipping
if ($action === 'delete') {
    $stmt = $pdo->prepare("DELETE FROM shippings WHERE id=?");
    $stmt->execute([$_GET['id']]);
    header('Location: shippings.php');
    exit;
}

// Ambil data shipping
$shippings = $pdo->query("SELECT s.*, o.order_code FROM shippings s LEFT JOIN orders o ON s.order_id = o.id ORDER BY s.id DESC")->fetchAll();

// Ambil daftar pesanan untuk dropdown (tambahkan daerah & berat_total)
$orders = $pdo->query("SELECT id, order_code, daerah, berat_total FROM orders ORDER BY id DESC")->fetchAll();

// Ambil data tarif pengiriman
$shipping_rates = $pdo->query("SELECT * FROM shipping_rates ORDER BY daerah, berat_min")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Manajemen Pengiriman - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
    background: url('https://i.pinimg.com/736x/2b/f0/d2/2bf0d2c8428a3384892c520ac4049bd5.jpg') no-repeat center center fixed !important;
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

        .form-control,
        .form-select {
            background: #330000;
            color: #fff;
            border: 1px solid #b30000;
        }

        .form-control:focus,
        .form-select:focus {
            background: #4d0000;
            border-color: #ff4d4d;
            box-shadow: none;
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
    <?php include '../includes/header.php'; ?>
    <div class="container">
        <h1>Manajemen Pengiriman</h1>
        <a href="admin_dashboard.php" class="btn btn-light mb-3">Kembali ke Dashboard</a>
        <a href="shippings.php" class="btn btn-secondary mb-3">Daftar Pengiriman</a>
        <?php if ($action == 'list'): ?>
            <a href="shippings.php?action=create" class="btn btn-primary mb-3">Tambah Pengiriman</a>
            <table class="table table-bordered table-hover text-white align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Kode Pesanan</th>
                        <th>Kurir</th>
                        <th>No. Resi</th>
                        <th>Ongkir</th>
                        <th>Status</th>
                        <th>Estimasi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($shippings as $s): ?>
                        <tr>
                            <td><?= $s['id'] ?></td>
                            <td><?= htmlspecialchars($s['order_code'] ?? '') ?></td>
                            <td><?= htmlspecialchars($s['courier'] ?? '') ?></td>
                            <td><?= htmlspecialchars($s['tracking_number'] ?? '') ?></td>
                            <td><?= number_format($s['cost'], 2) ?></td>
                            <td><?= htmlspecialchars($s['status'] ?? '') ?></td>
                            <td><?= htmlspecialchars($s['estimated_delivery'] ?? '') ?></td>
                            <td>
                                <a href="shippings.php?action=edit&id=<?= $s['id'] ?>" class="btn btn-sm btn-warning text-dark mb-1">Edit</a>
                                <a href="shippings.php?action=delete&id=<?= $s['id'] ?>" class="btn btn-sm btn-danger mb-1" onclick="return confirm('Yakin hapus data pengiriman?')">Hapus</a>
                                <a href="shipping_rates.php" class="btn btn-sm btn-primary mb-1">Atur Tarif</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif ($action == 'create' || $action == 'edit'):
            $shipping = ['id' => '', 'order_id' => '', 'courier' => '', 'tracking_number' => '', 'cost' => '', 'status' => 'Belum Dikirim', 'estimated_delivery' => ''];
            if ($action == 'edit') {
                $stmt = $pdo->prepare("SELECT * FROM shippings WHERE id=?");
                $stmt->execute([$_GET['id']]);
                $shipping = $stmt->fetch();
                if (!$shipping) {
                    echo "<div class='alert alert-danger'>Data pengiriman tidak ditemukan.</div>";
                    exit;
                }
            }
        ?>
            <form method="POST" class="mb-5">
                <?php if ($action == 'edit'): ?>
                    <input type="hidden" name="id" value="<?= $shipping['id'] ?>">
                <?php endif; ?>
                <div class="mb-3">
                    <label class="form-label">Pesanan</label>
                    <select name="order_id" class="form-select" required <?= $action == 'edit' ? 'disabled' : '' ?>>
                        <option value="">Pilih Pesanan</option>
                        <?php foreach ($orders as $o): ?>
                            <option value="<?= $o['id'] ?>" <?= $o['id'] == $shipping['order_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($o['order_code']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($action == 'edit'): ?>
                        <input type="hidden" name="order_id" value="<?= $shipping['order_id'] ?>">
                    <?php endif; ?>
                </div>
                <div class="mb-3">
                    <label class="form-label">Kurir</label>
                    <input type="text" name="courier" class="form-control" required value="<?= htmlspecialchars($shipping['courier'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">No. Resi</label>
                    <input type="text" name="tracking_number" class="form-control" value="<?= htmlspecialchars($shipping['tracking_number'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Pesanan</label>
                    <select name="order_id" id="order_id" class="form-select" required <?= $action == 'edit' ? 'disabled' : '' ?> onchange="setOngkir()">
                        <option value="">Pilih Pesanan</option>
                        <?php foreach ($orders as $o): ?>
                            <option
                                value="<?= $o['id'] ?>"
                                data-daerah="<?= htmlspecialchars($o['daerah']) ?>"
                                data-berat="<?= $o['berat_total'] ?>"
                                <?= $o['id'] == $shipping['order_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($o['order_code']) ?> (<?= htmlspecialchars($o['daerah']) ?>, <?= $o['berat_total'] ?> kg)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($action == 'edit'): ?>
                        <input type="hidden" name="order_id" value="<?= $shipping['order_id'] ?>">
                    <?php endif; ?>
                </div>
                <div class="mb-3">
                    <label class="form-label">Ongkir</label>
                    <input type="number" step="0.01" name="cost" id="ongkir" class="form-control" required value="<?= htmlspecialchars($shipping['cost'] ?? '') ?>">
                    <div class="form-text text-white-50">Ongkir otomatis terisi sesuai tarif jika pesanan dipilih.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Status Pengiriman</label>
                    <select name="status" class="form-select" required>
                        <option value="Belum Dikirim" <?= $shipping['status'] == 'Belum Dikirim' ? 'selected' : ''; ?>>Belum Dikirim</option>
                        <option value="Dikirim" <?= $shipping['status'] == 'Dikirim' ? 'selected' : ''; ?>>Dikirim</option>
                        <option value="Selesai" <?= $shipping['status'] == 'Selesai' ? 'selected' : ''; ?>>Selesai</option>
                        <option value="Dibatalkan" <?= $shipping['status'] == 'Dibatalkan' ? 'selected' : ''; ?>>Dibatalkan</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Estimasi Pengiriman</label>
                    <input type="text" name="estimated_delivery" class="form-control" value="<?= htmlspecialchars($shipping['estimated_delivery'] ?? '') ?>">
                </div>
                <button type="submit" class="btn btn-success"><?= $action == 'create' ? 'Tambah' : 'Update' ?></button>
                <a href="shippings.php" class="btn btn-secondary">Batal</a>
            </form>
        <?php endif; ?>
    </div>
    <script>
        // Data tarif dari PHP ke JS
        const shippingRates = <?= json_encode($shipping_rates) ?>;

        function setOngkir() {
            const select = document.getElementById('order_id');
            const ongkirInput = document.getElementById('ongkir');
            const daerah = select.options[select.selectedIndex].getAttribute('data-daerah');
            const berat = parseFloat(select.options[select.selectedIndex].getAttribute('data-berat'));
            let tarif = 0;
            shippingRates.forEach(rate => {
                if (
                    rate.daerah.trim().toLowerCase() === daerah.trim().toLowerCase() &&
                    berat >= parseFloat(rate.berat_min) &&
                    berat <= parseFloat(rate.berat_max)
                ) {
                    tarif = rate.tarif;
                }
            });
            ongkirInput.value = tarif;
        }
    </script>
</body>

</html>