<?php
// public/form_chat.php

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

require __DIR__ . '/../bootstrap.php';

Logger::info('Loaded public/form_chat.php');

session_start();
Logger::debug('Session started');

// Initialize variables
$error   = '';
$success = '';

Logger::debug('Request method: ' . ($_SERVER['REQUEST_METHOD'] ?? '')); 
// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Logger::info('Processing form submission');
    $name     = trim($_POST['name']      ?? '');
    $email    = trim($_POST['email']     ?? '');
    $whatsapp = trim($_POST['whatsapp']  ?? '');
    Logger::debug("Received input name={\$name}, email={\$email}, whatsapp={\$whatsapp}");

    // Validate inputs
    if (empty($name) || empty($email) || empty($whatsapp)) {
        $error = 'Please fill in all fields';
        Logger::warning('Validation error: missing fields');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
        Logger::warning('Validation error: invalid email ' . $email);
    } else {
        // Format WhatsApp number (remove non-numeric characters)
        $whatsapp_clean = preg_replace('/[^0-9]/', '', $whatsapp);
        $_SESSION['testing_wa'] = 'web_' . $whatsapp_clean;
        $_SESSION['testing_name'] = $name;
        $_SESSION['testing_email'] = $email;

        Logger::debug('Formatted WhatsApp number: ' . $whatsapp_clean);

        // Set Jakarta timezone and current time
        date_default_timezone_set('Asia/Jakarta');
        $now = date('Y-m-d H:i:s');
        Logger::debug('Current timestamp: ' . $now);

        // Get user IP and agent
        $ip_address  = $_SERVER['REMOTE_ADDR']    ?? '';
        $user_agent  = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $whatsapp_db = 'web_' . $whatsapp_clean;
        Logger::debug("IP={$ip_address}, Agent={$user_agent}");

        try {
            // Check if user exists
            $check_sql = 'SELECT id FROM users WHERE whatsapp = :whatsapp';
            $stmt = $pdo->prepare($check_sql);
            $stmt->execute([':whatsapp' => $whatsapp_db]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            Logger::debug('Executed user existence query');

            if ($user) {
                Logger::info('Existing user found, ID=' . $user['id']);
                // Update existing user
                $update_sql = 'UPDATE users SET
                    name           = :name,
                    email          = :email,
                    lastupdatedate = :now,
                    ip_address     = :ip_address,
                    user_agent     = :user_agent,
                    lastlogin      = :now
                 WHERE whatsapp     = :whatsapp';
                $update_stmt = $pdo->prepare($update_sql);
                $update_stmt->execute([
                    ':name'       => $name,
                    ':email'      => $email,
                    ':now'        => $now,
                    ':ip_address' => $ip_address,
                    ':user_agent' => $user_agent,
                    ':whatsapp'   => $whatsapp_db,
                ]);
                Logger::info('User information updated successfully');
                $success = 'User information updated successfully!';

            } else {
                Logger::info('No existing user, proceeding to registration');
                // Insert new user
                $insert_sql = 'INSERT INTO users
                    (telegramid, telegramusername, whatsapp, name, email, quota_hour, regdate, ip_address, user_agent, lastlogin)
                 VALUES
                    (:telegramid, :telegramusername, :whatsapp, :name, :email, :quota, :now, :ip_address, :user_agent, :now)';
                $insert_stmt = $pdo->prepare($insert_sql);
                $insert_stmt->execute([
                    ':telegramid'       => '',
                    ':telegramusername' => '',
                    ':whatsapp'         => $whatsapp_db,
                    ':name'             => $name,
                    ':email'            => $email,
                    ':quota'            => 35,
                    ':now'              => $now,
                    ':ip_address'       => $ip_address,
                    ':user_agent'       => $user_agent,
                ]);
                Logger::info('New user registered successfully');
                $success = 'Registration successful!';
            }

        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
            Logger::error('Database exception: ' . $e->getMessage());
        }

        // Redirect to chat page if successful
        if ($success) {
            Logger::info('Redirecting user to chat.php');
            header('Location: chat.php');
            exit;
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Registration</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 500px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"],
        input[type="email"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }
        button:hover {
            background-color: #45a049;
        }
        .error {
            color: #f44336;
            margin-bottom: 15px;
        }
        .success {
            color: #4CAF50;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Chat Registration</h1>
        
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="name">Full Name:</label>
                <input type="text" id="name" name="name" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="whatsapp">WhatsApp Number:</label>
                <input type="text" id="whatsapp" name="whatsapp" required placeholder="6281234567890">
            </div>
            
            <button type="submit">Start Chatting</button>
        </form>
    </div>
</body>
</html>
