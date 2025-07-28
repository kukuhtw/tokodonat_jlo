<?php
// src/Order.php

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

class Order
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
     * Retrieve a single order by numeric id
     *
     * @param int $orderId
     * @return array|null Associative array of the order or null if not found
     */
    public function view(int $orderId): ?array
    {
        $sql = "SELECT *
                FROM `order`
                WHERE id = :id
                LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $orderId, \PDO::PARAM_INT);
        $stmt->execute();

        $order = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $order ?: null;
    }
}