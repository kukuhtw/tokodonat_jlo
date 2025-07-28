<?php
/**
 * public/update_order.php
 *
 * ✏️ Proses update status dan detail pesanan
 * Hanya bisa diakses oleh admin yang login
 */

require __DIR__ . '/../bootstrap.php';
session_start();

if (empty($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

// Ambil data dari POST
$id                = (int)($_POST['id'] ?? 0);
$ispaid            = isset($_POST['ispaid']) ? (int)$_POST['ispaid'] : 0;
$order_description = trim($_POST['order_description'] ?? '');
$note              = trim($_POST['note'] ?? '');
$paid_date_raw     = $_POST['paid_date'] ?? null;

// Validasi paid_date ke format datetime MySQL (jika tidak kosong)
$paid_date = null;
if (!empty($paid_date_raw)) {
    $timestamp = strtotime($paid_date_raw);
    if ($timestamp !== false) {
        $paid_date = date('Y-m-d H:i:s', $timestamp);
    }
}

// Update database
if ($id > 0) {
    $sql = "
        UPDATE `order`
        SET
            ispaid = :ispaid,
            paid_date = :paid_date,
            order_description = :order_description,
            note = :note
        WHERE id = :id
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':ispaid'            => $ispaid,
        ':paid_date'         => $paid_date,
        ':order_description' => $order_description,
        ':note'              => $note,
        ':id'                => $id
    ]);
}

// Redirect kembali ke halaman view_order
header('Location: view_order.php');
exit;
