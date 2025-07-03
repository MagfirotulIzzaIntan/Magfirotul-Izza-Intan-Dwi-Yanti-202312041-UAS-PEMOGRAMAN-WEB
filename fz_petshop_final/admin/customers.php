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
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role_id, email, phone, alamat, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([
            $_POST['username'],
            password_hash($_POST['password'], PASSWORD_DEFAULT),
            $_POST['role_id'],
            $_POST['email'],
            $_POST['phone'],
            $_POST['alamat']
        ]);
        header('Location: customers.php');
        exit;
    }
    if ($action === 'edit') {
        $stmt = $pdo->prepare("UPDATE users SET username=?, email=?, phone=?, alamat=?, role_id=? WHERE id=?");
        $stmt->execute([
            $_POST['username'],
            $_POST['email'],
            $_POST['phone'],
            $_POST['alamat'],
            $_POST['role_id'],
            $_POST['id']
        ]);
        header('Location: customers.php');
        exit;
    }
}

// Hapus pelanggan
if ($action === 'delete') {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id=?");
    $stmt->execute([$_GET['id']]);
    header('Location: customers.php');
    exit;
}

$customers = $pdo->query("SELECT * FROM users WHERE role_id IN (2, 3, 4) ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Manajemen Pelanggan - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
     background: url('https://i.pinimg.com/736x/f1/44/57/f14457d6f78a0a84dfa4c8c8e420270a.jpg') no-repeat center center fixed !important;
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
        .form-select,
        textarea {
            background: #330000;
            color: #fff;
            border: 1px solid #b30000;
        }

        .form-control:focus,
        .form-select:focus,
        textarea:focus {
            background: #4d0000;
            color: #fff;
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
        <h1>Manajemen Pelanggan</h1>
        <a href="admin_dashboard.php" class="btn btn-light mb-3">Kembali ke Dashboard</a>
        <a href="customers.php" class="btn btn-secondary mb-3">Daftar Pelanggan</a>
        <?php if ($action == 'list'): ?>
            <a href="customers.php?action=create" class="btn btn-primary mb-3">Tambah Pelanggan</a>
            <table class="table table-bordered table-hover text-white align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>No. HP</th>
                        <th>Alamat</th>
                        <th>Segment</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customers as $c): ?>
                        <tr>
                            <td><?= $c['id'] ?></td>
                            <td><?= htmlspecialchars($c['username'] ?? '') ?></td>
                            <td><?= htmlspecialchars($c['email'] ?? '') ?></td>
                            <td><?= htmlspecialchars($c['phone'] ?? '') ?></td>
                            <td><?= htmlspecialchars($c['alamat'] ?? '') ?></td>
                            <td><?php
                                if ($c['role_id'] == 2) echo 'Member';
                                elseif ($c['role_id'] == 3) echo 'Premium';
                                elseif ($c['role_id'] == 4) echo 'VIP';
                                else echo 'Lainnya';
                                ?></td>
                            <td>
                                <a href="customers.php?action=edit&id=<?= $c['id'] ?>" class="btn btn-sm btn-warning text-dark">Edit</a>
                                <a href="customers.php?action=delete&id=<?= $c['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus pelanggan?')">Hapus</a>
                                <a href="customer_orders.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-info">Riwayat</a>
                                <a href="send_email.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-success">Kirim Email</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif ($action == 'create' || $action == 'edit'):
            // TAMBAHKAN 'role_id' => 2 agar tidak warning
            $customer = ['id' => '', 'username' => '', 'email' => '', 'phone' => '', 'alamat' => '', 'role_id' => 2];
            if ($action == 'edit') {
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
                $stmt->execute([$_GET['id']]);
                $customer = $stmt->fetch();
                if (!$customer) {
                    echo "<div class='alert alert-danger'>Pelanggan tidak ditemukan.</div>";
                    exit;
                }
            }
        ?>
            <form method="POST" class="mb-5">
                <?php if ($action == 'edit'): ?>
                    <input type="hidden" name="id" value="<?= $customer['id'] ?>">
                <?php endif; ?>
                <div class="mb-3">
                    <label class="form-label">Nama</label>
                    <input type="text" name="username" class="form-control" required value="<?= htmlspecialchars($customer['username'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($customer['email']) ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">No. HP</label>
                    <input type="text" name="phone" class="form-control" required value="<?= htmlspecialchars($customer['phone']) ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Alamat</label>
                    <textarea name="alamat" class="form-control" rows="2" required><?= htmlspecialchars($customer['alamat'] ?? '') ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Segment</label>
                    <select name="role_id" class="form-select" required>
                        <option value="2" <?= $customer['role_id'] == 2 ? 'selected' : ''; ?>>Member</option>
                        <option value="3" <?= $customer['role_id'] == 3 ? 'selected' : ''; ?>>Premium</option>
                        <option value="4" <?= $customer['role_id'] == 4 ? 'selected' : ''; ?>>VIP</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-success"><?= $action == 'create' ? 'Tambah' : 'Update' ?></button>
                <a href="customers.php" class="btn btn-secondary">Batal</a>
            </form>
        <?php endif; ?>
    </div>
</body>

</html>