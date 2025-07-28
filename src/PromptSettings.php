 <?php
 // src/PromptSettings.php
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

class PromptSettings {
    private Database $db;
    public function __construct(Database $db) { $this->db = $db; }
    public function getAll(): array {
        return $this->db->fetchAll('SELECT id,promptid,instruction FROM prompts ORDER BY promptid');
    }
    public function set(string $promptid, string $instruction): bool {
        $row = $this->db->fetch('SELECT id FROM prompts WHERE promptid=:pid', [':pid'=>$promptid]);
        if ($row) {
            return $this->db->execute(
                'UPDATE prompts SET instruction=:ins WHERE promptid=:pid',
                [':ins'=>$instruction,':pid'=>$promptid]
            ) > 0;
        }
        return $this->db->execute(
            'INSERT INTO prompts (promptid,instruction) VALUES (:pid,:ins)',
            [':pid'=>$promptid,':ins'=>$instruction]
        ) > 0;
    }
}


?>
