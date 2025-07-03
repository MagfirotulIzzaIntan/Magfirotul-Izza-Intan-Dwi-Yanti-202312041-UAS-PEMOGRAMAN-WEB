<?php
session_start();
require_once '../config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Username dan password wajib diisi.';
    } else {
        // Ambil user berdasar username
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user) {
            // Verifikasi password
            if (password_verify($password, $user['password'])) {
                // Simpan session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role_id'] = $user['role_id'];

                // Redirect berdasarkan username
                if ($user['role_id'] == 1) {
                    header('Location: ../admin/admin_dashboard.php');
                } else {
                    header('Location: ../user/index.php');
                }
                exit;
            } else {
                $error = 'Password salah.';
            }
        } else {
            $error = 'Username tidak ditemukan.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Login FZ Petshop</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: url('https://i.pinimg.com/736x/b5/12/6f/b5126f6038d6c3e60399ded972e2d918.jpg') no-repeat center center fixed;
            background-size: cover;
            margin: 0;
            padding: 0;
        }

        .overlay {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .card-login {
            background-color: rgba(153, 0, 0, 0.93);
            border: none;
            padding: 34px 38px 30px 38px;
            color: #fff;
            max-width: 410px;
            width: 100%;
            border-radius: 11px;
            box-shadow: 0 2px 18px rgba(128, 0, 0, .13);
        }

        .form-control {
            background-color: #fff;
            color: #000;
            border-radius: 7px;
        }

        .btn-login {
            background-color: #ff3366;
            border: none;
            color: #fff;
            border-radius: 7px;
            font-weight: 600;
            box-shadow: 0 1px 7px #ff336622;
        }

        .btn-login:hover {
            background-color: #ff6688;
        }

        .signup-link {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #ffc0cb;
            text-decoration: none;
        }

        .signup-link:hover {
            text-decoration: underline;
            color: #fff;
        }

        @media (max-width: 575.98px) {
            .overlay {
                padding-left: 7px;
                padding-right: 7px;
            }

            .card-login {
                padding: 23px 8px 18px 8px;
                max-width: 97vw;
            }

            h3 {
                font-size: 1.21em;
            }
        }

        @media (max-width: 350px) {
            .card-login {
                padding: 10px 2px;
            }
        }
    </style>
</head>

<body>
    <div class="overlay">
        <div class="card-login">
            <h3 class="text-center mb-4">Selamat Datang di FZ Petshop</h3>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlentities($error) ?></div>
            <?php endif; ?>
            <form method="post" autocomplete="off">
                <div class="mb-3">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control" required autofocus>
                </div>
                <div class="mb-3">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-login w-100">Login</button>
            </form>
            <a href="signup.php" class="signup-link">Belum punya akun? Daftar di sini</a>
        </div>
    </div>
</body>

</html>