<?php
// public/update_password.php
/*
 * 🍩 Aplikasi Chatbot Toko Donat JLO Jakarta
 * (Melayani pertanyaan & pesanan donat secara otomatis)
 * Dibuat oleh: Kukuh TW
 *
 * 📧 Email     : kukuhtw@gmail.com 
 * 📱 WhatsApp  : https://wa.me/628129893706
 * 📷 Instagram : @kukuhtw
 * 🐦 X/Twitter : @kukuhtw
 * 👍 Facebook  : https://www.facebook.com/kukuhtw
 * 💼 LinkedIn  : https://id.linkedin.com/in/kukuhtw
*/
require __DIR__ . '/../bootstrap.php';
session_start();
if (empty($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}
$err  = [];
$succ = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = trim($_POST['oldpass'] ?? '');
    $new = trim($_POST['newpass'] ?? '');

    if (!$adminAuth->verify($_SESSION['admin'], $old)) {
        $err[] = 'Password lama salah.';
    } elseif (empty($new)) {
        $err[] = 'Password baru tidak boleh kosong.';
    } else {
        if ($adminAuth->createOrUpdatePassword($_SESSION['admin'], $new)) {
            $succ = 'Password berhasil diupdate.';
        } else {
            $err[] = 'Gagal melakukan update password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Update Password</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <style>
    body { min-height: 100vh; display: flex; }
    #sidebar { width: 250px; }
    #content { flex: 1; padding: 2rem; }
  </style>
</head>
<body>

     <?php include("nav_admin.php"); ?>
 
  <div id="content">
    <div class="container-fluid">
      <h1 class="mb-4">Update Password</h1>

      <?php if ($err): ?>
        <div class="alert alert-danger">
          <ul class="mb-0">
            <?php foreach ($err as $e): ?>
              <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <?php if ($succ): ?>
        <div class="alert alert-success"><?= htmlspecialchars($succ) ?></div>
      <?php endif; ?>

      <form method="post">
        <div class="mb-3 position-relative">

            <p class="mb-1"><strong>Username:</strong> <?= htmlspecialchars($_SESSION['admin']) ?></p>

          <label for="oldpass" class="form-label">Password Lama</label>
          <div class="input-group">
            <input type="password" id="oldpass" name="oldpass" class="form-control" required>
            <button class="btn btn-outline-secondary" type="button" id="toggleOld">
              <i class="bi bi-eye" id="eyeOld"></i>
            </button>
          </div>
        </div>

        <div class="mb-3 position-relative">
          <label for="newpass" class="form-label">Password Baru</label>
          <div class="input-group">
            <input type="password" id="newpass" name="newpass" class="form-control" required>
            <button class="btn btn-outline-secondary" type="button" id="toggleNew">
              <i class="bi bi-eye" id="eyeNew"></i>
            </button>
          </div>
        </div>

        <div class="d-grid">
          <button type="submit" class="btn btn-primary">Update Password</button>
        </div>
      </form>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.querySelector('#toggleOld').addEventListener('click', () => {
      const f = document.querySelector('#oldpass'), i = document.querySelector('#eyeOld');
      const t = f.type === 'password' ? 'text' : 'password';
      f.type = t; i.classList.toggle('bi-eye'); i.classList.toggle('bi-eye-slash');
    });
    document.querySelector('#toggleNew').addEventListener('click', () => {
      const f = document.querySelector('#newpass'), i = document.querySelector('#eyeNew');
      const t = f.type === 'password' ? 'text' : 'password';
      f.type = t; i.classList.toggle('bi-eye'); i.classList.toggle('bi-eye-slash');
    });
  </script>
</body>
</html>


