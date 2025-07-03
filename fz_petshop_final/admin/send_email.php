<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header('Location: ../auth/login.php');
    exit;
}
include '../config/db.php';

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT username, email FROM users WHERE id=?");
$stmt->execute([$id]);
$customer = $stmt->fetch();

if (!$customer) {
    echo "<div class='alert alert-danger'>Pelanggan tidak ditemukan.</div>";
    exit;
}

$status = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $to = $customer['email'];
    $subject = $_POST['subject'];
    $message = $_POST['message'];
    $headers = "From: admin@domainanda.com\r\n";
    $headers .= "Content-type: text/plain; charset=UTF-8\r\n";

    // Fungsi mail() hanya akan bekerja jika server Anda support email
    if (mail($to, $subject, $message, $headers)) {
        $status = '<div class="alert alert-success">Email berhasil dikirim ke ' . htmlspecialchars($customer['email']) . '</div>';
    } else {
        $status = '<div class="alert alert-danger">Gagal mengirim email.</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Kirim Email ke <?= htmlspecialchars($customer['username']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            background: #800000;
            color: #fff;
        }

        .container {
            max-width: 600px;
            margin-top: 30px;
        }

        .form-control,
        .form-select textarea,
        textarea {
            background: #330000;
            color: #fff;
            border: 1px solid #b30000;
            /* Tambahkan ini agar placeholder juga putih */
            caret-color: #fff;
        }

        .form-control::placeholder,
        textarea::placeholder {
            color: #fff !important;
            opacity: 1;
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

        a,
        a:hover {
            color: #fff;
            text-decoration: none;
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
    <div class="container">
        <h2>Kirim Email ke Pelanggan</h2>
        <div class="mb-3">
            <strong>Nama:</strong> <?= htmlspecialchars($customer['username']) ?><br>
            <strong>Email:</strong> <?= htmlspecialchars($customer['email']) ?>
        </div>
        <a href="customers.php" class="btn btn-light mb-3">Kembali ke Daftar Pelanggan</a>
        <?= $status ?>
        <form method="POST" class="mb-4">
            <div class="mb-3">
                <label class="form-label">Subjek</label>
                <input type="text" name="subject" class="form-control" required placeholder="Contoh: Status Pesanan, Promo, Ucapan Selamat">
            </div>
            <div class="mb-3">
                <label class="form-label">Pesan</label>
                <textarea name="message" class="form-control" rows="6" required placeholder="Tulis pesan Anda di sini..."></textarea>
            </div>
            <button type="submit" class="btn btn-success">Kirim Email</button>
            <a href="customers.php" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</body>

</html>