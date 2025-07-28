<?php
// public/edit_information.php

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

// Pastikan hanya admin yang bisa mengakses
if (empty($_SESSION['admin'])) {
    header('HTTP/1.1 403 Forbidden');
    exit;
}

// Hanya terima request POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    exit;
}

// Validasi input
$requiredFields = ['id', 'namespace', 'judul', 'content'];
foreach ($requiredFields as $field) {
    if (empty($_POST[$field])) {
        $_SESSION['info'] = [
            'status' => 'danger',
            'message' => 'Semua field harus diisi!'
        ];
        header('Location: view_information.php?page=' . ($_POST['page'] ?? 1));
        exit;
    }
}

// Ambil data dari POST
$id = (int)$_POST['id'];
$namespace = trim($_POST['namespace']);
$judul = trim($_POST['judul']);
$content = trim($_POST['content']);
$ispinecone =  0;
$page = max(1, (int)($_POST['page'] ?? 1));

try {
    // Update informasi di database
    $sql = "UPDATE information SET 
                namespace = :namespace,
                judul = :judul,
                content_information = :content,
                ispinecone = :ispinecone,
                lastupdate = NOW()
            WHERE id = :id";
    
    $stmt = $db->getConnection()->prepare($sql);
    $stmt->execute([
        ':namespace' => $namespace,
        ':judul' => $judul,
        ':content' => $content,
        ':ispinecone' => $ispinecone,
        ':id' => $id
    ]);

    // Set notifikasi sukses
    $_SESSION['info'] = [
        'status' => 'success',
        'message' => 'Informasi berhasil diperbarui!'
    ];

} catch (PDOException $e) {
    // Tangani error database
    error_log('Database error: ' . $e->getMessage());
    $_SESSION['info'] = [
        'status' => 'danger',
        'message' => 'Gagal memperbarui informasi: ' . $e->getMessage()
    ];
}

// Redirect kembali ke halaman view dengan pagination yang sama
header('Location: view_information.php?page=' . $page);
exit;