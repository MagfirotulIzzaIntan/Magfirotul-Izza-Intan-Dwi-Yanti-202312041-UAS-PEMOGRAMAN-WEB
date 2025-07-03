<?php
require_once 'config/db.php';

$username = 'admin';
$password_input = 'admin123';

// Ambil data user dari database
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

if (!$user) {
    die("User tidak ditemukan");
}

echo "Password di DB: " . $user['password'] . "<br>";

// Cek password_verify
if (password_verify($password_input, $user['password'])) {
    echo "Password cocok! Login berhasil.";
} else {
    echo "Password TIDAK cocok!";
}
