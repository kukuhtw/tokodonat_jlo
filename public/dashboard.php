<?php
// public/dashboard.php
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
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard Admin</title>
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
  <h1 class="mb-4">📊 Dashboard Admin Chatbot Donat JLO</h1>

  <div class="card mb-4">
    <div class="card-body">
      <p><strong>Login Sejak / Logged in since:</strong> <?= htmlspecialchars($_SESSION['login_time'] ?? '') ?></p>
    </div>
  </div>

  <div class="card mb-4">
    <div class="card-header bg-warning text-dark">
      Tentang Aplikasi Chatbot Toko Donat JLO Jakarta
    </div>
    <div class="card-body">
      <p>
        <strong>Chatbot Toko Donat JLO</strong> adalah sistem otomatis yang melayani pertanyaan dan pemesanan donat melalui chat (WhatsApp atau Website). Pembeli bisa memesan donat untuk <strong>pengiriman ke tempat</strong> atau <strong>ambil sendiri di toko</strong>.
      </p>

      <h5 class="mt-4">📦 Layanan Pengiriman</h5>
      <ul>
        <li>Area layanan hanya mencakup <strong>Jakarta</strong> (Jakarta Timur, Barat, Utara, Selatan, dan Pusat).</li>
        <li>Chatbot akan menghitung <strong>biaya kirim</strong> otomatis berdasarkan wilayah pengiriman.</li>
        <li><strong>PPN</strong> otomatis dihitung dan ditambahkan ke total pembelian.</li>
      </ul>

      <h5 class="mt-4">📋 Fitur Chatbot</h5>
      <ul>
        <li>Menampilkan <strong>daftar menu donat</strong> lengkap beserta harga satuan.</li>
        <li>Mendukung <strong>diskon khusus</strong> yang bisa disesuaikan dari dashboard admin.</li>
        <li>Pembeli bisa memilih jumlah dan varian, lalu sistem akan menghitung total otomatis.</li>
        <li>Memberikan <strong>opsi ambil di toko</strong> atau <strong>minta kirim</strong>.</li>
        <li>Menyimpan data pesanan dan pembeli untuk keperluan laporan dan follow-up.</li>
      </ul>

      <h5 class="mt-4">⚙️ Pengaturan yang Bisa Diubah Admin</h5>
      <ul>
        <li>✅ Menambahkan / mengedit <strong>menu donat</strong>.</li>
        <li>✅ Mengatur <strong>harga per item</strong> dan diskon promosi.</li>
        <li>✅ Mengatur <strong>biaya kirim per wilayah</strong>.</li>
        <li>✅ Menyesuaikan <strong>persentase PPN</strong>.</li>
      </ul>

      <h5 class="mt-4">🌟 Manfaat Chatbot Donat JLO</h5>
      <ul>
        <li>✅ Menjawab pelanggan secara otomatis 24 jam.</li>
        <li>✅ Mengurangi beban CS dalam menjawab pertanyaan berulang.</li>
        <li>✅ Meningkatkan <strong>efisiensi pemesanan</strong> dan mengurangi kesalahan input order.</li>
        <li>✅ Dapat dijalankan di <strong>laptop, hosting pribadi</strong>, atau <strong>VPS</strong>.</li>
      </ul>

      <p class="mt-3">
        🚀 Aplikasi ini cocok untuk UMKM kuliner yang ingin meningkatkan pelayanan pelanggan dengan teknologi modern, hemat biaya, dan tetap ramah pengguna.
      </p>
    </div>
  </div>
</div>

</div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
