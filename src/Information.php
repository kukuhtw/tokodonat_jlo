
<?php
// src/Information.php

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
class Information
{
    /**
     * @var \PDO
     */
    private $pdo;

    public function __construct(Database $db)
    {
        // Sekarang getConnection() sudah ada
        $this->pdo = $db->getConnection();
    }

    /**
     * Simpan informasi ke tabel information
     *
     * @param string $namespace
     * @param string $contentInformation
     * @param bool $isPinecone
     * @param string $judul
     * @return bool
     */
    public function add(string $namespace, string $contentInformation, bool $isPinecone, string $judul): bool
    {
        // Set timezone to GMT+7 (Asia/Jakarta)
        $date      = new DateTime('now', new DateTimeZone('Asia/Jakarta'));
        $timestamp = $date->format('Y-m-d H:i:s');

        $sql = "INSERT INTO information
                    (namespace, content_information, ispinecone, judul, lastupdate, regdate)
                VALUES
                    (:namespace, :content_information, :ispinecone, :judul, :lastupdate, :regdate)";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':namespace'           => $namespace,
            ':content_information' => $contentInformation,
            ':ispinecone'          => $isPinecone ? 1 : 0,
            ':judul'               => $judul,
            ':lastupdate'          => $timestamp,
            ':regdate'             => $timestamp,
        ]);
    }

    /**
     * Ambil informasi berdasarkan ID
     *
     * @param int $id
     * @return array|null
     */
    public function getById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM information WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Ambil semua informasi
     *
     * @return array
     */
    public function getAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM information ORDER BY lastupdate DESC");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Update informasi berdasarkan ID
     *
     * @param int $id
     * @param string $contentInformation
     * @param bool $isPinecone
     * @param string $judul
     * @return bool
     */
    public function update(int $id, string $contentInformation, bool $isPinecone, string $judul): bool
    {
        // Set timezone to GMT+7 (Asia/Jakarta)
        $date      = new DateTime('now', new DateTimeZone('Asia/Jakarta'));
        $timestamp = $date->format('Y-m-d H:i:s');

        $sql = "UPDATE information
                SET content_information = :content_information,
                    ispinecone = :ispinecone,
                    judul = :judul,
                    lastupdate = :lastupdate
                WHERE id = :id";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':content_information' => $contentInformation,
            ':ispinecone'          => $isPinecone ? 1 : 0,
            ':judul'               => $judul,
            ':lastupdate'          => $timestamp,
            ':id'                  => $id,
        ]);
    }
}
