<?php
session_start();
include '../config/db.php'; // pastikan path sesuai

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // Cek apakah username sudah ada
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        $error = "Username sudah terdaftar.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role_id, email, phone) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$username, $password, 2, $email, $phone]);

        // Redirect ke login setelah berhasil
        header('Location: login.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Sign Up - FZ Petshop</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: url('https://placekitten.com/1200/800') no-repeat center center fixed;
            background-size: cover;
            margin: 0;
            padding: 0;
        }

        .overlay {
            background-color: rgba(128, 0, 0, 0.85);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 0 10px;
        }

        .card-signup {
            background-color: rgba(153, 0, 0, 0.92);
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

        .btn-signup {
            background-color: #ff3366;
            border: none;
            color: #fff;
            border-radius: 7px;
            font-weight: 600;
            box-shadow: 0 1px 7px #ff336622;
        }

        .btn-signup:hover {
            background-color: #ff6688;
        }

        .login-link {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #ffc0cb;
            text-decoration: none;
        }

        .login-link:hover {
            text-decoration: underline;
            color: #fff;
        }

        @media (max-width: 575.98px) {
            .overlay {
                padding-left: 7px;
                padding-right: 7px;
            }

            .card-signup {
                padding: 23px 8px 18px 8px;
                max-width: 97vw;
            }

            h3 {
                font-size: 1.21em;
            }
        }

        @media (max-width: 350px) {
            .card-signup {
                padding: 10px 2px;
            }
        }
    </style>
</head>

<body>
    <div class="overlay">
        <div class="card-signup">
            <h3 class="text-center mb-4">Buat Akun FZ Petshop</h3>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="POST" autocomplete="off">
                <div class="mb-3">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>No. HP</label>
                    <input type="text" name="phone" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-signup w-100">Daftar</button>
            </form>
            <a href="login.php" class="login-link">Sudah punya akun? Login di sini</a>
        </div>
    </div>
</body>

</html>