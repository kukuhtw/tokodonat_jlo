<?php
// src/ChatService.php

use PDO;
use DateTime;
use DateTimeZone;

/**
 * Processes chat messages: calls AI API and logs history.
 */
class ChatService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Handles an incoming message for a sender.
     *
     * @param string $sender Sender identifier (WhatsApp number).
     * @return array ['reply' => string] to return as JSON.
     */
    public function process(string $sender): array
    {
        // Ambil pesan user dari request
        $message = trim($_POST['message'] ?? '');

        // Panggil AI untuk mendapatkan balasan
        $reply = $this->callAI($sender, $message);

        // Simpan chat history
        $this->saveHistory($sender, $message, $reply);

        return ['reply' => $reply];
    }

    /**
     * Stub untuk memanggil AI (misalnya OpenAI API).
     * Ganti dengan implementasi konkret.
     */
    private function callAI(string $sender, string $message): string
    {
        // TODO: Integrasi ke API AI (OpenAI, dll.)
        // Contoh sederhana:
        return "Echo: " . $message;
    }

    /**
     * Simpan chat history ke database.
     */
    private function saveHistory(string $sender, string $message, string $reply): void
    {
        $date = new DateTime('now', new DateTimeZone('Asia/Jakarta'));
        $timestamp = $date->format('Y-m-d H:i:s');

        $sql = "INSERT INTO chat_history (whatsapp, human, ai, chatdate) VALUES (:whatsapp, :human, :ai, :chatdate)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':whatsapp' => $sender,
            ':human'    => $message,
            ':ai'       => $reply,
            ':chatdate' => $timestamp,
        ]);
    }
}
