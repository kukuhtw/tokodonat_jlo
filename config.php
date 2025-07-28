<?php
/**
 * config.php
 * Database configuration settings
 * 
 * 

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

// config.php
return [
    'db' => [
        'host'       => 'localhost',
        'dbname'     => 'tokodonat_jlo',
        'user'       => 'root',
        'password'   => '',
        'charset'    => 'utf8mb4',
    ],
    'logger' => [
        'path' => __DIR__ . '/logs/app.log',
        'level' => 'DEBUG',
    ],    
    'features' => [
        'admin_password' => true,  // <— pastikan ini ada!
    ],
];


