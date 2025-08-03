<?php
// public/webhook_wa.php

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

header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../src/ChatHandler.php';

// Initialize Logger
Logger::init([
    'path' => __DIR__ . '/../logs/webhook.log',
    'level' => 'DEBUG' // Can be DEBUG, INFO, WARNING, ERROR
]);

// Get Fonnte Token from database
try {
    global $pdo;
    $stmt = $pdo->prepare("SELECT value FROM settings WHERE `key` = 'Token_Fonnte'");
    $stmt->execute();
    $tokenResult = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$tokenResult) {
        throw new Exception("Token_Fonnte not found in settings");
    }
    
    $Token_Fonnte = $tokenResult['value'];
    Logger::debug("Successfully retrieved Fonnte token from database");
} catch (Exception $e) {
    Logger::error("Failed to get Fonnte token: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Server configuration error']);
    exit;
}

// Get input data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Log incoming request
Logger::debug("Incoming request: " . json_encode($data));

// Validate input
if (empty($data['sender'])) {
    Logger::error("No sender provided in request");
    http_response_code(400);
    echo json_encode(['error' => 'Sender number is required']);
    exit;
}

// Extract data
$device = $data['device'] ?? '';
$isipesan = $data['message'] ?? '';
$sender = $data['sender'];
$name = str_replace(["'", "`"], "", $data['name'] ?? '');

Logger::info("Processing message from $sender ($name): $isipesan");

// Set timezone
date_default_timezone_set("Asia/Jakarta");
$timestamp = date("Y-m-d H:i:s");

// Initialize session (simulate backend_chat.php behavior)
session_id(md5($sender)); // Use sender number as session ID
session_start();

if (!isset($_SESSION['testing_wa'])) {
    // Register new user session
    $_SESSION['testing_wa'] = $sender;
    $_SESSION['testing_name'] = $name;
    $_SESSION['testing_email'] = ''; // WhatsApp users may not have email
    
    Logger::info("New user registered: $sender ($name)");
}

try {
    // Initialize chat handler
    $handler = new ChatHandler($pdo, $sender, $name, '');
    Logger::debug("ChatHandler initialized for $sender");
    
    $result = $handler->handle($isipesan);
    Logger::debug("Handler response: " . json_encode($result));
    
    // Prepare response
    $response = ['result' => $result['result'] ?? $result['reply'] ?? ''];
    
    // If response contains buttons or special formatting, handle accordingly
    if (isset($result['buttons'])) {
        $buttonJSON = json_encode($result['buttons']);
        Logger::debug("Sending message with buttons to $sender");
        kirimPesan_whatsapp($sender, $response['result'], $buttonJSON, $Token_Fonnte);
    } else {
        Logger::debug("Sending plain message to $sender");
        kirimPesan_whatsapp($sender, $response['result'], '', $Token_Fonnte);
    }
    
    Logger::info("Successfully processed message from $sender");
    
} catch (Throwable $e) {
    Logger::error("Error processing message from $sender: " . $e->getMessage());
    
    // Send error message to user
    $errorMessage = "Maaf, terjadi kesalahan dalam memproses permintaan Anda. Silakan coba lagi nanti.";
    kirimPesan_whatsapp($sender, $errorMessage, '', $Token_Fonnte);
    
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
    exit;
}

// Return success response
Logger::debug("Returning success response for $sender");
echo json_encode(['status' => 'success', 'message' => 'Message processed']);

function kirimPesan_whatsapp($targetgroup, $message, $buttonJSON, $Token_Fonnte) {
    Logger::debug("Preparing to send WhatsApp message to $targetgroup");
    
    $check2chardipdean = substr($targetgroup, 0, 2);
    $countrycode = $check2chardipdean;
    
    if ($check2chardipdean == "62" || $check2chardipdean == "08") {
        $countrycode = "0";
    }
    
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.fonnte.com/send',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => array(
            'target' => $targetgroup,
            'message' => $message,
            'schedule' => '0',
            'filename' => 'filenamehere',
            'countrycode' => $countrycode,
            'buttonJSON' => $buttonJSON
        ),
        CURLOPT_HTTPHEADER => array(
            'Authorization: '.$Token_Fonnte
        ),
    ));

    $response = curl_exec($curl);
    
    if (curl_errno($curl)) {
        Logger::error("WhatsApp API error for $targetgroup: " . curl_error($curl));
    } else {
        Logger::debug("WhatsApp API response for $targetgroup: $response");
    }
    
    curl_close($curl);
    
    return $response;
}