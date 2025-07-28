<?php
// public/delete_information.php

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

// 1. Authentication
if (empty($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

// 2. Get parameters
$id     = $_POST['id'] ?? null;
$page   = (int) ($_POST['page'] ?? 1);
$search = $_POST['q'] ?? '';

if (!$id) {
    $_SESSION['info'] = ['status'=>'danger', 'message'=>'Invalid ID.'];
    header("Location: view_information.php?page={$page}&q=" . urlencode($search));
    exit;
}

// 3. Get settings from database
$conn = $db->getConnection();
$keys = ['OPEN_AI_KEY', 'PINECONE_API_KEY', 'PINECONE_INDEX_NAME', 'PINECONE_NAMESPACE'];
$in   = str_repeat('?,', count($keys) - 1) . '?';
$stmt = $conn->prepare("SELECT `key`, `value` FROM settings WHERE `key` IN ($in)");
$stmt->execute($keys);
$settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

$pineconeApiKey    = $settings['PINECONE_API_KEY']    ?? '';
$pineconeIndexName = $settings['PINECONE_INDEX_NAME'] ?? '';
$namespace         = $settings['PINECONE_NAMESPACE']  ?? '';

// 4. Delete from Pinecone
Logger::debug("Attempting to delete vector for information ID: {$id}");
$pineconeDeleted = false;
$pineconeError   = null;

$url = "https://{$pineconeIndexName}.pinecone.io/vectors/delete";
$payload = [
    'ids' => [(string)$id],
    'namespace' => $namespace,
];
Logger::debug("Pinecone delete url: " . $url);
Logger::debug("Pinecone payload: " . json_encode($payload));

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'Api-Key: ' . $pineconeApiKey,
    ],
    CURLOPT_POSTFIELDS     => json_encode($payload),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 10,
]);

$response = curl_exec($ch);
if ($response === false) {
    $pineconeError = curl_error($ch);
    Logger::error("Failed to delete Pinecone vector ID {$id}: " . $pineconeError);
} else {
    $responseData = json_decode($response, true);
    Logger::debug("Pinecone delete response: " . json_encode($responseData, true));

    if (isset($responseData['code']) && $responseData['code'] == 404) {
        Logger::info("Vector ID {$id} not found in Pinecone - may have been deleted already");
        $pineconeDeleted = true;
    } elseif (isset($responseData['code'])) {
        $pineconeError = "Pinecone API error: " . ($responseData['message'] ?? 'Unknown error');
        Logger::error($pineconeError);
    } else {
        Logger::info("Successfully deleted vector ID {$id} from Pinecone");
        $pineconeDeleted = true;
    }
}
curl_close($ch);

// 5. Delete from MySQL if Pinecone deletion successful
if ($pineconeDeleted) {
    try {
        $stmt = $conn->prepare("DELETE FROM information WHERE id = :id");
        $stmt->execute([':id' => $id]);
        Logger::info("Information ID {$id} successfully deleted from database");

        $_SESSION['info'] = [
            'status'  => 'success',
            'message' => "Information ID {$id} successfully deleted."
        ];
    } catch (PDOException $e) {
        Logger::error("Failed to delete information ID {$id} from database: " . $e->getMessage());
        $_SESSION['info'] = [
            'status'  => 'danger',
            'message' => "Failed to delete information from database."
        ];
    }
} else {
    Logger::warning("Preserved information ID {$id} in database due to Pinecone deletion failure");
    $_SESSION['info'] = [
        'status'  => 'warning',
        'message' => "Information preserved in database. Pinecone deletion failed: " . 
                     ($pineconeError ?? 'Unknown error')
    ];
}

// 6. Redirect with notification
header("Location: view_information.php?page={$page}&q=" . urlencode($search));
exit;
