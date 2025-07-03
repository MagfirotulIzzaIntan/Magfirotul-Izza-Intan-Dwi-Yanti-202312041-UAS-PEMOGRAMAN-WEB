<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role_id'], [2, 3, 4])) {
    header('Location: ../auth/login.php');
    exit;
}
$user_id = $_SESSION['user_id'];
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];

$products = [];
$total = 0;

if ($cart) {
    $ids = implode(',', array_map('intval', array_keys($cart)));
    $stmt = $pdo->query("SELECT * FROM products WHERE id IN ($ids)");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($products as $product) {
        $qty = $cart[$product['id']];
        $total += $product['price'] * $qty;
    }
}

$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $cart) {
    $alamat = trim($_POST['alamat'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $order_code = 'ORD' . date('YmdHis') . rand(10, 99);
    $status = 'Menunggu Pembayaran';

    $stmt = $pdo->prepare("INSERT INTO orders (user_id, order_code, total_amount, status, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$user_id, $order_code, $total, $status]);
    $order_id = $pdo->lastInsertId();

    foreach ($products as $product) {
        $qty = $cart[$product['id']];
        $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $stmt->execute([$order_id, $product['id'], $qty, $product['price']]);
    }

    unset($_SESSION['cart']);
    $success = true;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Checkout - FZ Petshop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <style>
        body {
         /* Background image dari URL */
      background: url('hhttps://www.purina.co.id/sites/default/files/2024-09/Cara%20Menggemukkan%20Kucing%20Makanan%20dan%20Perawatan%20yang%20Tepat.jpg') no-repeat center center fixed;
      background-size: cover;
      font-family: Arial, sans-serif;
      color: white;
    }
        .container {
            max-width: 900px;
            margin-top: 40px;
            margin-bottom: 40px;
        }

        .card {
            background: #4d0000;
            color: #fff;
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(128, 0, 0, 0.10);
        }

        .card-header {
            background: #b30000;
            color: #fff;
            border-bottom: 1px solid #ff69b4;
        }

        .btn-pink {
            background: #ff69b4;
            color: #800000;
            border: none;
        }

        .btn-pink:hover,
        .btn-pink:focus {
            background: #ffc0cb;
            color: #800000;
        }

        .form-control,
        textarea {
            background: #330000;
            color: #fff;
            border: 1px solid #b30000;
        }

        .form-control:focus,
        textarea:focus {
            background: #4d0000;
            color: #fff;
            border-color: #ff69b4;
            box-shadow: none;
        }

        .table {
            color: #fff;
            background: #4d0000;
        }

        .table th,
        .table td {
            border-color: #b30000;
        }

        .table thead {
            background: #b30000;
        }

        .alert-success {
            background: #ff69b4;
            color: #800000;
            border: none;
        }

        .badge-pink {
            background: #ff69b4;
            color: #800000;
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
            .container {
                padding: 0 9px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h2 class="mb-4">Checkout</h2>
        <?php if ($success): ?>
            <div class="alert alert-success">
                <h5 class="mb-2">Pesanan berhasil dibuat!</h5>
                <div>Kode Pesanan Anda: <span class="fw-bold badge badge-pink"><?= htmlspecialchars($order_code) ?></span></div>
                <div class="mt-2">Silakan lakukan pembayaran ke rekening yang tertera pada halaman pembayaran/riwayat pesanan Anda.</div>
                <a href="orders.php" class="btn btn-pink mt-3">Lihat Riwayat Pesanan</a>
            </div>
        <?php elseif (!$cart): ?>
            <div class="alert alert-warning text-dark">
                Keranjang belanja Anda kosong.<br>
                <a href="katalog.php" class="btn btn-sm btn-pink mt-2">Kembali ke Katalog</a>
            </div>
        <?php else: ?>
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-cart"></i> Ringkasan Keranjang
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered m-0">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th>Harga</th>
                                    <th>Jumlah</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $product):
                                    $qty = $cart[$product['id']];
                                    $subtotal = $product['price'] * $qty; ?>
                                    <tr>
                                        <td>
                                            <b><?= htmlspecialchars($product['name']) ?></b>
                                        </td>
                                        <td>Rp <?= number_format($product['price'], 0, ',', '.') ?></td>
                                        <td><?= $qty ?></td>
                                        <td>Rp <?= number_format($subtotal, 0, ',', '.') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">Total</td>
                                    <td class="fw-bold">Rp <?= number_format($total, 0, ',', '.') ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <form method="POST" class="mb-4">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-person"></i> Data Pengiriman
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Alamat Pengiriman</label>
                            <textarea name="alamat" class="form-control" rows="3" required placeholder="Tuliskan alamat lengkap pengiriman..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">No. HP/WA</label>
                            <input type="text" name="phone" class="form-control" required placeholder="0812xxxxxxx">
                        </div>
                        <button type="submit" class="btn btn-pink"><i class="bi bi-credit-card"></i> Konfirmasi & Pesan Sekarang</button>
                    </div>
                </div>
            </form>
        <?php endif; ?>
        <div class="text-center mt-5">
            <a href="products.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali ke Produk</a>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>