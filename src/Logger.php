<?php
/**
 * src/Logger.php
 * Simple logger for tracking and error logging
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


class Logger
{
    /** @var string */
    private static $file;
    /** @var string */
    private static $level;

    public static function init(array $config)
    {
        self::$file  = $config['path'];
        self::$level = $config['level'];

        // ensure log directory exists
        $dir = dirname(self::$file);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
    }

    public static function log(string $level, string $message)
    {
        $date = (new DateTime())->format('Y-m-d H:i:s');
        $line = "[{$date}][{$level}] {$message}" . PHP_EOL;
        file_put_contents(self::$file, $line, FILE_APPEND);
    }

    public static function debug(string $message)
    {
        if (in_array('DEBUG', [self::$level, 'DEBUG'])) {
            self::log('DEBUG', $message);
        }
    }

    public static function info(string $message)
    {
        if (in_array(self::$level, ['DEBUG', 'INFO'])) {
            self::log('INFO', $message);
        }
    }

    public static function error(string $message)
    {
        self::log('ERROR', $message);
    }

    public static function warning(string $message)
    {
        if (in_array(self::$level, ['DEBUG', 'INFO', 'WARNING'])) {
            self::log('WARNING', $message);
        }
    }
}


?>