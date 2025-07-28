<?php
// public/proses_metadata.php
// Proses pembuatan metadata untuk Pinecone berdasarkan kolom description
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
session_start();
if (empty($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

require __DIR__ . '/../bootstrap.php';
Logger::debug('--- Start proses_metadata.php ---');

// 1. Ambil parameter data_id
$dataId = isset($_GET['data_id']) ? (int)$_GET['data_id'] : 0;
Logger::debug("Received data_id: {$dataId}");
if (!$dataId) {
    Logger::error('Missing or invalid data_id');
    header('HTTP/1.1 400 Bad Request');
    echo 'Missing or invalid data_id';
    exit;
}

try {
    // 2. Koneksi
    $conn = $db->getConnection();
    Logger::debug('Database connection established.');

    // 3. Ambil description dari data_inventory
    $stmt = $conn->prepare('SELECT description FROM data_inventory WHERE id = :id');
    $stmt->execute([':id' => $dataId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        Logger::error("No data_inventory record for id={$dataId}");
        header('HTTP/1.1 404 Not Found');
        echo 'Data not found';
        exit;
    }
    $description = $row['description'];
    Logger::debug('Fetched description (length=' . strlen($description) . ').');

    // 4. Ambil OPEN_AI_KEY dan modelgpt dari settings
    $stmt = $conn->prepare(
        "SELECT `key`,`value" .
        "` FROM settings WHERE `key` IN ('OPEN_AI_KEY','modelgpt')"
    );
    $stmt->execute();
    $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    if (empty($settings['OPEN_AI_KEY']) || empty($settings['modelgpt'])) {
        Logger::error('Missing OPEN_AI_KEY or modelgpt in settings');
        header('HTTP/1.1 500 Internal Server Error');
        echo 'Settings OPEN_AI_KEY or modelgpt missing';
        exit;
    }
    $apiKey   = $settings['OPEN_AI_KEY'];
    $modelGpt = $settings['modelgpt'];
    Logger::debug("Settings loaded: model={$modelGpt}");

    // 5. Ambil instruction dari prompts (METADATA_PINECONE)
    $stmt = $conn->prepare(
        "SELECT instruction
         FROM prompts
         WHERE promptid = 'METADATA_PINECONE'"
    );
    $stmt->execute();
    $promptRow = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$promptRow) {
        Logger::error("Prompt 'METADATA_PINECONE' not found");
        header('HTTP/1.1 500 Internal Server Error');
        echo 'Prompt METADATA_PINECONE not found';
        exit;
    }
    $instruction = $promptRow['instruction'];
    Logger::debug('Prompt instruction loaded.');

    // 6. Gabungkan instruction + description
    $promptContent = $instruction . "\n\n" . $description;
    Logger::debug('Prompt content prepared (length=' . strlen($promptContent) . ').');

    // 7. Panggil OpenAI
    $response = call_open_ai($apiKey, $promptContent, $modelGpt);
    Logger::debug('OpenAI API response received.');

    // 8. Parse response dan ambil metadata
    $json = json_decode($response, true);
    if (!isset($json['choices'][0]['message']['content']) && !isset($json['choices'][0]['text'])) {
        Logger::error('Invalid OpenAI response: ' . $response);
        header('HTTP/1.1 500 Internal Server Error');
        echo 'OpenAI API error';
        exit;
    }
    $metadata = isset($json['choices'][0]['message']['content'])
        ? trim($json['choices'][0]['message']['content'])
        : trim($json['choices'][0]['text']);
    Logger::debug('Extracted metadata (length=' . strlen($metadata) . ').');

    // 9. Update metadata_pinecone dan lastupdatedate di data_inventory
    $date = new DateTime('now', new DateTimeZone('Asia/Jakarta'));
    $lastupdatedate = $date->format('Y-m-d H:i:s');

    $metadata = str_replace("```json", "", $metadata);
    $metadata = str_replace("```", "", $metadata);

    $upd = $conn->prepare(
        'UPDATE data_inventory
         SET metadata_pinecone = :metadata,
             ispinecone       = 0,
             lastupdatedate   = :lastupdatedate
         WHERE id = :id'
    );
    $upd->execute([
        ':metadata'       => $metadata,
        ':lastupdatedate' => $lastupdatedate,
        ':id'             => $dataId
    ]);

    Logger::info("Updated metadata_pinecone for id={$dataId} at {$lastupdatedate}.");

    // Redirect ke view_data.php setelah sukses
    header('Location: view_data.php');
    exit;

} catch (Exception $e) {
    Logger::error('Exception: ' . $e->getMessage());
    header('HTTP/1.1 500 Internal Server Error');
    echo 'Internal server error';
    exit;
}

/**
 * Fungsi untuk memanggil OpenAI Chat Completion
 */
function call_open_ai($OPENAI_API_KEY, $prompt_content, $modelgpt) {
    $temperature       = 0.8;
    $max_tokens        = 800;
    $top_p             = 1;
    $frequency_penalty = 0;
    $presence_penalty  = 0;
    $stop              = ["\n\n$$$"];

    $messages = [
        ['role' => 'system', 'content' => 'You are a helpful assistant.'],
        ['role' => 'user',   'content' => $prompt_content],
    ];

    $postData = [
        'model'             => $modelgpt,
        'messages'          => $messages,
        'temperature'       => $temperature,
        'max_tokens'        => $max_tokens,
        'top_p'             => $top_p,
        'frequency_penalty' => $frequency_penalty,
        'presence_penalty'  => $presence_penalty,
        'stop'              => $stop,
    ];

    $endpoint = 'https://api.openai.com/v1/chat/completions';
    $headers  = [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $OPENAI_API_KEY,
    ];

    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_POST,           true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER,     $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS,     json_encode($postData));

    $content = curl_exec($ch);
    $curlErr = curl_error($ch);
    curl_close($ch);

    if ($curlErr) {
        Logger::error("cURL error: {$curlErr}");
    }

    return preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $content);
}

?>