<?php
// public/view_users.php

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
session_start();
if (empty($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_user'])) {
        $id = (int)$_POST['id'];
        $pdo = $db->getConnection();
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $message = "User deleted successfully!";
    } elseif (isset($_POST['update_quota'])) {
        $id = (int)$_POST['id'];
        $quota_hour = (int)$_POST['quota_hour'];
        $pdo = $db->getConnection();
        $stmt = $pdo->prepare("UPDATE users SET quota_hour = :quota_hour WHERE id = :id");
        $stmt->execute([':quota_hour' => $quota_hour, ':id' => $id]);
        $message = "Quota updated successfully!";
    }
}

// Get all users
$pdo = $db->getConnection();
$stmt = $pdo->query("SELECT id, telegramid, telegramusername, whatsapp, name, email, quota_hour, regdate FROM users ORDER BY regdate DESC");
$users = $stmt->fetchAll(\PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Users - Donat JLO Chatbot</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body {
            padding: 20px;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 1200px;
        }
        .table-responsive {
            margin-top: 20px;
        }
        .alert {
            margin-top: 20px;
        }
    </style>
</head>
<body class="d-flex">

    <?php include 'nav_admin.php'; ?>

    <div class="container">
        <h1 class="text-center mb-4">User Management</h1>
        
        <?php if (!empty($message)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                       
                        <th>WhatsApp</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Quota/Hour</th>
                        <th>Registration Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['id']); ?></td>
                            
                            <td><?php echo htmlspecialchars($user['whatsapp'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($user['name'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($user['email'] ?? 'N/A'); ?></td>
                            <td>
                                <form method="post" class="d-flex">
                                    <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                    <input type="number" name="quota_hour" value="<?php echo $user['quota_hour']; ?>" class="form-control form-control-sm" style="width: 70px;">
                                    <button type="submit" name="update_quota" class="btn btn-sm btn-primary ms-2">Update</button>
                                </form>
                            </td>
                            <td><?php echo htmlspecialchars($user['regdate']); ?></td>
                            <td>
                                <form method="post" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                    <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                    <button type="submit" name="delete_user" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>