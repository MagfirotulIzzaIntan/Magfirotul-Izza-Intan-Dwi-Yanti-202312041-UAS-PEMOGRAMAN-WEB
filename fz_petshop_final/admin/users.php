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
    $password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role_id) VALUES (?, ?, ?, ?)");
    $stmt->execute([
      $_POST['username'],
      $_POST['email'],
      $password_hash,
      $_POST['role_id']
    ]);
    header('Location: users.php');
    exit;
  }
  if ($action === 'edit') {
    if (!empty($_POST['password'])) {
      $password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
      $stmt = $pdo->prepare("UPDATE users SET username=?, email=?, password=?, role_id=? WHERE id=?");
      $stmt->execute([
        $_POST['username'],
        $_POST['email'],
        $password_hash,
        $_POST['role_id'],
        $_POST['id']
      ]);
    } else {
      $stmt = $pdo->prepare("UPDATE users SET username=?, email=?, role_id=? WHERE id=?");
      $stmt->execute([
        $_POST['username'],
        $_POST['email'],
        $_POST['role_id'],
        $_POST['id']
      ]);
    }
    header('Location: users.php');
    exit;
  }
}

if ($action === 'delete') {
  $stmt = $pdo->prepare("DELETE FROM users WHERE id=?");
  $stmt->execute([$_GET['id']]);
  header('Location: users.php');
  exit;
}

$roles = $pdo->query("SELECT * FROM roles")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Manajemen Pengguna - Admin Panel</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
   background: url('https://i.pinimg.com/736x/a6/31/0c/a6310c1f30b0dc1d2291c91a066e7f82.jpg') no-repeat center center fixed !important;
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
    <h1>Manajemen Pengguna</h1>
    <a href="admin_dashboard.php" class="btn btn-light mb-3">Kembali ke Dashboard</a>
    <a href="users.php" class="btn btn-secondary mb-3">Daftar Pengguna</a>
    <?php if ($action == 'list'): ?>
      <a href="users.php?action=create" class="btn btn-primary mb-3">Tambah Pengguna</a>
      <table class="table table-bordered table-hover text-white align-middle">
        <thead>
          <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Email</th>
            <th>Role</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $users = $pdo->query("SELECT u.*, r.name AS role_name FROM users u LEFT JOIN roles r ON u.role_id = r.id")->fetchAll();
          foreach ($users as $user):
          ?>
            <tr>
              <td><?= $user['id'] ?></td>
              <td><?= htmlspecialchars($user['username']) ?></td>
              <td><?= htmlspecialchars($user['email']) ?></td>
              <td><?= htmlspecialchars($user['role_name']) ?></td>
              <td>
                <a href="users.php?action=edit&id=<?= $user['id'] ?>" class="btn btn-sm btn-warning text-dark">Edit</a>
                <a href="users.php?action=delete&id=<?= $user['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus pengguna?')">Hapus</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

    <?php elseif ($action == 'create' || $action == 'edit'):
      $user = ['id' => '', 'username' => '', 'email' => '', 'role_id' => 2, 'password' => ''];
      if ($action == 'edit') {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
        $stmt->execute([$_GET['id']]);
        $user = $stmt->fetch();
        if (!$user) {
          echo "<div class='alert alert-danger'>Pengguna tidak ditemukan.</div>";
          exit;
        }
      }
    ?>
      <form method="POST" class="mb-5">
        <?php if ($action == 'edit'): ?>
          <input type="hidden" name="id" value="<?= $user['id'] ?>">
        <?php endif; ?>
        <div class="mb-3">
          <label class="form-label">Username</label>
          <input type="text" name="username" class="form-control" required value="<?= htmlspecialchars($user['username']) ?>">
        </div>
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($user['email']) ?>">
        </div>
        <div class="mb-3">
          <label class="form-label"><?= $action == 'edit' ? 'Password (kosongkan jika tidak ingin mengganti)' : 'Password' ?></label>
          <input type="password" name="password" class="form-control" <?= $action == 'create' ? 'required' : '' ?>>
        </div>
        <div class="mb-3">
          <label class="form-label">Role</label>
          <select name="role_id" class="form-select" required>
            <?php foreach ($roles as $role): ?>
              <?php if ($role['id'] == 1 || $role['id'] == 2): ?>
                <option value="<?= $role['id'] ?>" <?= $role['id'] == $user['role_id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($role['name']) ?>
                </option>
              <?php endif; ?>
            <?php endforeach; ?>
          </select>
        </div>
        <button type="submit" class="btn btn-success"><?= $action == 'create' ? 'Tambah' : 'Update' ?></button>
        <a href="users.php" class="btn btn-secondary">Batal</a>
      </form>
    <?php endif; ?>
  </div>
</body>

</html>