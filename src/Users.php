<?php
//// src/Users.php

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
class Users
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
     * Tambahkan user baru
     *
     * @param array $userData
     * @return bool
     */
    public function add(array $userData): bool
    {
        $date = new DateTime('now', new DateTimeZone('Asia/Jakarta'));
        $timestamp = $date->format('Y-m-d H:i:s');

        $sql = "INSERT INTO users
                    (id, telegramid, telegramusername, whatsapp, name, email, address_delivery, 
                     lastmessages, lastresponse, quota_hour, regdate, needhuman, fullname, 
                     orderid, orderdesc, lastupdatedate, dataprofile, ip_address, user_agent, lastlogin)
                VALUES
                    (:id, :telegramid, :telegramusername, :whatsapp, :name, :email, :address_delivery,
                     :lastmessages, :lastresponse, :quota_hour, :regdate, :needhuman, :fullname,
                     :orderid, :orderdesc, :lastupdatedate, :dataprofile, :ip_address, :user_agent, :lastlogin)";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':id' => $userData['id'],
            ':telegramid' => $userData['telegramid'],
            ':telegramusername' => $userData['telegramusername'],
            ':whatsapp' => $userData['whatsapp'] ?? null,
            ':name' => $userData['name'] ?? null,
            ':email' => $userData['email'] ?? null,
            ':address_delivery' => $userData['address_delivery'],
            ':lastmessages' => $userData['lastmessages'],
            ':lastresponse' => $userData['lastresponse'] ?? null,
            ':quota_hour' => $userData['quota_hour'] ?? 10,
            ':regdate' => $timestamp,
            ':needhuman' => $userData['needhuman'] ?? 0,
            ':fullname' => $userData['fullname'],
            ':orderid' => $userData['orderid'] ?? null,
            ':orderdesc' => $userData['orderdesc'] ?? null,
            ':lastupdatedate' => $timestamp,
            ':dataprofile' => $userData['dataprofile'] ?? null,
            ':ip_address' => $userData['ip_address'],
            ':user_agent' => $userData['user_agent'],
            ':lastlogin' => $timestamp
        ]);
    }

    /**
     * Ambil user berdasarkan ID
     *
     * @param int $id
     * @return array|null
     */
    public function getById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Ambil user berdasarkan Telegram ID
     *
     * @param string $telegramId
     * @return array|null
     */
    public function getByTelegramId(string $telegramId): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE telegramid = :telegramid LIMIT 1");
        $stmt->execute([':telegramid' => $telegramId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Ambil semua users
     *
     * @return array
     */
    public function getAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM users ORDER BY lastupdatedate DESC");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Update data user
     *
     * @param int $id
     * @param string|null $whatsapp
     * @param string|null $name
     * @param int|null $quotaHour
     * @return bool
     */
    public function update(int $id, ?string $whatsapp = null, ?string $name = null, ?int $quotaHour = null): bool
    {
        $date = new DateTime('now', new DateTimeZone('Asia/Jakarta'));
        $timestamp = $date->format('Y-m-d H:i:s');

        $sql = "UPDATE users SET 
                    whatsapp = :whatsapp,
                    name = :name,
                    quota_hour = :quota_hour,
                    lastupdatedate = :lastupdatedate
                WHERE id = :id";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':whatsapp' => $whatsapp,
            ':name' => $name,
            ':quota_hour' => $quotaHour,
            ':lastupdatedate' => $timestamp,
            ':id' => $id
        ]);
    }

    /**
     * Hapus user berdasarkan ID
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Update last login timestamp
     *
     * @param int $id
     * @return bool
     */
    public function updateLastLogin(int $id): bool
    {
        $date = new DateTime('now', new DateTimeZone('Asia/Jakarta'));
        $timestamp = $date->format('Y-m-d H:i:s');

        $stmt = $this->pdo->prepare("UPDATE users SET lastlogin = :lastlogin WHERE id = :id");
        return $stmt->execute([
            ':lastlogin' => $timestamp,
            ':id' => $id
        ]);
    }
}