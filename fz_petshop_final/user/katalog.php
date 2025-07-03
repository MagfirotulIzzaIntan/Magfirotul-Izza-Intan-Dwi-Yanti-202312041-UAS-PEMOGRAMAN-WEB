<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role_id'], [2, 3, 4])) {
  header('Location: ../auth/login.php');
  exit;
}

// Ambil semua kategori terlebih dahulu
$stmtCat = $pdo->prepare("SELECT * FROM categories ORDER BY name ASC");
$stmtCat->execute();
$categories = $stmtCat->fetchAll(PDO::FETCH_ASSOC);

// Cek filter kategori
if (isset($_GET['category']) && is_numeric($_GET['category'])) {
  $catId = $_GET['category'];
  $stmt = $pdo->prepare("SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.status = 1 AND p.category_id = ? ORDER BY p.name ASC");
  $stmt->execute([$catId]);
  $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
  $stmt = $pdo->prepare("SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.status = 1 ORDER BY p.name ASC");
  $stmt->execute();
  $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Katalog Produk</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <style>
    body {
      background: url('https://i.pinimg.com/736x/3f/22/7c/3f227cfa485f9239e38b9f986be19809.jpg') no-repeat center center fixed;
      background-size: cover;
      font-family: Arial, sans-serif;
      color: white;
    }
    .text-black-bold-large {
      color: #000000;
      font-size: 2.5rem;
      font-weight: bold;
    }
    .text-black-bold {
      color: #000000;
      font-weight: bold;
    }
    .bg-maroon {
      background: #4d0000 !important;
    }
    .card {
      border: none;
      box-shadow: 0 2px 8px rgba(128, 0, 0, 0.10);
      transition: transform 0.2s, box-shadow 0.2s;
    }
    .card:hover {
      transform: translateY(-7px) scale(1.03);
      box-shadow: 0 8px 20px rgba(128, 0, 0, 0.21);
    }
    .card-img-top {
      object-fit: cover;
      height: 180px;
      background: #b30000;
      border-top-left-radius: 0.5rem;
      border-top-right-radius: 0.5rem;
    }
    .list-group-item {
      background: #330000;
      color: #fff;
      border: 1px solid #b30000;
      transition: background 0.2s;
    }
    .list-group-item a {
      color: #fff;
      transition: color 0.2s;
    }
    .list-group-item:hover,
    .list-group-item.active {
      background: #b30000;
      color: #fff;
    }
    .btn-outline-light {
      border-radius: 0;
      border-color: #ff69b4;
      color: #ff69b4;
    }
    .btn-outline-light:hover,
    .btn-outline-light:focus {
      background: #ff69b4;
      color: #800000;
      border-color: #ff69b4;
    }
    .btn-detail:hover,
    .btn-detail:focus {
      background-color: #ffc0cb;
      color: #800000;
    }
    .btn-secondary,
    .btn-secondary:focus {
      background: #b30000;
      color: #fff;
      border: none;
    }
    .btn-secondary:hover {
      background: #800000;
    }
    @media (max-width: 767px) {
      .card-img-top {
        height: 140px;
      }
      .text-black-bold-large {
        font-size: 2rem;
      }
    }
  </style>
</head>
<body>
  <div class="container my-5">
    <h2 class="mb-4 text-black-bold-large">Katalog Produk</h2>
    <div class="row">
      <div class="col-md-3 mb-4">
        <h5 class="text-black-bold">Kategori</h5>
        <ul class="list-group mb-4">
          <li class="list-group-item<?php echo (empty($_GET['category'])) ? ' active' : ''; ?>"
              style="cursor:pointer;"
              onclick="window.location='katalog.php'">
            <span class="text-decoration-none<?php echo (empty($_GET['category'])) ? ' fw-bold' : ''; ?>">Semua</span>
          </li>
          <?php foreach ($categories as $cat): ?>
            <li class="list-group-item<?php echo (isset($_GET['category']) && $_GET['category'] == $cat['id']) ? ' active' : ''; ?>"
                style="cursor:pointer;"
                onclick="window.location='katalog.php?category=<?php echo $cat['id']; ?>'">
              <span class="text-decoration-none<?php echo (isset($_GET['category']) && $_GET['category'] == $cat['id']) ? ' fw-bold' : ''; ?>">
                <?php echo htmlspecialchars($cat['name']); ?>
              </span>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
      <div class="col-md-9">
        <div class="row">
          <?php if ($products): ?>
            <?php foreach ($products as $product): ?>
              <div class="col-md-4 mb-4">
                <div class="card h-100 bg-maroon text-light">
                  <img src="../assets/images/<?php echo htmlspecialchars($product['image']); ?>"
                       class="card-img-top"
                       alt="<?php echo htmlspecialchars($product['name']); ?>">
                  <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                    <span class="badge bg-pink mb-2" style="background-color:#ff69b4;color:#800000"><?php echo htmlspecialchars($product['category_name']); ?></span>
                    <p class="card-text" style="min-height:44px;"><?php echo substr(strip_tags($product['description']), 0, 80); ?>...</p>
                    <p class="card-text fw-bold text-warning">Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></p>
                    <a href="detail_product.php?id=<?php echo $product['id']; ?>" class="btn btn-outline-light btn-sm mt-2">Detail</a>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="col-12">
              <p class="text-light">Produk tidak ditemukan.</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <div class="text-center mt-5">
      <a href="index.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali ke Dashboard</a>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
