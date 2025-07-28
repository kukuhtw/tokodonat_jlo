<?php
// src/ChatHistory.php

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
class ChatHistory
{
    /**
     * @var \PDO
     */
    private $pdo;

    public function __construct(Database $db)
    {
        $this->pdo = $db->getConnection();
    }

    /**
     * Get chat history by ID
     *
     * @param int $id
     * @return array|null
     */
    public function getById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM chat_history WHERE id = :id AND isdeleted = 0 LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Get all chat history records
     *
     * @param int $limit
     * @return array
     */
    public function getAll(int $limit = 100): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM chat_history WHERE isdeleted = 0 ORDER BY chatdate DESC LIMIT :limit");
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get chat history by Telegram ID
     *
     * @param string $telegramId
     * @param int $limit
     * @return array
     */
    public function getByTelegramId(string $telegramId, int $limit = 20): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM chat_history WHERE telegramid = :telegramid AND isdeleted = 0 ORDER BY chatdate DESC LIMIT :limit");
        $stmt->bindValue(':telegramid', $telegramId);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get chat history by WhatsApp number
     *
     * @param string $whatsapp
     * @param int $limit
     * @return array
     */
    public function getByWhatsApp(string $whatsapp, int $limit = 20): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM chat_history WHERE whatsapp = :whatsapp AND isdeleted = 0 ORDER BY chatdate DESC LIMIT :limit");
        $stmt->bindValue(':whatsapp', $whatsapp);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get chat history by date range
     *
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getByDateRange(string $startDate, string $endDate): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM chat_history WHERE chatdate BETWEEN :start_date AND :end_date AND isdeleted = 0 ORDER BY chatdate DESC");
        $stmt->execute([
            ':start_date' => $startDate,
            ':end_date' => $endDate
        ]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get total token usage statistics
     *
     * @return array
     */
    public function getTokenStatistics(): array
    {
        $stmt = $this->pdo->query("SELECT 
            SUM(prompt_token) as total_prompt_tokens,
            SUM(completion_token) as total_completion_tokens,
            SUM(num_token) as total_tokens,
            COUNT(id) as total_chats
            FROM chat_history 
            WHERE isdeleted = 0");
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
}