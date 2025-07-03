<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header('Location: ../auth/login.php');
    exit;
}
include '../config/db.php';

$action = $_GET['action'] ?? 'list';

// Proses tambah/edit kategori
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'create') {
        $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
        $stmt->execute([$_POST['name'], $_POST['description']]);
        header('Location: categories.php');
        exit;
    }
    if ($action === 'edit') {
        $stmt = $pdo->prepare("UPDATE categories SET name=?, description=? WHERE id=?");
        $stmt->execute([$_POST['name'], $_POST['description'], $_POST['id']]);
        header('Location: categories.php');
        exit;
    }
}

// Proses hapus kategori
if ($action === 'delete') {
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id=?");
    $stmt->execute([$_GET['id']]);
    header('Location: categories.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <title>Manajemen Kategori - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
   background: url('https://i.pinimg.com/736x/1f/6f/6b/1f6f6b1270ee87b79cf96fe0567c370e.jpg') no-repeat center center fixed !important;
  background-size: cover !important;
  font-family: Arial, sans-serif;
  color: white;
}

        .container {
            max-width: 700px;
            margin-top: 30px;
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
        <h1>Manajemen Kategori</h1>
        <a href="admin_dashboard.php" class="btn btn-light mb-3">Kembali ke Dashboard</a>
        <a href="categories.php" class="btn btn-secondary mb-3">Daftar Kategori</a>
        <?php if ($action == 'list'): ?>
            <a href="categories.php?action=create" class="btn btn-primary mb-3">Tambah Kategori</a>
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Kategori</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $categories = $pdo->query("SELECT * FROM categories ORDER BY id DESC")->fetchAll();
                    foreach ($categories as $cat):
                    ?>
                        <tr>
                            <td><?= $cat['id'] ?></td>
                            <td><?= htmlspecialchars($cat['name']) ?></td>
                            <td>
                                <a href="categories.php?action=edit&id=<?= $cat['id'] ?>" class="btn btn-sm btn-warning text-dark">Edit</a>
                                <a href="categories.php?action=delete&id=<?= $cat['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus kategori?')">Hapus</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif ($action == 'create' || $action == 'edit'):
            $cat = ['id' => '', 'name' => '', 'description' => ''];
            if ($action == 'edit') {
                $stmt = $pdo->prepare("SELECT * FROM categories WHERE id=?");
                $stmt->execute([$_GET['id']]);
                $cat = $stmt->fetch();
                if (!$cat) {
                    echo "<div class='alert alert-danger'>Kategori tidak ditemukan.</div>";
                    exit;
                }
            }
        ?>
            <form method="POST" class="mb-5">
                <?php if ($action == 'edit'): ?>
                    <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                <?php endif; ?>
                <div class="mb-3">
                    <label class="form-label">Nama Kategori</label>
                    <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($cat['name']) ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($cat['description']) ?></textarea>
                </div>
                <button type="submit" class="btn btn-success"><?= $action == 'create' ? 'Tambah' : 'Update' ?></button>
                <a href="categories.php" class="btn btn-secondary">Batal</a>
            </form>
        <?php endif; ?>