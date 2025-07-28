<?php

// public/backend_chat.php

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

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../src/ChatHandler.php';


/* ---------- 1. Session check ---------- */
session_start();
if (!isset($_SESSION['testing_wa'])) {
    Logger::info('backend_chat: unregistered user attempted access');
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'User not registered. Please register first.']);
    exit;
}

$sender = $_SESSION['testing_wa'];
$name  =  $_SESSION['testing_name'] ;
$email =   $_SESSION['testing_email'];
Logger::debug("backend_chat: request from $sender");

/* ---------- 2. Handle request ---------- */
try {
    /** @var PDO $pdo  – loaded by bootstrap.php */
    global $pdo;

    $handler = new ChatHandler($pdo, $sender, $name ,$email);
    $result  = $handler->handle();

    Logger::info("45. backend_chat: response sent to $sender");
} catch (Throwable $e) {
    Logger::error("backend_chat: {$e->getMessage()}");
    $result = ['error' => 'Internal server error'];
}

/* ---------- 3. Emit JSON ---------- */
header('Content-Type: application/json');
// Menjadi:
echo json_encode(['result' => $result['result'] ?? $result['reply'] ?? ''], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
?>