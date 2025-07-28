<?php
// public/query_data.php
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

// Inisialisasi variabel untuk sticky form & error
$errors       = [];
$buyer_name   = '';
$buyer_email  = '';
$buyer_wa     = '';
$buyer_query  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // masih dipakai sebagai fallback jika JS mati
    $buyer_name  = trim($_POST['buyer_name']  ?? '');
    $buyer_email = trim($_POST['buyer_email'] ?? '');
    $buyer_wa    = trim($_POST['buyer_wa']    ?? '');
    $buyer_query = trim($_POST['buyer_query'] ?? '');

    // Validasi
    if ($buyer_name === '') {
        $errors[] = 'Nama buyer harus diisi.';
    }
    if ($buyer_wa === '') {
        $errors[] = 'Whatsapp buyer harus diisi.';
    }
    if ($buyer_query === '') {
        $errors[] = 'Deskripsi lengkap harus diisi.';
    }

    // fallback: redirect ke proses
    if (empty($errors)) {
        header('Location: query_data_process.php'
            . '?name='  . urlencode($buyer_name)
            . '&email=' . urlencode($buyer_email)
            . '&wa='    . urlencode($buyer_wa)
            . '&q='     . urlencode($buyer_query)
        );
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
 <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Cari Barang</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="d-flex">
  <?php include 'nav_admin.php'; ?>

  <div id="content" class="flex-grow-1 p-4">
    <h1 class="mb-4">Cari Barang</h1>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger">
        <ul class="mb-0">
          <?php foreach ($errors as $e): ?>
            <li><?= htmlspecialchars($e) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form id="search-form" method="post" action="">
      <div class="mb-3">
        <label for="buyer_name" class="form-label">Nama Buyer <span class="text-danger">*</span></label>
        <input type="text" id="buyer_name" name="buyer_name" class="form-control"
               value="<?= htmlspecialchars($buyer_name) ?>" required>
      </div>

      <div class="mb-3">
        <label for="buyer_email" class="form-label">Email Buyer</label>
        <input type="email" id="buyer_email" name="buyer_email" class="form-control"
               value="<?= htmlspecialchars($buyer_email) ?>">
      </div>

      <div class="mb-3">
        <label for="buyer_wa" class="form-label">Whatsapp Buyer <span class="text-danger">*</span></label>
        <input type="text" id="buyer_wa" name="buyer_wa" class="form-control"
               value="<?= htmlspecialchars($buyer_wa) ?>" required>
      </div>

      <div class="mb-3">
        <label for="buyer_query" class="form-label">Deskripsi Lengkap <span class="text-danger">*</span></label>
        <textarea id="buyer_query" name="buyer_query" class="form-control" rows="6" required><?= htmlspecialchars($buyer_query) ?></textarea>
      </div>

      <button type="submit" id="search-button" class="btn btn-primary">Cari</button>
    </form>

    <!-- container untuk menampilkan hasil -->
    <div id="result" class="mt-4"></div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const form   = document.getElementById('search-form');
      const button = document.getElementById('search-button');
      const result = document.getElementById('result');

      form.addEventListener('submit', e => {
        // cegah default submit
        e.preventDefault();

        // ambil nilai field
        const name  = encodeURIComponent(document.getElementById('buyer_name').value);
        const email = encodeURIComponent(document.getElementById('buyer_email').value);
        const wa    = encodeURIComponent(document.getElementById('buyer_wa').value);
        const query = encodeURIComponent(document.getElementById('buyer_query').value);

        // bangun URL dengan GET params
        const url = `query_data_process.php?name=${name}&email=${email}&wa=${wa}&q=${query}`;

        // disable button sementara
        button.disabled = true;
        button.textContent = 'Mencari…';

        // reset container hasil
        result.innerHTML = '';

        // fetch ke proses
        fetch(url)
          .then(res => res.json())
          .then(data => {
            if (data.error) {
              result.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
            } else if (data.result) {
              result.innerHTML = `<div class="alert alert-success">${data.result}</div>`;
            } else {
              result.innerHTML = `<div class="alert alert-warning">Hasil tidak ditemukan.</div>`;
            }
          })
          .catch(err => {
            console.error(err);
            result.innerHTML = `<div class="alert alert-danger">Terjadi kesalahan saat memproses permintaan.</div>`;
          })
          .finally(() => {
            // kembalikan state tombol
            button.disabled = false;
            button.textContent = 'Cari';
          });
      });
    });
  </script>
</body>
</html>
