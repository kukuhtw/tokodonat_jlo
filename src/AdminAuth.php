<?php
/**
 * src/AdminAuth.php
 * Admin password creation and update functions
 */
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


class AdminAuth
{
    private $db;
    private $enabled;

    public function __construct(Database $db, bool $enabled)
    {
        $this->db      = $db;
        $this->enabled = $enabled;
    }

    public function createOrUpdatePassword(string $loginadmin, string $plaintextPassword): bool
    {
        if (! $this->enabled) {
            Logger::warning('AdminAuth: Feature disabled, cannot create or update admin.');
            return false;
        }
        // Check if loginadmin exists
        $row = $this->db->fetch(
            'SELECT adminid FROM msadmin WHERE loginadmin = :login',
            [':login' => $loginadmin]
        );

        if ($row) {
            // If exists, update password
            return $this->updatePassword((int)$row['adminid'], $plaintextPassword);
        }

        // Otherwise, insert new admin
        $hash = password_hash($plaintextPassword, PASSWORD_DEFAULT);
        $sql  = "INSERT INTO msadmin (loginadmin, loginpassword) VALUES (:login, :pass)";
        $count = $this->db->execute($sql, [
            ':login' => $loginadmin,
            ':pass'  => $hash,
        ]);

        if ($count > 0) {
            Logger::info("AdminAuth: New admin '{$loginadmin}' created.");
            return true;
        }

        Logger::error("AdminAuth: Failed to insert new admin '{$loginadmin}'.");
        return false;
    }

    /**
     * Update admin password by ID
     * @param int $adminId
     * @param string $newPlaintext
     * @return bool
     */
    public function updatePassword(int $adminId, string $newPlaintext): bool
    {
        if (! $this->enabled) {
            Logger::warning('AdminAuth: Feature disabled, cannot update password.');
            return false;
        }

        $hash = password_hash($newPlaintext, PASSWORD_DEFAULT);
        $sql  = 'UPDATE msadmin SET loginpassword = :pass WHERE adminid = :id';
        $count = $this->db->execute($sql, [
            ':pass' => $hash,
            ':id'   => $adminId,
        ]);

        if ($count > 0) {
            Logger::info("AdminAuth: Password updated for admin ID {$adminId}.");
            return true;
        }

        Logger::error("AdminAuth: Failed to update password for admin ID {$adminId}.");
        return false;
    }


    /**
     * Verify admin login
     */
    public function verify(string $loginadmin, string $plaintext): bool
    {
        $row = $this->db->fetch("SELECT adminid, loginpassword FROM msadmin WHERE loginadmin = :login", [':login' => $loginadmin]);
        if (! $row) return false;
        return password_verify($plaintext, $row['loginpassword']);
    }
}

?>