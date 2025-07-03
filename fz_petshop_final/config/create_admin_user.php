<?php
require_once 'config/db.php';

$username = 'admin';
$password_plain = 'admin123';
$role_id = 1;
$email = 'admin@example.com';
$phone = '08123456789';

// Hash password yang benar
$password_hash = password_hash($password_plain, PASSWORD_DEFAULT);

// Hapus dulu user dengan username sama jika ada (opsional)
$stmt = $pdo->prepare("DELETE FROM users WHERE username = ?");
$stmt->execute([$username]);

// Insert user baru dengan password hash yang benar
$stmt = $pdo->prepare("INSERT INTO users (username, password, role_id, email, phone) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$username, $password_hash, $role_id, $email, $phone]);

echo "User admin berhasil dibuat dengan password hash yang benar.";
