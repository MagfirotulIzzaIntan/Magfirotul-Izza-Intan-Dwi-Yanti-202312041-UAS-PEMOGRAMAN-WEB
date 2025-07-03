<?php
session_start();
require_once '../config/db.php';

if (!isset($_GET['id'])) {
    header('Location: katalog.php');
    exit;
}
$id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    echo "<div class='alert alert-danger'>Produk tidak ditemukan.</div>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Detail Produk - <?= htmlspecialchars($product['name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            background: #800000;
            color: #fff;
            min-height: 100vh;
        }

        .card {
            background: #4d0000;
            color: #ffc0cb;
            border: none;
            border-radius: 8px;
        }

        .btn-back {
            background: #b30000;
            color: #fff;
        }

        .btn-back:hover {
            background: #ff69b4;
            color: #800000;
        }

        .detail-img {
            max-width: 100%;
            border-radius: 10px;
            max-height: 320px;
            object-fit: cover;
        }
    </style>
</head>

<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-7">
                <div class="card p-4">
                    <img src="../assets/images/<?= htmlspecialchars($product['image'] ?? 'default.png') ?>"
                        onerror="this.src='../assets/images/default.png';"
                        class="mb-4 detail-img"
                        alt="<?= htmlspecialchars($product['name']) ?>">
                    <h2><?= htmlspecialchars($product['name']) ?></h2>
                    <p><strong>Kategori:</strong> <?= htmlspecialchars($product['category_name'] ?? '-') ?></p>
                    <p><strong>Harga:</strong> Rp <?= number_format($product['price'], 0, ',', '.') ?></p>
                    <p><strong>Stok:</strong> <?= (int)$product['stock'] ?></p>
                    <p><strong>Deskripsi:</strong><br>
                        <?= nl2br(htmlspecialchars($product['description'])) ?>
                    </p>
                    <a href="katalog.php" class="btn btn-back mt-3">&laquo; Kembali ke Katalog</a>
                </div>
            </div>
        </div>
    </div>
</body>

</html>