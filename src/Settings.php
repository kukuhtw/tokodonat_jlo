<?php
// src/Settings.php
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

/*
 * 
*/

class Settings
{
    private Database $db;
    public function __construct(Database $db)
    {
        $this->db = $db;
    }
    public function get(string $key): ?string
    {
        $row = $this->db->fetch('SELECT `value` FROM settings WHERE `key` = :key', [':key'=>$key]);
        return $row['value'] ?? null;
    }
    public function set(string $key, string $value): bool
    {
        $row = $this->db->fetch('SELECT id FROM settings WHERE `key` = :key', [':key'=>$key]);
        if ($row) {
            return $this->db->execute('UPDATE settings SET `value` = :value WHERE `key` = :key', [':value'=>$value,':key'=>$key])>0;
        }
        return $this->db->execute('INSERT INTO settings (`key`,`value`) VALUES (:key,:value)', [':key'=>$key,':value'=>$value])>0;
    }
}
?>
