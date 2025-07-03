<?php
session_start();

// Hanya user dengan role 2, 3, atau 4 yang bisa mengakses
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role_id'], [2, 3, 4])) {
    header('Location: ../auth/login.php');
    exit;
}

include '../config/db.php';

// Ambil produk aktif dari database
$products = $pdo->query("
    SELECT p.*, c.name AS category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.status = 1 
    ORDER BY p.name
")->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Daftar Produk - FZ Petshop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <style>
        body {
          /* Background image dari URL */
      background: url('https://i.pinimg.com/736x/16/d1/35/16d135669aea9a1ada3d8eb5206419c5.jpg') no-repeat center center fixed;
      background-size: cover;
      font-family: Arial, sans-serif;
      color: white;
    }

        .container {
            max-width: 1100px;
        }

        .card {
            background-color: #4d0000;
            border: none;
            border-radius: 12px;
            color: #ffc0cb;
            transition: transform 0.15s, box-shadow 0.15s;
            box-shadow: 0 2px 8px rgba(128, 0, 0, 0.09);
        }

        .card:hover {
            transform: translateY(-4px) scale(1.025);
            box-shadow: 0 8px 22px #ff336680;
        }

        .card-img-top {
            max-height: 180px;
            object-fit: cover;
            border-radius: 12px 12px 0 0;
            background: #fff;
        }

        .card-title {
            color: #ff69b4;
            font-size: 1.15rem;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .card-text {
            color: #fff;
            font-size: 0.96em;
            margin-bottom: 5px;
        }

        .price {
            font-weight: bold;
            color: #ffcccb;
            font-size: 1.08em;
            margin-bottom: 3px;
        }

        .stock {
            font-style: italic;
            color: #ffe5ec;
            font-size: 0.95em;
        }

        .btn-add {
            background-color: #cc0033;
            border: none;
            color: #fff;
            font-weight: 500;
            border-radius: 6px;
        }

        .btn-add:hover,
        .btn-add:focus {
            background-color: #ff3366;
            color: #800000;
        }

        .btn-detail {
            background-color: #ff69b4;
            color: #800000;
            border: none;
            font-weight: bold;
            border-radius: 6px;
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

        .form-control {
            background: #330000;
            color: #fff;
            border: 1px solid #b30000;
            border-radius: 6px;
        }

        .form-control:focus {
            background: #4d0000;
            border-color: #ff69b4;
            color: #fff;
        }

        @media (max-width: 767px) {
            .container {
                padding: 0 9px;
            }

            .card-title,
            .price {
                font-size: 1em;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h1 class="mb-4 text-center">Daftar Produk FZ Petshop</h1>
        <div class="row g-4">
            <?php if (count($products) === 0): ?>
                <div class="col-12">
                    <div class="alert alert-warning text-dark text-center mb-0">
                        <i class="bi bi-exclamation-circle"></i> Produk belum tersedia.
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($products as $p): ?>
                    <div class="col-sm-6 col-md-4 col-lg-3">
                        <div class="card h-100">
                            <img src="../assets/images/<?= htmlspecialchars($p['image'] ?? 'default.png') ?>"
                                class="card-img-top"
                                alt="<?= htmlspecialchars($p['name']) ?>"
                                onerror="this.src='../assets/images/default.png';">
                            <div class="card-body d-flex flex-column pb-2">
                                <h5 class="card-title"><?= htmlspecialchars($p['name']) ?></h5>
                                <div class="card-text mb-1">Kategori: <span style="color:#fff;"><?= htmlspecialchars($p['category_name'] ?? '-') ?></span></div>
                                <div class="price mb-1">Rp <?= number_format($p['price'], 0, ',', '.') ?></div>
                                <div class="stock mb-2">Stok: <?= $p['stock'] ?></div>
                                <div class="d-grid gap-2 mt-auto">
                                    <!-- <a href="detail_product.php?id=<?= $p['id'] ?>" class="btn btn-detail mb-2">
                                        <i class="bi bi-info-circle"></i> Detail
                                    </a> -->
                                    <?php if ($p['stock'] > 0): ?>
                                        <form action="add_to_cart.php" method="post" class="d-flex flex-column gap-2 align-items-stretch">
                                            <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                            <div class="input-group mb-2">
                                                <span class="input-group-text" style="background:#b30000;color:#fff;">Qty</span>
                                                <input type="number" name="quantity" value="1" min="1" max="<?= $p['stock'] ?>" class="form-control" style="width:90px;" required>
                                            </div>
                                            <input type="hidden" name="redirect" value="checkout">
                                            <button type="submit" class="btn btn-add">
                                                <i class="bi bi-cart-plus"></i> Tambah ke Keranjang
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-secondary" disabled>Stok Habis</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="text-center mt-5">
            <a href="index.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali ke Dashboard</a>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>