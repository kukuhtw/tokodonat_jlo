<?php
// public/add_information.php

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

// Redirect jika bukan admin
if (empty($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

// Get PINECONE_NAMESPACE from settings
$pineconeNamespace = ''; // default value
try {
    $stmt = $db->getConnection()->prepare("SELECT value FROM settings WHERE `key` = 'PINECONE_NAMESPACE' LIMIT 1");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result) {
        $pineconeNamespace = $result['value'];
    }
} catch (PDOException $e) {
    error_log('Error getting PINECONE_NAMESPACE: ' . $e->getMessage());
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate input
    $requiredFields = ['judul', 'content'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            $_SESSION['info'] = [
                'status' => 'danger',
                'message' => 'Semua field wajib diisi!'
            ];
            header('Location: add_information.php');
            exit;
        }
    }

    // Sanitize input
    $namespace = $pineconeNamespace; // Use the value from settings
    $judul = trim($_POST['judul']);
    $content = trim($_POST['content']);
    $ispinecone = 0;

    try {
        // Insert new record
        $sql = "INSERT INTO information 
                (namespace, judul, content_information, ispinecone, lastupdate, regdate)
                VALUES 
                (:namespace, :judul, :content, :ispinecone, NOW(), NOW())";
        
        $stmt = $db->getConnection()->prepare($sql);
        $stmt->execute([
            ':namespace' => $namespace,
            ':judul' => $judul,
            ':content' => $content,
            ':ispinecone' => $ispinecone
        ]);

        // Set success notification
        $_SESSION['info'] = [
            'status' => 'success',
            'message' => 'Informasi baru berhasil ditambahkan!'
        ];

        // Redirect to view page
        header('Location: view_information.php');
        exit;

    } catch (PDOException $e) {
        // Log error and show message
        error_log('Database error: ' . $e->getMessage());
        $_SESSION['info'] = [
            'status' => 'danger',
            'message' => 'Gagal menambahkan informasi: ' . $e->getMessage()
        ];
    }
}

// Show notification if exists
$info = $_SESSION['info'] ?? null;
unset($_SESSION['info']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Tambah Informasi Baru</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="d-flex">
  <?php include 'nav_admin.php'; ?>

  <div id="content" class="flex-grow-1 p-4">
    <h1 class="mb-3">Tambah Informasi Baru</h1>

    <?php if ($info): ?>
      <div class="alert alert-<?= $info['status'] ?>"><?= htmlspecialchars($info['message']) ?></div>
    <?php endif; ?>

    <form method="post" class="border p-4 rounded">
      <div class="mb-3">
        <label for="namespace" class="form-label">Namespace</label>
        <input type="text" class="form-control" id="namespace" name="namespace" 
               value="<?= htmlspecialchars($pineconeNamespace) ?>" readonly>
        <div class="form-text">Namespace diambil dari pengaturan sistem</div>
      </div>

      <div class="mb-3">
        <label for="judul" class="form-label">Judul</label>
        <input type="text" class="form-control" id="judul" name="judul" required>
        <div class="form-text">Judul informasi yang akan ditampilkan</div>
      </div>

      <div class="mb-3">
        <label for="content" class="form-label">Konten Informasi</label>
        <textarea class="form-control" id="content" name="content" rows="8" required></textarea>
        <div class="form-text">Isi lengkap informasi</div>
      </div>

      
      <div class="d-flex justify-content-between">
        <a href="view_information.php" class="btn btn-secondary">
          <i class="bi bi-arrow-left"></i> Kembali
        </a>
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-save"></i> Simpan
        </button>
      </div>
    </form>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>