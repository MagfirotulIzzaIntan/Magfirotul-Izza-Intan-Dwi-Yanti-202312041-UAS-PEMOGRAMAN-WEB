<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header('Location: ../auth/login.php');
    exit;
}
include '../config/db.php';

$action = $_GET['action'] ?? 'list';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'create') {
        $stmt = $pdo->prepare("INSERT INTO shipping_rates (daerah, berat_min, berat_max, tarif) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $_POST['daerah'],
            $_POST['berat_min'],
            $_POST['berat_max'],
            $_POST['tarif']
        ]);
        header('Location: shipping_rates.php');
        exit;
    }
    if ($action === 'edit') {
        $stmt = $pdo->prepare("UPDATE shipping_rates SET daerah=?, berat_min=?, berat_max=?, tarif=? WHERE id=?");
        $stmt->execute([
            $_POST['daerah'],
            $_POST['berat_min'],
            $_POST['berat_max'],
            $_POST['tarif'],
            $_POST['id']
        ]);
        header('Location: shipping_rates.php');
        exit;
    }
}

if ($action === 'delete') {
    $stmt = $pdo->prepare("DELETE FROM shipping_rates WHERE id=?");
    $stmt->execute([$_GET['id']]);
    header('Location: shipping_rates.php');
    exit;
}

$rates = $pdo->query("SELECT * FROM shipping_rates ORDER BY daerah, berat_min")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Atur Tarif Pengiriman</title>
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

        .btn {
            border-radius: 0;
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
</head>

<body>
    <div class="container">
        <h1>Atur Tarif Pengiriman</h1>
        <a href="shippings.php" class="btn btn-light mb-3">Kembali ke Pengiriman</a>
        <a href="shipping_rates.php" class="btn btn-secondary mb-3">Daftar Tarif</a>
        <?php if ($action == 'list'): ?>
            <a href="shipping_rates.php?action=create" class="btn btn-primary mb-3">Tambah Tarif</a>
            <table class="table table-bordered table-hover text-white align-middle">
                <thead>
                    <tr>
                        <th>Daerah</th>
                        <th>Berat Min (kg)</th>
                        <th>Berat Max (kg)</th>
                        <th>Tarif (Rp)</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rates as $r): ?>
                        <tr>
                            <td><?= htmlspecialchars($r['daerah']) ?></td>
                            <td><?= $r['berat_min'] ?></td>
                            <td><?= $r['berat_max'] ?></td>
                            <td><?= number_format($r['tarif'], 2) ?></td>
                            <td>
                                <a href="shipping_rates.php?action=edit&id=<?= $r['id'] ?>" class="btn btn-sm btn-warning text-dark">Edit</a>
                                <a href="shipping_rates.php?action=delete&id=<?= $r['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus tarif?')">Hapus</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif ($action == 'create' || $action == 'edit'):
            $rate = ['id' => '', 'daerah' => '', 'berat_min' => '', 'berat_max' => '', 'tarif' => ''];
            if ($action == 'edit') {
                $stmt = $pdo->prepare("SELECT * FROM shipping_rates WHERE id=?");
                $stmt->execute([$_GET['id']]);
                $rate = $stmt->fetch();
                if (!$rate) {
                    echo "<div class='alert alert-danger'>Tarif tidak ditemukan.</div>";
                    exit;
                }
            }
        ?>
            <form method="POST" class="mb-5">
                <?php if ($action == 'edit'): ?>
                    <input type="hidden" name="id" value="<?= $rate['id'] ?>">
                <?php endif; ?>
                <div class="mb-3">
                    <label class="form-label">Daerah</label>
                    <input type="text" name="daerah" class="form-control" required value="<?= htmlspecialchars($rate['daerah']) ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Berat Minimum (kg)</label>
                    <input type="number" step="0.01" name="berat_min" class="form-control" required value="<?= $rate['berat_min'] ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Berat Maksimum (kg)</label>
                    <input type="number" step="0.01" name="berat_max" class="form-control" required value="<?= $rate['berat_max'] ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Tarif (Rp)</label>
                    <input type="number" step="0.01" name="tarif" class="form-control" required value="<?= $rate['tarif'] ?>">
                </div>
                <button type="submit" class="btn btn-success"><?= $action == 'create' ? 'Tambah' : 'Update' ?></button>
                <a href="shipping_rates.php" class="btn btn-secondary">Batal</a>
            </form>
        <?php endif; ?>
    </div>
</body>

</html>