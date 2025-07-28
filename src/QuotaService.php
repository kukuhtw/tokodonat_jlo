<?php
// src/QuotaService.php
class QuotaService
{
    private PDO $pdo;
    private string $whatsapp;

    public function __construct(PDO $pdo, string $whatsapp)
    {
        $this->pdo = $pdo;
        $this->whatsapp = $whatsapp;
        Logger::info("QuotaService instantiated for {$this->whatsapp}");
        
    }

    

    public function getTotalChatLastHour(): int
    {
        try {
            date_default_timezone_set('Asia/Jakarta');
            $oneHourAgo = (new DateTime('-1 hour'))->format('Y-m-d H:i:s');

            Logger::debug("Calculating chats since {$oneHourAgo} for {$this->whatsapp}");

            $stmt = $this->pdo->prepare(
                "SELECT COUNT(*) AS total_chat
                 FROM chat_history
                 WHERE whatsapp = ?
                   AND chatdate >= ?"
            );
            $stmt->execute([$this->whatsapp, $oneHourAgo]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            $total = (int) ($row['total_chat'] ?? 0);
            Logger::info("Found {$total} chats in the last hour for {$this->whatsapp}");

            return $total;
        } catch (\Exception $e) {
            Logger::error("Error in getTotalChatLastHour: " . $e->getMessage());
            return 0;
        }
    }

    public function getHourlyQuota(): int
    {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT quota_hour
                 FROM users
                 WHERE whatsapp = ?"
            );
            $stmt->execute([$this->whatsapp]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            $quota = (int) ($row['quota_hour'] ?? 35);
            Logger::info("Hourly quota for {$this->whatsapp} is {$quota}");

            return $quota;
        } catch (\Exception $e) {
            Logger::error("Error in getHourlyQuota: " . $e->getMessage());
            return 35;
        }
    }

    public function hasQuota(): bool
    {
        $total = $this->getTotalChatLastHour();
        $quota = $this->getHourlyQuota();
        $has   = $total < $quota;

        if ($has) {
            Logger::info("{$this->whatsapp} has quota ({$total}/{$quota})");
        } else {
            Logger::warning("{$this->whatsapp} exceeded quota ({$total}/{$quota})");
        }

        return $has;
    }
}
