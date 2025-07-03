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
    $stmt = $pdo->prepare("INSERT INTO blogs (title, content, category, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
    $stmt->execute([
      $_POST['title'],
      $_POST['content'],
      $_POST['category']
    ]);
    header('Location: blogs.php');
    exit;
  }
  if ($action === 'edit') {
    $stmt = $pdo->prepare("UPDATE blogs SET title=?, content=?, category=?, updated_at=NOW() WHERE id=?");
    $stmt->execute([
      $_POST['title'],
      $_POST['content'],
      $_POST['category'],
      $_POST['id']
    ]);
    header('Location: blogs.php');
    exit;
  }
}

if ($action === 'delete') {
  $stmt = $pdo->prepare("DELETE FROM blogs WHERE id=?");
  $stmt->execute([$_GET['id']]);
  header('Location: blogs.php');
  exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Manajemen Blog - Admin Panel</title>
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

    a,
    a:hover {
      color: #fff;
      text-decoration: none;
    }

    .btn-primary,
    .btn-success,
    .btn-secondary {
      border-radius: 0;
    }

    .table {
      background: #4d0000;
    }

    .table thead {
      background: #b30000;
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
    <h1>Manajemen Blog</h1>
    <a href="admin_dashboard.php" class="btn btn-light mb-3">Kembali ke Dashboard</a>
    <a href="blogs.php" class="btn btn-secondary mb-3">Daftar Artikel</a>
    <?php if ($action == 'list'): ?>
      <a href="blogs.php?action=create" class="btn btn-primary mb-3">Tambah Artikel</a>
      <table class="table table-bordered table-hover text-white align-middle">
        <thead>
          <tr>
            <th>ID</th>
            <th>Judul</th>
            <th>Kategori</th>
            <th>Dibuat</th>
            <th>Diupdate</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $blogs = $pdo->query("SELECT * FROM blogs ORDER BY created_at DESC")->fetchAll();
          foreach ($blogs as $blog):
          ?>
            <tr>
              <td><?= $blog['id'] ?></td>
              <td><?= htmlspecialchars($blog['title']) ?></td>
              <td><?= htmlspecialchars($blog['category']) ?></td>
              <td><?= $blog['created_at'] ?></td>
              <td><?= $blog['updated_at'] ?></td>
              <td>
                <a href="blogs.php?action=edit&id=<?= $blog['id'] ?>" class="btn btn-sm btn-warning text-dark">Edit</a>
                <a href="blogs.php?action=delete&id=<?= $blog['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus artikel?')">Hapus</a>
                <!-- Tombol share artikel -->
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode('https://domainanda.com/blog.php?id=' . $blog['id']) ?>" target="_blank" class="btn btn-sm btn-primary">Share FB</a>
                <a href="https://twitter.com/intent/tweet?url=<?= urlencode('https://domainanda.com/blog.php?id=' . $blog['id']) ?>&text=<?= urlencode($blog['title']) ?>" target="_blank" class="btn btn-sm btn-info">Share Twitter</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

    <?php elseif ($action == 'create' || $action == 'edit'):
      $blog = ['id' => '', 'title' => '', 'content' => '', 'category' => ''];
      if ($action == 'edit') {
        $stmt = $pdo->prepare("SELECT * FROM blogs WHERE id=?");
        $stmt->execute([$_GET['id']]);
        $blog = $stmt->fetch();
        if (!$blog) {
          echo "<div class='alert alert-danger'>Artikel tidak ditemukan.</div>";
          exit;
        }
      }
    ?>
      <form method="POST" class="mb-5">
        <?php if ($action == 'edit'): ?>
          <input type="hidden" name="id" value="<?= $blog['id'] ?>">
        <?php endif; ?>
        <div class="mb-3">
          <label class="form-label">Judul</label>
          <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($blog['title']) ?>">
        </div>
        <div class="mb-3">
          <label class="form-label">Kategori</label>
          <input type="text" name="category" class="form-control" value="<?= htmlspecialchars($blog['category']) ?>">
        </div>
        <div class="mb-3">
          <label class="form-label">Konten</label>
          <textarea name="content" class="form-control" rows="8" required><?= htmlspecialchars($blog['content']) ?></textarea>
        </div>
        <button type="submit" class="btn btn-success"><?= $action == 'create' ? 'Tambah' : 'Update' ?></button>
        <a href="blogs.php" class="btn btn-secondary">Batal</a>
      </form>
    <?php endif; ?>
  </div>
</body>

</html>