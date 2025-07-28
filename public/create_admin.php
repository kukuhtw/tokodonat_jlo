<?php
// public/create_admin.php
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

// 1) Panggil bootstrap sekali—cukup ini saja:
require __DIR__ . '/../bootstrap.php';  // otomatis load config, Logger, Database, AdminAuth

$errors  = [];
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['loginadmin'] ?? '');
    $pass  = trim($_POST['password']   ?? '');

    if ($login === '' || $pass === '') {
        $errors[] = 'Username & Password wajib diisi.';
        Logger::warning('CreateAdmin: Empty credentials submitted.');
    } else {
       
        if ($adminAuth->createOrUpdatePassword($login, $pass)) {
            $success = 'Admin dibuat atau password diupdate.';
        } else {
            $errors[] = 'Gagal menyimpan data admin.';
        }
       
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Create Admin</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <h1 class="mb-4 text-center">Create Admin</h1>

        <?php if ($errors): ?>
          <div class="alert alert-danger">
            <ul class="mb-0">
              <?php foreach ($errors as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <?php if ($success): ?>
          <div class="alert alert-success">
            <?= htmlspecialchars($success) ?>
          </div>
        <?php endif; ?>

        <form method="post">
          <div class="mb-3">
            <label for="loginadmin" class="form-label">Username</label>
            <input type="text" id="loginadmin" name="loginadmin" class="form-control" value="<?= htmlspecialchars($_POST['loginadmin'] ?? '') ?>" required>
          </div>

          <div class="mb-3 position-relative">
            <label for="password" class="form-label">Password</label>
            <div class="input-group">
              <input type="password" id="password" name="password" class="form-control" required>
              <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                <i class="bi bi-eye" id="eyeIcon"></i>
              </button>
            </div>
          </div>

          <div class="d-grid">
            <button type="submit" class="btn btn-primary">Buat Admin</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS & dependencies -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

  <script>
    // Toggle password visibility
    const togglePassword = document.querySelector('#togglePassword');
    const passwordField = document.querySelector('#password');
    const eyeIcon = document.querySelector('#eyeIcon');

    togglePassword.addEventListener('click', function () {
      const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
      passwordField.setAttribute('type', type);
      eyeIcon.classList.toggle('bi-eye');
      eyeIcon.classList.toggle('bi-eye-slash');
    });
  </script>
</body>
</html>
