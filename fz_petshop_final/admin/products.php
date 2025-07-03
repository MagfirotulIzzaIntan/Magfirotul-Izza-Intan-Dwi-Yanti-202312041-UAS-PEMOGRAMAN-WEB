<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
  header('Location: ../auth/login.php');
  exit;
}
include '../config/db.php';

$action = $_GET['action'] ?? 'list';

function uploadImage($inputName, $oldImage = '')
{
  if (isset($_FILES[$inputName]) && $_FILES[$inputName]['error'] === UPLOAD_ERR_OK) {
    $ext = pathinfo($_FILES[$inputName]['name'], PATHINFO_EXTENSION);
    $newName = uniqid('img_', true) . '.' . strtolower($ext);
    $uploadDir = __DIR__ . '/../assets/images/';
    if (!is_dir($uploadDir)) {
      mkdir($uploadDir, 0777, true);
    }
    $target = $uploadDir . $newName;
    if (move_uploaded_file($_FILES[$inputName]['tmp_name'], $target)) {
      // Hapus gambar lama jika ada
      if ($oldImage && file_exists($uploadDir . $oldImage)) {
        unlink($uploadDir . $oldImage);
      }
      return $newName;
    }
  }
  return $oldImage;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if ($action === 'create') {
    $image = uploadImage('image');
    $stmt = $pdo->prepare("INSERT INTO products (category_id, name, description, price, stock, status, image) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
      $_POST['category_id'],
      $_POST['name'],
      $_POST['description'],
      $_POST['price'],
      $_POST['stock'],
      $_POST['status'],
      $image
    ]);
    header('Location: products.php');
    exit;
  }
  if ($action === 'edit') {
    $stmt = $pdo->prepare("SELECT image FROM products WHERE id=?");
    $stmt->execute([$_POST['id']]);
    $old = $stmt->fetch();
    $image = uploadImage('image', $old['image'] ?? '');
    $stmt = $pdo->prepare("UPDATE products SET category_id=?, name=?, description=?, price=?, stock=?, status=?, image=? WHERE id=?");
    $stmt->execute([
      $_POST['category_id'],
      $_POST['name'],
      $_POST['description'],
      $_POST['price'],
      $_POST['stock'],
      $_POST['status'],
      $image,
      $_POST['id']
    ]);
    header('Location: products.php');
    exit;
  }
}

if ($action === 'delete') {
  $stmt = $pdo->prepare("SELECT image FROM products WHERE id=?");
  $stmt->execute([$_GET['id']]);
  $old = $stmt->fetch();
  if ($old && !empty($old['image'])) {
    $imgPath = __DIR__ . '/../assets/images/' . $old['image'];
    if (file_exists($imgPath)) unlink($imgPath);
  }
  $stmt = $pdo->prepare("DELETE FROM products WHERE id=?");
  $stmt->execute([$_GET['id']]);
  header('Location: products.php');
  exit;
}

$categories = $pdo->query("SELECT * FROM categories")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Manajemen Produk - Admin Panel</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
  background: url('https://i.pinimg.com/736x/28/6e/4c/286e4c1cd7bfd14a027186b5236d983f.jpg') no-repeat center center fixed !important;
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
    <h1>Manajemen Produk</h1>
    <a href="admin_dashboard.php" class="btn btn-light mb-3">Kembali ke Dashboard</a>
    <a href="products.php" class="btn btn-secondary mb-3">Daftar Produk</a>
    <?php if ($action == 'list'): ?>
      <a href="products.php?action=create" class="btn btn-primary mb-3">Tambah Produk</a>
      <table class="table table-bordered table-hover text-white align-middle">
        <thead>
          <tr>
            <th>ID</th>
            <th>Nama</th>
            <th>Kategori</th>
            <th>Harga</th>
            <th>Stok</th>
            <th>Status</th>
            <th>Gambar</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $products = $pdo->query("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id")->fetchAll();
          foreach ($products as $p):
          ?>
            <tr>
              <td><?= $p['id'] ?></td>
              <td><?= htmlspecialchars($p['name']) ?></td>
              <td><?= htmlspecialchars($p['category_name']) ?></td>
              <td><?= number_format($p['price'], 2) ?></td>
              <td><?= $p['stock'] ?></td>
              <td><?= $p['status'] ? 'Aktif' : 'Nonaktif' ?></td>
              <td>
                <?php if (!empty($p['image'])): ?>
                  <img src="../assets/images/<?= htmlspecialchars($p['image']) ?>" alt="Gambar Produk" style="max-width:60px;max-height:60px;">
                <?php else: ?>
                  <span class="text-muted">-</span>
                <?php endif; ?>
              </td>
              <td>
                <a href="products.php?action=edit&id=<?= $p['id'] ?>" class="btn btn-sm btn-warning text-dark">Edit</a>
                <a href="products.php?action=delete&id=<?= $p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus produk?')">Hapus</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

    <?php elseif ($action == 'create' || $action == 'edit'):
      $product = ['id' => '', 'category_id' => '', 'name' => '', 'description' => '', 'price' => '', 'stock' => '', 'status' => 1, 'image' => ''];
      if ($action == 'edit') {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id=?");
        $stmt->execute([$_GET['id']]);
        $product = $stmt->fetch();
        if (!$product) {
          echo "<div class='alert alert-danger'>Produk tidak ditemukan.</div>";
          exit;
        }
      }
    ?>
      <form method="POST" class="mb-5" enctype="multipart/form-data">
        <?php if ($action == 'edit'): ?>
          <input type="hidden" name="id" value="<?= $product['id'] ?>">
        <?php endif; ?>
        <div class="mb-3">
          <label class="form-label">Kategori</label>
          <select name="category_id" class="form-select" required>
            <option value="">-- Pilih Kategori --</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $product['category_id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Nama Produk</label>
          <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($product['name']) ?>">
        </div>
        <div class="mb-3">
          <label class="form-label">Deskripsi</label>
          <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($product['description']) ?></textarea>
        </div>
        <div class="mb-3">
          <label class="form-label">Harga</label>
          <input type="number" step="0.01" name="price" class="form-control" required value="<?= $product['price'] ?>">
        </div>
        <div class="mb-3">
          <label class="form-label">Stok</label>
          <input type="number" name="stock" class="form-control" required value="<?= $product['stock'] ?>">
        </div>
        <div class="mb-3">
          <label class="form-label">Status</label>
          <select name="status" class="form-select" required>
            <option value="1" <?= $product['status'] ? 'selected' : '' ?>>Aktif</option>
            <option value="0" <?= !$product['status'] ? 'selected' : '' ?>>Nonaktif</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Gambar Produk</label>
          <input type="file" name="image" class="form-control" accept="image/*">
          <?php if (!empty($product['image'])): ?>
            <div class="mt-2">
              <img src="../assets/images/<?= htmlspecialchars($product['image']) ?>" alt="Gambar Produk" style="max-width:120px;">
            </div>
          <?php endif; ?>
        </div>
        <button type="submit" class="btn btn-success"><?= $action == 'create' ? 'Tambah' : 'Update' ?></button>
        <a href="products.php" class="btn btn-secondary">Batal</a>
      </form>
    <?php endif; ?>
  </div>
</body>

</html>