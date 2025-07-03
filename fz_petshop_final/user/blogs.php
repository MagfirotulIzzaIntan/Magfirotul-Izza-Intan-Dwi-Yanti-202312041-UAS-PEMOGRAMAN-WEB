<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role_id'], [2, 3, 4])) {
  header('Location: ../auth/login.php');
  exit;
}
include '../config/db.php'; // perbaiki path di sini

$blogs = $pdo->query("SELECT * FROM blogs ORDER BY created_at DESC")->fetchAll();

$comments = [
  1 => [
    ['username' => 'Andi', 'comment' => 'Artikel sangat bermanfaat!'],
    ['username' => 'Budi', 'comment' => 'Terima kasih tipsnya.']
  ],
  2 => [
    ['username' => 'Sari', 'comment' => 'Saya suka artikel ini.']
  ]
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['blog_id'], $_POST['comment'])) {
  $blog_id = (int)$_POST['blog_id'];
  $username = $_SESSION['username'] ?? 'User';
  $comment = htmlspecialchars($_POST['comment']);
  // Simulasi: tampilkan komentar baru di reload berikutnya (tidak persist)
  $comments[$blog_id][] = ['username' => $username, 'comment' => $comment];
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Artikel Blog - User Panel</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <style>
    body {
         /* Background image dari URL */
      background: url('https://i.pinimg.com/736x/b2/75/e8/b275e8cca8f99f4ebf32dc675e7268a2.jpg') no-repeat center center fixed;
      background-size: cover;
      font-family: Arial, sans-serif;
      color: white;
    }

    .container {
      max-width: 900px;
      margin-top: 20px;
    }

    a,
    a:hover {
      color: #fff;
      text-decoration: none;
    }

    .blog-content {
      background: #330000;
      color: #fff;
      padding: 18px 20px;
      border-radius: 8px;
      margin-bottom: 24px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .comment-box {
      background: #222;
      color: #fff;
      padding: 10px 12px;
      border-radius: 6px;
      margin-bottom: 6px;
    }

    .btn-sm {
      margin-right: 5px;
    }

    textarea.form-control {
      min-height: 60px;
      resize: vertical;
    }

    .blog-content h4 {
      margin-bottom: 6px;
    }

    .blog-content small {
      color: #ffcccc;
    }

    .blog-content .mt-2 {
      margin-top: 12px !important;
    }

    .blog-content .mt-3 {
      margin-top: 18px !important;
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
  </style>
</head>

<body>
  <div class="container">
    <h1>Artikel Blog</h1>
    <a href="index.php" class="btn btn-secondary mb-3"><i class="bi bi-arrow-left"></i> Kembali ke Dashboard</a>
    <?php foreach ($blogs as $blog): ?>
      <div class="blog-content mb-4">
        <h4><?= htmlspecialchars($blog['title']) ?></h4>
        <div>
          <small>
            Kategori: <?= htmlspecialchars($blog['category']) ?> | Dibuat: <?= $blog['created_at'] ?>
            <?php
            // Simulasi tag: ambil kata diawali # dari konten
            preg_match_all('/#(\w+)/', $blog['content'], $matches);
            if (!empty($matches[0])) {
              echo ' | Tag: ' . implode(', ', array_map('htmlspecialchars', $matches[0]));
            }
            ?>
          </small>
        </div>
        <div class="mt-2"><?= nl2br(htmlspecialchars($blog['content'])) ?></div>
        <!-- Share ke media sosial -->
        <div class="mt-2">
          <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode('https://domainanda.com/blog.php?id=' . $blog['id']) ?>" target="_blank" class="btn btn-sm btn-primary">Share FB</a>
          <a href="https://twitter.com/intent/tweet?url=<?= urlencode('https://domainanda.com/blog.php?id=' . $blog['id']) ?>&text=<?= urlencode($blog['title']) ?>" target="_blank" class="btn btn-sm btn-info">Share Twitter</a>
        </div>
        <!-- Komentar -->
        <div class="mt-3">
          <b>Komentar:</b>
          <?php if (!empty($comments[$blog['id']])): ?>
            <?php foreach ($comments[$blog['id']] as $c): ?>
              <div class="comment-box">
                <b><?= htmlspecialchars($c['username']) ?>:</b> <?= nl2br(htmlspecialchars($c['comment'])) ?>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="text-muted">Belum ada komentar.</div>
          <?php endif; ?>
          <!-- Form komentar -->
          <form method="POST" class="mt-2">
            <input type="hidden" name="blog_id" value="<?= $blog['id'] ?>">
            <div class="mb-2">
              <textarea name="comment" class="form-control" rows="2" placeholder="Tulis komentar..." required></textarea>
            </div>
            <button type="submit" class="btn btn-success btn-sm">Kirim Komentar</button>
          </form>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>