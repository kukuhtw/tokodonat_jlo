<?php
// public/query_data_process.php
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

header('Content-Type: application/json; charset=UTF-8');

try {
    // --- 1. Ambil & validasi parameter ---
    $buyer_name  = trim($_GET['name']  ?? '');
    $buyer_email = trim($_GET['email'] ?? '');
    $buyer_wa    = trim($_GET['wa']    ?? '');
    $buyer_query = trim($_GET['q']     ?? '');

    if ($buyer_name === '' || $buyer_wa === '' || $buyer_query === '') {
        Logger::error("Parameter missing: name={$buyer_name}, wa={$buyer_wa}, query_length=" . strlen($buyer_query));
        echo json_encode(['error' => 'Parameter tidak lengkap.']);
        exit;
    }
    Logger::debug("Params received: name={$buyer_name}, email={$buyer_email}, wa={$buyer_wa}");

    // --- 2. Load konfigurasi OpenAI, Pinecone & GPT model ---
    $conn = $db->getConnection();
    $keys = ['OPEN_AI_KEY','PINECONE_API_KEY','PINECONE_INDEX_NAME','PINECONE_NAMESPACE','PINECONE_ENVIRONMENT','modelgpt'];
    $in   = rtrim(str_repeat('?,', count($keys)), ',');
    $stmt = $conn->prepare("SELECT `key`,`value` FROM settings WHERE `key` IN ($in)");
    $stmt->execute($keys);
    $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    $openaiKey     = $settings['OPEN_AI_KEY']         ?? null;
    $pineconeKey   = $settings['PINECONE_API_KEY']    ?? null;
    $pineconeIndex = $settings['PINECONE_INDEX_NAME'] ?? null;
    $pineconeNS    = $settings['PINECONE_NAMESPACE']  ?? null;
    $pineconeEnv   = $settings['PINECONE_ENVIRONMENT']?? '';
    $modelgpt      = $settings['modelgpt']            ?? 'gpt-4o-mini';

    if (!$openaiKey || !$pineconeKey || !$pineconeIndex || !$pineconeNS) {
        Logger::error('Configuration incomplete: ' . json_encode($settings));
        echo json_encode(['error' => 'Konfigurasi OpenAI/Pinecone belum lengkap.']);
        exit;
    }



// Prepare the message for OpenAI
    $messages = [
        [
            'role' => 'system',
            'content' => 'You are an assistant'
        ],
        [
            'role' => 'user',
            'content' => '\n\nidentifikasi barang jasa dicar user. JAWAB TANPA PENJELASAN. Remove comments inside the array, as JSON does not support comments. Berikut permintaan user: '.$buyer_query. '\n\n.
contoh metadatapinecone sepert ini. SESUAIKAN. 
Metadata Pinecone: {
 "ID": "1",
 "judul": "Villa 3 Kamar dengan Private Pool dan View Pantai di Lombok",
 "transaksi": "jual",
 "kategori": "Rumah",
 "lokasi": "Lombok",
 "luas": "",
 "kondisi": "Baru",
 "jumlah": "1 unit",
 "tahun": "",
 "merek": "",
 "kapasitas": "3 kamar",
 "sertifikat": "",
 "fitur_lain": "Private pool, view pantai",
 "harga": "8000000000",
 "harga_keterangan": "total",
 "deskripsi": "Dijual villa mewah 3 kamar di Lombok, dilengkapi private pool dan pemandangan pantai yang memukau. Cocok untuk hunian pribadi atau investasi liburan. Bangunan masih baru, lokasi strategis dan siap huni."
}
'

        ]
    ];

    // Call OpenAI API
    
    $openai_response = call_open_ai($openaiKey, $modelgpt, $messages);
    $pinecone_filter = $openai_response['choices'][0]['message']['content'];


    $pinecone_filter = str_replace("```php","",$pinecone_filter);
     $pinecone_filter = str_replace("*","",$pinecone_filter);
    $pinecone_filter = str_replace("```","",$pinecone_filter);

   

    // --- 3. Embedding teks ---
    $embedResp = call_embeddings_openai($openaiKey, $buyer_query);
    $embData   = json_decode($embedResp, true);
    $vector    = $embData['data'][0]['embedding'] ?? [];

    if (empty($vector)) {
        Logger::error('Embedding empty or invalid response: ' . substr($embedResp, 0, 200));
        echo json_encode(['error' => 'Gagal membuat embedding.']);
        exit;
    }
    Logger::debug('Embedding created, vector length=' . count($vector));

    Logger::debug("Embedding length: " . count($vector)); // harus 1536

     Logger::debug("Embedding pineconeNS: " . $pineconeNS); // harus 1536

    // --- 4. Query ke Pinecone ---
    $pineResp = query_pinecone(
        $pineconeKey,
        $pineconeIndex,
        $pineconeEnv,
        $pineconeNS,
        [],
        $vector
    );
    $pineData = json_decode($pineResp, true);
    $matches  = $pineData['matches'] ?? [];
    Logger::debug('Pinecone returned ' . count($matches) . ' matches');

    if (empty($matches)) {
        echo json_encode(['result' => 'Hasil pencarian tidak menemukan data yang cocok, coba sebutkan kriteria lebih spesifik']);
        exit;
    }

    // --- 5. Siapkan metadata untuk prompt OpenAI Chat ---
    $content_meta = '';
    foreach ($matches as $m) {
        if (isset($m['score']) && $m['score'] >= 0.820) {
            $content_meta .= get_content($m['score'], $m['id']) . "\n";
        }
    }

    // --- 6. Panggil OpenAI Chat ---
    $messages = [
        ['role'=>'system','content'=>'You are an assistant'],
        ['role'=>'user','content'=> trim($content_meta) . "\n\nBerikan jawaban beserta dataid inventory, Matchscore, owner , phone, email dan deskripsi lengkap"]
    ];
    Logger::debug('OpenAI Chat Request: ' . json_encode($messages));
    $chatResp = call_open_ai($openaiKey, $modelgpt, $messages);
    Logger::debug('OpenAI Chat Response: ' . substr(json_encode($chatResp), 0, 1000));

    $answer = trim($chatResp['choices'][0]['message']['content'] ?? '');
    if ($answer === '') {
        Logger::warning('OpenAI returned empty answer: ' . json_encode($chatResp));
        $answer = 'Hasil pencarian tidak menemukan data yang cocok.';
    }
    Logger::debug('OpenAI answer snippet: ' . substr($answer, 0, 100));

    // --- 7. Simpan ke database transaksi_query ---
    $now = date('Y-m-d H:i:s');
    $sql = "INSERT INTO transaksi_query 
        (buyer_name,buyer_email,buyer_wa,buyer_query,results,trdate)
       VALUES (?,?,?,?,?,?)";
    $ins = $conn->prepare($sql);
    $ins->execute([$buyer_name, $buyer_email, $buyer_wa, $buyer_query, $answer, $now]);
    Logger::info('Transaksi_query inserted, ID=' . $conn->lastInsertId());

    // --- 8. Output JSON ---
    echo json_encode(['result' => nl2br(htmlspecialchars($answer))], JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT);
    exit;

} catch (Exception $e) {
    Logger::error('Exception: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    echo json_encode(['error' => 'Terjadi kesalahan internal.']);
    exit;
}

/**
 * Panggilan ke OpenAI Embeddings
 */
function call_embeddings_openai(string $apiKey, string $textToEmbed): string {
    $url = 'https://api.openai.com/v1/embeddings';
    $payload = [
        'input' => $textToEmbed,
        'model' => 'text-embedding-ada-002',
    ];
    $jsonPayload = json_encode($payload);
    Logger::debug("OpenAI Embeddings Request: {$jsonPayload}");

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
        ],
        CURLOPT_POSTFIELDS     => $jsonPayload,
        CURLOPT_RETURNTRANSFER => true,
    ]);
    $resp = curl_exec($ch);
    if ($resp === false) {
        Logger::error('OpenAI curl error: ' . curl_error($ch));
        curl_close($ch);
        return '';
    }
    curl_close($ch);

    //Logger::debug('OpenAI Embeddings Response: ' . substr($resp, 0, 1000));
    return $resp;
}

/**
 * Query Pinecone
 */
function query_pinecone(
    string $apiKey,
    string $indexName,
    string $environment,
    string $namespace,
    array  $filter,
    array  $vector
): string {
    $url = "https://{$indexName}.pinecone.io/query";
   

    $payload = [
  'topK'          => 10,
  'namespace'     => $namespace,
  'vector'        => $vector,
  'includeValues' => true,
  // drop filter kalau kosong
    ];
    if (! empty($filter)) {
      $payload['filter'] = $filter;
    }
    $jsonPayload = json_encode($payload);
    //Logger::debug("Pinecone Query Request: {$jsonPayload}");

   // Logging lengkap
    Logger::debug("Pinecone Query URL: {$url}");
    Logger::debug("Pinecone indexName: {$indexName}");
    Logger::debug("Pinecone namespace: {$namespace}");
    Logger::debug("Pinecone filter: " . json_encode($filter));
    Logger::debug("Pinecone vector length: " . count($vector));
    //Logger::debug("Pinecone Query Payload: {$jsonPayload}");

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Api-Key: ' . $apiKey,
        ],
        CURLOPT_POSTFIELDS     => $jsonPayload,
        CURLOPT_RETURNTRANSFER => true,
    ]);
    $resp = curl_exec($ch);
    if ($resp === false) {
        Logger::error('Pinecone curl error: ' . curl_error($ch));
        curl_close($ch);
        return '';
    }
    curl_close($ch);

    Logger::debug('Pinecone Response: ' . substr($resp, 0, 1000));

    Logger::debug('Full Pinecone raw response: ' . $resp);
Logger::debug('HTTP status code: ' . curl_getinfo($ch, CURLINFO_HTTP_CODE));



    return $resp;
}

function call_open_ai($OPENAI_KEY, $modelgpt, $messages_param) {
    // Inisialisasi konfigurasi permintaan
    $temperature = 0.9;
    $max_tokens = 8192;
    $top_p = 0;
    $frequency_penalty = 0;
    $presence_penalty = 0;
    $stop = "\n\n$$$";

    $postData = array(
        'model' => $modelgpt,
        'messages' => $messages_param,
        'temperature' => $temperature,
        'max_tokens' => $max_tokens,
        'top_p' => $top_p,
        'frequency_penalty' => $frequency_penalty,
        'presence_penalty' => $presence_penalty,
        'stop' => $stop,
    );

    $BASE_END_POINT = 'https://api.openai.com/v1/chat/completions';

    $headers = array(
        'Content-Type: application/json',
        'Authorization: Bearer ' . $OPENAI_KEY,
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $BASE_END_POINT);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));

    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}

/**
 * Ambil konten inventory dan format HTML-nya
 *
 * @param float  $score  Skor kecocokan dari Pinecone
 * @param string $id     ID inventory
 * @return string        HTML snippet, atau empty string jika tidak ditemukan
 */
function get_content($score, $id) {
    // Ambil koneksi global
    global $db;
    $conn = $db->getConnection();

    // Query hanya field yang dibutuhkan
    // Query hanya field yang dibutuhkan
    $sql = "
        SELECT 
            description,
            metadata_pinecone,
            `owner`,
            `phone`,
            `email`
        FROM data_inventory
        WHERE id = :id
        LIMIT 1
    ";


    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (! $row) {
            // Jika tidak ada data, return string kosong
            return '';
        }

        // Pilih description, fallback ke info
        $content = trim($row['metadata_pinecone']);
        $owner = trim($row['owner']) ;
         $phone = trim($row['phone']) ;

        // Optional logging
        Logger::debug("get_content(): loaded id={$id}, score={$score}");

        // Bangun HTML
        $html  = "\n";
        $html .=   "\n";
        $html .=     "ID: {$id}";
       
        $html .=   "\n";
        $html .=     "Matchscore: {$score}\n";
         $html .=   "\n";
        $html .=     "owner: {$owner}\n";
         $html .=   "\n";
        $html .=     "phone: {$phone}\n";
        $html .=     "{$content}";
        

        return $html;

    } catch (PDOException $e) {
        // Log error dan return kosong
        Logger::error("get_content() error for id={$id}: " . $e->getMessage());
        return '';
    }
}
