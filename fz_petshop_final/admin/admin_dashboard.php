<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header('Location: auth/login.php');
    exit;
}
include '../config/db.php';
$username = $_SESSION['username'];

// Ambil statistik otomatis dari database
$totalProduk = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalKategori = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$totalPesanan = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalPelanggan = $pdo->query("SELECT COUNT(*) FROM users WHERE role_id = 2")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: #800000;
            color: #fff;
            min-height: 100vh;
        }

        .sidebar {
            background: #4d0000;
            width: 250px;
            min-height: 100vh;
            padding: 24px 16px 16px 16px;
            position: fixed;
            left: 0;
            top: 0;
            box-shadow: 2px 0 12px rgba(0, 0, 0, .18);
            z-index: 1030;
            transition: left 0.3s;
        }

        .sidebar h4 {
            color: #ff69b4;
            margin-bottom: 30px;
            letter-spacing: 1.5px;
        }

        .sidebar a {
            display: flex;
            align-items: center;
            color: #fff;
            font-size: 1.05rem;
            padding: 10px 12px;
            border-radius: 7px;
            margin-bottom: 4px;
            transition: background .16s, color .16s;
            text-decoration: none;
        }

        .sidebar a i {
            margin-right: 9px;
            font-size: 1.2em;
        }

        .sidebar a.active,
        .sidebar a:hover {
            background: #b30000;
            color: #fff;
        }

        .main {
            margin-left: 255px;
            padding: 40px 32px 32px 32px;
            min-height: 100vh;
            background: linear-gradient(135deg, #800000 80%, #b30000);
            transition: margin-left 0.3s;
        }

        .dashboard-header {
            display: flex;
            align-items: center;
            gap: 18px;
            margin-bottom: 28px;
        }

        .dashboard-header i {
            font-size: 2.4em;
            color: #ff69b4;
        }

        .welcome {
            font-size: 1.3em;
            font-weight: bold;
            color: #ff69b4;
            margin-bottom: 7px;
        }

        .quick-stats-row {
            row-gap: 25px;
        }

        .stat-card {
            background: #b30000;
            color: #fff;
            border-radius: 14px;
            padding: 26px 20px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(128, 0, 0, .10);
            transition: transform .12s;
        }

        .stat-card:hover {
            transform: scale(1.035);
            box-shadow: 0 6px 18px #ff336640;
        }

        .stat-card .bi {
            font-size: 2.1em;
            margin-bottom: 10px;
            color: #ff69b4;
        }

        .stat-label {
            font-size: 1.03em;
            margin-top: 9px;
            letter-spacing: .9px;
        }

        .stat-value {
            font-size: 1.6em;
            font-weight: bold;
            color: #ffe5ec;
        }

        .dashboard-info {
            margin-top: 36px;
            background: #4d0000;
            padding: 28px 22px;
            border-radius: 12px;
            font-size: 1.06em;
            color: #fff;
            box-shadow: 0 1px 7px rgba(255, 105, 180, .08);
        }

        .sidebar-toggle {
            display: none;
            position: fixed;
            top: 15px;
            left: 15px;
            background: #b30000;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 8px 12px;
            z-index: 1050;
            font-size: 1.2em;
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
    <button class="sidebar-toggle" type="button" onclick="toggleSidebar()" aria-label="Buka menu"><i class="bi bi-list"></i></button>
    <div class="sidebar" id="sidebar">
        <h4>Admin Panel</h4>
        <a href="admin_dashboard.php" class="active"><i class="bi bi-speedometer2"></i> Dashboard</a>
        <a href="products.php"><i class="bi bi-box-seam"></i> Produk</a>
        <a href="categories.php"><i class="bi bi-tags"></i> Kategori</a>
        <a href="orders.php"><i class="bi bi-receipt"></i> Pesanan</a>
        <a href="payments.php"><i class="bi bi-credit-card"></i> Pembayaran</a>
        <a href="shippings.php"><i class="bi bi-truck"></i> Pengiriman</a>
        <a href="customers.php"><i class="bi bi-person-lines-fill"></i> Pelanggan</a>
        <a href="users.php"><i class="bi bi-people"></i> Pengguna</a>
        <a href="reports.php"><i class="bi bi-bar-chart"></i> Laporan</a>
        <a href="blogs.php"><i class="bi bi-journal-text"></i> Blog</a>
        <a href="../auth/logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </div>
    <div class="main">
        <div class="dashboard-header">
            <i class="bi bi-speedometer2"></i>
            <div>
                <div class="welcome">Selamat datang, <?= htmlspecialchars($username) ?>!</div>
                <div>Anda login sebagai <span class="fw-bold">Admin</span>.</div>
            </div>
        </div>
        <!-- Quick Stats -->
        <div class="row quick-stats-row mb-4">
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <i class="bi bi-box-seam"></i>
                    <div class="stat-value">
                        <?= number_format($totalProduk) ?>
                    </div>
                    <div class="stat-label">Produk</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <i class="bi bi-tags"></i>
                    <div class="stat-value">
                        <?= number_format($totalKategori) ?>
                    </div>
                    <div class="stat-label">Kategori</div>
                </div>
            </div>
            <div class="col-6 col-md-3 mt-4 mt-md-0">
                <div class="stat-card">
                    <i class="bi bi-receipt"></i>
                    <div class="stat-value">
                        <?= number_format($totalPesanan) ?>
                    </div>
                    <div class="stat-label">Total Pesanan</div>
                </div>
            </div>
            <div class="col-6 col-md-3 mt-4 mt-md-0">
                <div class="stat-card">
                    <i class="bi bi-people"></i>
                    <div class="stat-value">
                        <?= number_format($totalPelanggan) ?>
                    </div>
                    <div class="stat-label">Pelanggan</div>
                </div>
            </div>
        </div>
        <div class="dashboard-info">
            <h5 style="color:#ff69b4;font-weight:bold;">Petunjuk</h5>
            <ul class="mb-0">
                <li>Gunakan menu di samping untuk mengelola data Produk, Pesanan, Pengguna, dan lainnya.</li>
                <li>Statistik di atas membantu Anda memantau aktivitas toko.</li>
                <li>Pastikan selalu logout setelah selesai menggunakan sistem.</li>
            </ul>
        </div>
    </div>
    <script>
        function toggleSidebar() {
            var sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('open');
        }
        // Tutup sidebar saat klik di luar (mobile)
        document.addEventListener('click', function(e) {
            var sidebar = document.getElementById('sidebar');
            var btn = document.querySelector('.sidebar-toggle');
            if (window.innerWidth <= 991 && sidebar.classList.contains('open')) {
                if (!sidebar.contains(e.target) && !btn.contains(e.target)) {
                    sidebar.classList.remove('open');
                }
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>