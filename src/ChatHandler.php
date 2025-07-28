<?php
// src/ChatHandler.php

require_once __DIR__ . '/QuotaService.php';
require_once __DIR__ . '/saatini.php';

class ChatHandler
{
    private PDO $pdo;
    private string $sender;
    private string $name;
    private string $email;
    private QuotaService $quotaService;
    private string $defaultPrompt;  // Will be loaded from DB
    
    public function __construct(PDO $pdo, string $sender, string $name, string $email)
    {
        $this->pdo = $pdo;
        $this->sender = $sender;
        $this->quotaService = new QuotaService($pdo, $sender);
        $this->name = $name;
        $this->email = $email;

        Logger::info("ChatHandler instantiated for sender {$sender}");
        $this->defaultPrompt = $this->loadPromptFromDB('INSTRUCTION_1');
    }

    /**
     * Load prompt text from database
     */
    private function loadPromptFromDB(string $promptId): string
    {
        Logger::debug("Loading prompt {$promptId} from DB");

        $stmt = $this->pdo->prepare("
            SELECT instruction
            FROM prompts
            WHERE promptid = :promptid
            LIMIT 1
        ");
        $stmt->execute([':promptid' => $promptId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            Logger::error("Prompt {$promptId} not found in DB");
            throw new RuntimeException("Prompt dengan ID '{$promptId}' tidak ditemukan di database.");
        }

        Logger::debug("Prompt {$promptId} loaded OK");
        return $row['instruction'];
    }

    // ============= MAIN PROCESS ===========

    public function handle(): array
    {
        Logger::info("Incoming request from {$this->sender}");
        $sender = $this->sender;
        
        // 1. Check quota
        if (!$this->quotaService->hasQuota()) {
            Logger::warning("Quota exceeded for {$this->sender}");
            return ['error' => 'Kuota chat per jam Anda telah habis.'];
        }

        // 2. Get user message text
        $text = $this->getIncomingText();
        Logger::debug("Line 59.src/ChatHandler.php: User message: {$text}");
        
        if ($text === '') {
            Logger::warning(":Line 69.src/ChatHandler.php: Empty message received from {$this->sender}");
            return ['error' => 'Pesan kosong.'];
        }

        Logger::debug("Line 73.src/ChatHandler.php: User message: {$text}");

        // Get API settings when CONFIRM
        $keys = ['OPEN_AI_KEY','PINECONE_API_KEY','PINECONE_INDEX_NAME','PINECONE_NAMESPACE','modelgpt'];
        $settings = $this->fetchSettings($keys);

        if (empty($settings['OPEN_AI_KEY']) || empty($settings['PINECONE_API_KEY']) || empty($settings['modelgpt'])) {
            Logger::error("153.API keys or model not configured");
            return ['error' => 'API key atau model GPT belum dikonfigurasi.'];
        }

        $dataProfile = $this->getUserProfile();
        Logger::debug("87.dataProfile message: {$dataProfile}");

        Logger::debug("90. getCurrentShoppingCart for whatsapp = {$sender}");
        $profileText = $this->getUserDataProfile($sender);
        Logger::debug("95. profileText = {$profileText}");

        if ($profileText) {
            Logger::debug("100. profileText = {$profileText}");
        } else {
            Logger::debug("102. profileText = {$profileText}");
        }

        // Get current shopping cart data
        $cartJson = $this->getCurrentShoppingCart($sender);
        if (empty($cartJson)) {
            Logger::debug("Cart kosong untuk user: {$sender}");
        }

        // 3. When user types CONFIRM
        Logger::debug("80.text=: {$text}");
        $lower_msg = strtolower($text);
        $lower_msg = trim($lower_msg);
        Logger::debug("lower_msg=: {$lower_msg}");

        // ============== CONFIRM PROCESS ================
        if ($lower_msg == "confirm") {
            $whatsappNumber = $this->sender;
            Logger::debug("90.confirm whatsappNumber =: {$sender}");
            
            $orderid = $this->generateorderid($sender);
            Logger::debug("92.orderid whatsappNumber =: {$orderid}");
            
            // Get shopping cart data
            $cartJson = $this->getCurrentShoppingCart($whatsappNumber);

            if (empty($cartJson)) {
                Logger::debug("123.Cart kosong untuk user: {$whatsappNumber}");
                $reply = 'Keranjang belanja Anda masih kosong.. Anda belum melakukan pemesanan';
                return ['result' => $reply];
            }
            
            $ispaid = 0;
            $note = '';
            
            // Save order
            $now = (new DateTime('now', new DateTimeZone('Asia/Jakarta')))->format('Y-m-d H:i:s');
            Logger::debug("131.start saveOrder order_date=: {$now}");
            Logger::debug("131.start saveOrder orderid=: {$orderid}");
            Logger::debug("131.start saveOrder whatsappNumber=: {$whatsappNumber}");

            Logger::info("135. ChatHandler instantiated for sender {$whatsappNumber}");   
            
            $name = $this->name;
            $email = $this->email;
            
            $context = "\norderId: ".$orderid;
            $context .= "\nprofileText: ".$profileText;
            
            $defaultPrompt = $this->loadPromptFromDB('JSON_ORDER');
            $contextShoppingCart = "\n\n📦 Shopping Cart Saat Ini:\n" . $cartJson;

            // =========== GET LAST CHAT HISTORY ==========
            $getLastChatHistory = $this->getLastChatHistory($whatsappNumber,15);
            $mapHistoryToMessages = $this->mapHistoryToMessages($getLastChatHistory);

            // Build messages for OpenAI
            $messages = $this->buildMessages(
                $mapHistoryToMessages,   
                $defaultPrompt,    
                $contextShoppingCart . $context.$dataProfile       
            );

            Logger::info("183. defaultPrompt instantiated for sender: " . $defaultPrompt);
            Logger::info("185. messages instantiated for sender: " . json_encode($messages));
            
            $functions = $this->getOpenAIFunctions();
            $model = $settings['modelgpt'] ?? 'gpt-3.5-turbo';
            $aiResponse = $this->callOpenAI($messages, $settings['OPEN_AI_KEY'], $model, $functions);

            Logger::debug("187. aiResponse:".json_encode($aiResponse));
            Logger::debug("189. callOpenAI end: {$text}");
            
            $content = $aiResponse['choices'][0]['message']['content'] ?? '';
            Logger::debug("193. content end: {$content}");

            $content = str_replace("```json","",$content);
            $content = str_replace("```","",$content);

            if ($content == "") {
                Logger::debug("200. content Null");
                $result = 'Error content Null';
                return ['result' => $result];
            }

            Logger::debug("203. content end: {$content}");

            // Clean up content
            $content = strip_tags($content);
            $content = str_replace('*', '', $content);
            $content = nl2br($content);

            $this->saveOrder(
                $orderid,
                $whatsappNumber,
                $content,
                $now,
                0,
                $now,
                ''
            );

            Logger::debug("147. end saveOrder order_date=: {$now}");

            // Reset conversation
            $resetTo = $whatsappNumber . '_';
            $this->pdo
                ->prepare("UPDATE chat_history SET whatsapp = ? WHERE whatsapp = ?")
                ->execute([$resetTo, $whatsappNumber]);

            $result = 'Thanks for your order, we will contact you shortly. Please proceed with payment as mentioned before. Your Order ID: ' . $orderid;
            return ['result' => $result];
            exit; // Stop execution after sending JSON response
        }
        // END IF TEXT == CONFIRM

        // 4. Embedding via OpenAI
        Logger::debug("getEmbedding !");
        Logger::debug("Embedding Text: {$text}");

        $vector = $this->getEmbedding($text, $settings['OPEN_AI_KEY']);
        if (empty($vector)) {
            return ['error' => 'Gagal mendapatkan embedding.'];
        }

        Logger::debug("Embedding via OpenAI: {$text}");

        // 5. Query Pinecone
        Logger::debug("queryPinecone start: {$text}");
        $matches = $this->queryPinecone($vector, $settings);
        if (empty($matches)) {
            return ['result' => 'Maaf, tidak ada konten yang relevan.'];
        }
        Logger::debug("queryPinecone end: {$text}");

        // 6. Assemble context
        $context = $this->assembleContext($matches);
        Logger::debug("251.assembleContext end: {$text}");  
        $context .= "\n\n📦 Shopping Cart Saat Ini:\n" . $cartJson;

        // Build messages for OpenAI
        $messages = $this->buildMessages(
            [],   
            $this->defaultPrompt,
            $context.$text.$dataProfile
        );  

        Logger::info("260. messages instantiated for sender: " . json_encode($messages));
        $functions = $this->getOpenAIFunctions();
        Logger::debug("303.functions:". json_encode($functions));

        $model = $settings['modelgpt'] ?? 'gpt-3.5-turbo-0613';
        Logger::debug("304. getOpenAIFunctions end: {$text}");  

        $aiResponse = $this->callOpenAI($messages, $settings['OPEN_AI_KEY'], $model, $functions);
        Logger::debug("308.callOpenAI end: {$text}");
        Logger::debug("309.callOpenAI aiResponse:".json_encode($aiResponse));

        // 8. Handle function_call (e.g. get_user_address_data)
        if (!empty($aiResponse['choices'][0]['message']['function_call'])) {
            Logger::debug("312.callOpenAI end: {$text}");

            $fc = $aiResponse['choices'][0]['message']['function_call'];
            Logger::debug("316.callOpenAI function_call: " . json_encode($fc));

            // Decode arguments (JSON string inside)
            $args = json_decode($fc['arguments'], true);

            // Extract values
            $fullname = $args['fullname'] . "";
            $kelurahan = $args['kelurahan'] . "";
            $kecamatan = $args['kecamatan'] . "";
            $alamat_jalan = $args['alamat_jalan'] . "";
            $email = $args['contact_info']['email'] . "";
            $phone_number = $args['contact_info']['phone_number'] . "";

            Logger::debug("335.fullname: {$fullname}");

            // Build content from function call data
            $content = "Berikut data Anda:\n";
            $content .= "Nama Lengkap   : $fullname\n";
            $content .= "Kelurahan      : $kelurahan\n";
            $content .= "Kecamatan      : $kecamatan\n";
            $content .= "Alamat Jalan   : $alamat_jalan\n";
            $content .= "Email          : $email\n";
            $content .= "No. HP         : $phone_number";
            
            // Save to dataprofile column
            $sql = "UPDATE users SET dataprofile = :dataprofile, lastupdatedate = NOW() WHERE whatsapp = :whatsapp";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':dataprofile' => $content,
                ':whatsapp' => $sender
            ]);

            Logger::debug("✅ dataprofile updated for {$sender}");

            $replyArr = $this->executeFunctionCall($fc);
            $reply = nl2br($replyArr['message'] ?? 'Tidak ada respon.');

            
            Logger::debug("317.executeFunctionCall end: {$reply}");

            return ['result' => $content];
            
            
        }

        // 9. Normal chat response
        $content = $aiResponse['choices'][0]['message']['content'] ?? '';
        $content = str_replace('*', '', $content);
        $content = nl2br($content);

        // ======== SHOPPING CART UPDATE MECHANISM ===========
        $lastresponseAI = $content;
        Logger::debug("Line 320.src/ChatHandler.php: chatHistories: " . json_encode($lastresponseAI));

        // CHECK IF HISTORY CONTAINS SHOPPING CART INFO
        $defaultPrompt = $this->loadPromptFromDB('CHECK_SHOPPING_CART');
        Logger::debug("Line 328.src/ChatHandler.php: defaultPrompt: " . json_encode($defaultPrompt));

        // Build messages for OpenAI
        $messages = $this->buildMessages(
            [],   
            $defaultPrompt,    
            $lastresponseAI 
        );

        Logger::debug("Line 332.src/ChatHandler.php: messages: " . json_encode($messages));
        $aiResponse = $this->callOpenAI($messages, $settings['OPEN_AI_KEY'], $model, $functions);
        Logger::debug("Line 337.src/ChatHandler.php: messages: " . json_encode($aiResponse));

        $contentcheckshopart = $aiResponse['choices'][0]['message']['content'] ?? 'Tidak ada respons dari AI.';
        Logger::debug("364. content end: {$contentcheckshopart}");

        $decodedContent = json_decode($contentcheckshopart, true);
        $cartJson = "";

        if (is_array($decodedContent) && !empty($decodedContent['info_cart'])) {
            $cartJson = json_encode($decodedContent['order']);
            $sql = "UPDATE users 
                    SET current_shoppingcart = :cartJson,
                        lastupdatedate = NOW()
                    WHERE whatsapp = :whatsapp";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':cartJson' => $cartJson,
                ':whatsapp' => $sender
            ]);

            Logger::debug("✅ current_shoppingcart updated for whatsapp {$sender}");
        } else {
            Logger::debug("⚠️ Tidak ada info_cart dalam response, update dilewati.");
        }

        $this->saveHistory(
            $text,
            $settings['PINECONE_NAMESPACE'],
            $model,
            json_encode($aiResponse),
            $content,
            $aiResponse['usage'] ?? []
        );

        return ['result' => $content];
    }

    // ============= HELPER METHODS ===========

    private function getIncomingText(): string
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        return trim($data['message'] ?? '');
    }

    private function fetchSettings(array $keys): array
    {
        $in = str_repeat('?,', count($keys) - 1) . '?';
        $stmt = $this->pdo->prepare(
            "SELECT `key`, `value`
             FROM settings
             WHERE `key` IN ($in)"
        );
        $stmt->execute($keys);
        $rows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        return [
            'OPEN_AI_KEY' => $rows['OPEN_AI_KEY'] ?? '',
            'PINECONE_API_KEY' => $rows['PINECONE_API_KEY'] ?? '',
            'PINECONE_INDEX_NAME' => $rows['PINECONE_INDEX_NAME'] ?? '',
            'PINECONE_NAMESPACE' => $rows['PINECONE_NAMESPACE'] ?? '',
            'modelgpt' => $rows['modelgpt'] ?? '',
        ];
    }

    private function getEmbedding(string $text, string $apiKey): array
    {
        $ch = curl_init('https://api.openai.com/v1/embeddings');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'input' => $text,
                'model' => 'text-embedding-ada-002'
            ]),
        ]);
        $res = curl_exec($ch);
        curl_close($ch);
        $json = json_decode($res, true);
        return $json['data'][0]['embedding'] ?? [];
    }

    private function queryPinecone(array $vector, array $settings): array
    {
        $pineconeIndex = $settings['PINECONE_INDEX_NAME'];
        $url = "https://{$pineconeIndex}.pinecone.io/query";
        Logger::debug("Querying Pinecone URL: {$url}");

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Api-Key: ' . $settings['PINECONE_API_KEY']
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'topK' => 10,
                'namespace' => $settings['PINECONE_NAMESPACE'],
                'vector' => $vector,
                'includeValues' => true
            ]),
        ]);
        $res = curl_exec($ch);
        curl_close($ch);
        $json = json_decode($res, true);
        return $json['matches'] ?? [];
    }

    private function assembleContext(array $matches): string
    {
        Logger::info("assembleContext() started with " . count($matches) . " raw matches");
        $ctx = $this->defaultPrompt . "\n\n";
        Logger::debug("Default prompt prepended, current context length: " . strlen($ctx));

        foreach ($matches as $index => $m) {
            $score = $m['score'] ?? 0;

            if ($score < 0.55) {
                Logger::debug("Match #{$index} skipped (score {$score} < 0.55)");
                continue;
            }

            Logger::debug("Match #{$index} accepted (score {$score} >= 0.55)");

            $stmt = $this->pdo->prepare(
                "SELECT content_information
                 FROM information
                 WHERE id = ?"
            );
            $stmt->execute([$m['id']]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            Logger::debug("score lolos: {$m['id']} score {$score} )");

            if (empty($row['content_information'])) {
                Logger::warning("Match #{$index} has empty content_information for id {$m['id']}");
                continue;
            }

            $content = $row['content_information'];
            Logger::debug("Appending content for match #{$index}, length: " . strlen($content));
            $ctx .= "\n" . $content;
        }

        $finalLength = strlen($ctx);
        Logger::info("assembleContext() finished, final context length: {$finalLength}");
        Logger::info("ctx() finished, final ctx : {$ctx}");
        return $ctx;
    }

    private function getUserDataProfile(string $whatsapp): ?string
    {
        $sql = "SELECT dataprofile FROM users WHERE whatsapp = :whatsapp LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':whatsapp' => $whatsapp]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result['dataprofile'] ?? null;
    }

    /**
     * Build message array for OpenAI
     */
    private function buildMessages(array $history, string $systemPrompt, string $userPrompt = ''): array
    {
        $messages = [
            ['role' => 'system', 'content' => $systemPrompt]
        ];

        // Add history if exists
        foreach ($history as $msg) {
            $messages[] = $msg;
        }

        // Add latest user prompt if exists
        if ($userPrompt !== '') {
            $messages[] = [
                'role' => 'user',
                'content' => $userPrompt
            ];
        }

        return $messages;
    }

    private function getOpenAIFunctions(): array
    {
        return [
            [
                'name' => 'get_user_address_data',
                'description' => 'Collect user address details and check if delivery is available',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'fullname' => ['type' => 'string'],
                        'kelurahan' => ['type' => 'string'],
                        'kecamatan' => ['type' => 'string'],
                        'alamat_jalan' => ['type' => 'string'],
                        'contact_info' => [
                            'type' => 'object',
                            'properties' => [
                                'email' => ['type' => 'string'],
                                'phone_number' => ['type' => 'string'],
                            ],
                            'required' => ['phone_number']
                        ]
                    ],
                    'required' => ['fullname', 'kelurahan', 'kecamatan', 'alamat_jalan', 'contact_info']
                ]
            ]
        ];
    }

    private function callOpenAI(array $messages, string $apiKey, string $model, array $functions = null): array
    {
        $payload = [
            'model' => $model,
            'messages' => $messages,
            'temperature' => 0.2
        ];
        
        if ($functions) {
            $payload['functions'] = $functions;
        }

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
        ]);
        $res = curl_exec($ch);
        curl_close($ch);
        return json_decode($res, true);
    }

    private function getLastChatHistory(string $whatsapp, int $limit = 15): array
    {
        // Validate limit
        $limit = max(1, min($limit, 100));

        $sql = "SELECT human, ai, chatdate 
                FROM chat_history 
                WHERE whatsapp = :whatsapp 
                  AND chatdate >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
                ORDER BY chatdate DESC 
                LIMIT $limit";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':whatsapp' => $whatsapp]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getUserProfile(): string
    {
        $stmt = $this->pdo->prepare("
            SELECT dataprofile 
            FROM users  
            WHERE whatsapp = :whatsapp 
            LIMIT 1
        ");
        $stmt->execute([':whatsapp' => $this->sender]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $dataProfile = "Data Pelanggan: \n\n";
        if (!empty($row['dataprofile'])) {
            $dataProfile .= $row['dataprofile'];
        }

        return $dataProfile;
    }

    private function executeFunctionCall(array $fc): array
    {
        $args = json_decode($fc['arguments'] ?? '{}', true);
        
        $allowed = [
            "Kelapa Gading Barat", "Kelapa Gading Timur", "Pegangsaan Dua", "Cilincing", "Kalibaru", "Marunda",
            "Rorotan", "Semper Barat", "Semper Timur", "Sukapura", "Cempaka Putih Timur", "Cempaka Putih Barat",
            "Rawasari", "Koja", "Rawa Badak Selatan", "Tugu Selatan", "Lagoa", "Rawa Badak Utara", "Tugu Utara",
            "Kamal Muara", "Pejagalan", "Kapuk Muara", "Penjaringan", "Pluit", "Ancol", "Pademangan Barat",
            "Pademangan Timur", "Tanjung Priok", "Kebon Bawang", "Sungai Bambu", "Papanggo", "Sunter Agung",
            "Sunter Jaya", "Warakas", "Cipinang Jati", "Jatinegara Kaum", "Kayu Putih", "Pisangan Timur",
            "Pulo Gadung", "Rawamangun", "Cakung Barat", "Cakung Timur", "Jatinegara", "Penggilingan",
            "Pulo Gebang", "Rawa Terate", "Ujung Menteng", "Bambu Apus", "Ceger", "Cilangkap", "Cipayung",
            "Lubang Buaya", "Munjul", "Pondok Ranggon", "Setu", "Cibubur", "Ciracas", "Kelapa Dua Wetan",
            "Rambutan", "Susukan", "Duren Sawit", "Klender", "Malaka Jaya", "Malaka Sari", "Pondok Bambu",
            "Pondok Kelapa", "Pondok Kopi", "Bali Mester", "Bidara Cina", "Cipinang Besar Selatan",
            "Cipinang Besar Utara", "Cipinang Cempedak", "Cipinang Muara", "Kampung Melayu", "Rawa Bunga",
            "Balekambang", "Batu Ampar", "Cawang", "Cililitan", "Dukuh", "Kramat Jati", "Tengah",
            "Cipinang Melayu", "Halim Perdana Kusuma", "Kebon Pala", "Makasar", "Pinang Ranti",
            "Kayu Manis", "Kebon Manggis", "Pal Meriam", "Pisangan Baru", "Utan Kayu Selatan", "Utan Kayu Utara"
        ];

        if (($fc['name'] ?? '') === 'get_user_address_data') {
            $kel = $args['kelurahan'] ?? '';
            if (in_array($kel, $allowed)) {
                return [
                    'status' => 'success',
                    'message' => 'Delivery available.',
                    'data' => $args
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => "Delivery not available to kelurahan: {$kel}."
                ];
            }
        }

        return [
            'status' => 'error',
            'message' => 'Unknown function call.'
        ];
    }

    private function saveOrder(
        string $orderid,
        string $whatsapp,
        string $content,
        string $order_date,
        int $ispaid,
        string $paid_date,
        string $note
    ): void {
        Logger::debug('saveOrder() called', [
            'orderid' => $orderid,
            'whatsapp' => $whatsapp,
            'order_len' => strlen($content),
            'order_date' => $order_date
        ]);

        try {
            $this->pdo->beginTransaction();

            /* -------------------------------------------------
             * 1. UPSERT into `order`
             * -------------------------------------------------*/
            $sql = "INSERT INTO `order`
                    (`order_id`, `sender`, `order_description`, `order_date`,
                     `ispaid`, `paid_date`, `note`)
                    VALUES
                    (:orderid, :whatsapp, :order_desc, :order_date,
                     :ispaid, :paid_date, :note)
                    ON DUPLICATE KEY UPDATE
                     order_description = VALUES(order_description),
                     order_date = VALUES(order_date),
                     ispaid = VALUES(ispaid),
                     paid_date = VALUES(paid_date),
                     note = VALUES(note)";

            $stmt = $this->pdo->prepare($sql);
            $content = strip_tags($content);
            $stmt->execute([
                ':orderid' => $orderid,
                ':whatsapp' => $whatsapp,
                ':order_desc' => $content,
                ':order_date' => $order_date,
                ':ispaid' => $ispaid,
                ':paid_date' => $paid_date,
                ':note' => $note
            ]);

            /* -------------------------------------------------
             * 2. UPDATE users (denormalized cache)
             * -------------------------------------------------*/
            $sql = "UPDATE users
                    SET orderid = :orderid,
                        orderdesc = :order_desc,
                        lastupdatedate = :order_date,
                        current_shoppingcart = ''
                    WHERE whatsapp = :whatsapp";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':orderid' => $orderid,
                ':order_desc' => $content,
                ':order_date' => $order_date,
                ':whatsapp' => $whatsapp
            ]);

            $this->pdo->commit();
            Logger::info("saveOrder(): order {$orderid} saved for {$whatsapp}");
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            Logger::error("saveOrder(): failed - " . $e->getMessage());
            throw $e; // let the caller decide what to do
        }
    }
    
    private function getCurrentShoppingCart(string $whatsapp): ?string
    {
        Logger::debug("Mengambil current_shoppingcart untuk WhatsApp: {$whatsapp}");

        $stmt = $this->pdo->prepare("
            SELECT current_shoppingcart
            FROM users
            WHERE whatsapp = :whatsapp
            LIMIT 1
        ");
        $stmt->execute([':whatsapp' => $whatsapp]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && !empty($row['current_shoppingcart'])) {
            Logger::debug("Shopping cart ditemukan: " . $row['current_shoppingcart']);
            return $row['current_shoppingcart'];
        }

        Logger::debug("Tidak ada shopping cart ditemukan untuk WhatsApp: {$whatsapp}");
        return null;
    }

    private function mapHistoryToMessages(array $histories): array
    {
        $messages = [];

        // Sort from oldest to newest
        $histories = array_reverse($histories);

        foreach ($histories as $history) {
            if (!empty($history['human'])) {
                $messages[] = [
                    'role' => 'user',
                    'content' => $history['human']
                ];
            }
            if (!empty($history['ai'])) {
                $messages[] = [
                    'role' => 'assistant',
                    'content' => $history['ai']
                ];
            }
        }

        return $messages;
    }

    private function generateorderid($whatsappNumber): string
    {
        // Remove any special characters from the WhatsApp number
        $cleanNumber = preg_replace('/\D/', '', $whatsappNumber);

        // Get the current timestamp
        $timestamp = time();

        // Combine the cleaned number and timestamp, then hash it
        return 'ORD-' . strtoupper(substr(md5($cleanNumber . $timestamp), 0, 8));
    }

    private function saveHistory(
        string $human,
        string $namespace,
        string $model,
        string $jsonResponse,
        string $aiContent,
        array $usage
    ): void {
        Logger::debug("saveHistory(): preparing to log chat for {$this->sender}");

        $stmt = $this->pdo->prepare(
            "INSERT INTO chat_history 
             (whatsapp, user_id, human, namespace, modelgpt, json_response, ai, prompt_token, completion_token, num_token, chatdate)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        
        date_default_timezone_set('Asia/Jakarta');
        $now = date('Y-m-d H:i:s');

        $params = [
            $this->sender,
            $this->sender,
            $human,
            $namespace,
            $model,
            $jsonResponse,
            $aiContent,
            $usage['prompt_tokens'] ?? 0,
            $usage['completion_tokens'] ?? 0,
            $usage['total_tokens'] ?? 0,
            $now
        ];

        try {
            $stmt->execute($params);
            Logger::debug("saveHistory(): record inserted OK for {$this->sender}");
        } catch (\Throwable $e) {
            Logger::error("saveHistory(): failed to insert record for {$this->sender} - " . $e->getMessage());
        }
    }
}