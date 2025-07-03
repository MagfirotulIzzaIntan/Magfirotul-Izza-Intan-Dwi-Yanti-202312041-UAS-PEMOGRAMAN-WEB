<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role_id'], [2, 3, 4])) {
    header('Location: ../auth/login.php');
    exit;
}

$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

if ($product_id < 1) {
    header('Location: katalog.php');
    exit;
}

// Inisialisasi keranjang jika belum ada
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Kalau produk sudah ada di keranjang, tambahkan qty
if (isset($_SESSION['cart'][$product_id])) {
    $_SESSION['cart'][$product_id] += $quantity;
} else {
    $_SESSION['cart'][$product_id] = $quantity;
}

// Redirect ke checkout jika diminta
if (isset($_POST['redirect']) && $_POST['redirect'] == 'checkout') {
    header('Location: checkout.php');
} else {
    header('Location: katalog.php?msg=added');
}
exit;
