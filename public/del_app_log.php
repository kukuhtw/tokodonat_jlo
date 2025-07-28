<?php
// public/del_app_log.php
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

$success = null;
$error   = null;
$logFile = __DIR__ . '/../logs/app.log';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (file_exists($logFile)) {
        if (unlink($logFile)) {
            $success = 'Log file berhasil dihapus.';
            Logger::info('Deleted app.log via admin panel.');
        } else {
            $error = 'Gagal menghapus file log.';
            Logger::error('Failed to delete app.log via admin panel.');
        }
    } else {
        $error = 'File log tidak ditemukan.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Delete Log File</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <style>body{min-height:100vh;display:flex;}#sidebar{width:250px;}#content{flex:1;padding:2rem;}</style>
</head>
<body>
  <?php include("nav_admin.php"); ?>
 

  <div id="content">
    <div class="container-fluid">
      <h1 class="mb-4">Delete Application Log</h1>
      <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
      <?php endif; ?>
      <form method="post">
        <p>Hapus file log: <code>logs/app.log</code></p>
        <button type="submit" class="btn btn-danger">
          <i class="bi bi-trash"></i> Delete Log File
        </button>
      </form>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
