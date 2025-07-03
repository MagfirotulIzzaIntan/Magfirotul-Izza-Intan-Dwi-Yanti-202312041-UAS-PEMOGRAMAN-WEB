<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>FZ Petshop Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
      background: linear-gradient(45deg, #800000, #b22222);
      color: #fff;
    }

    .navbar,
    .footer {
      background-color: #5a0000;
    }

    a,
    a:hover {
      color: #ffc0cb;
    }
  </style>
</head>

<body>
  <nav class="navbar navbar-expand-lg navbar-dark mb-4" style="margin-top: -18px;">
    <div class="container">
      <a class="navbar-brand" href="admin_dashboard.php">FZ Petshop Admin</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navmenu">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navmenu">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link" href="products.php">Produk</a></li>
          <li class="nav-item"><a class="nav-link" href="categories.php">Kategori</a></li>
          <li class="nav-item"><a class="nav-link" href="orders.php">Pesanan</a></li>
          <li class="nav-item"><a class="nav-link" href="payments.php">Pembayaran</a></li>
          <li class="nav-item"><a class="nav-link" href="shippings.php">Pengiriman</a></li>
          <li class="nav-item"><a class="nav-link" href="customers.php">Pelanggan</a></li>
          <li class="nav-item"><a class="nav-link" href="users.php">Pengguna</a></li>
          <li class="nav-item"><a class="nav-link" href="reports.php">Laporan</a></li>
          <li class="nav-item"><a class="nav-link" href="blogs.php">Blog</a></li>
        </ul>
      </div>
    </div>
  </nav>