<?php
// public/view_chat_history.php

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

// Initialize ChatHistory
$chatHistory = new ChatHistory($db);

// Handle filters
$startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-7 days'));
$endDate = $_GET['end_date'] ?? date('Y-m-d');
$whatsappFilter = trim($_GET['whatsapp'] ?? '');
$humanFilter = trim($_GET['human'] ?? '');
$aiFilter = trim($_GET['ai'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Build WHERE conditions
$where = [];
$params = [];

// Date range filter
$where[] = "chatdate BETWEEN :start_date AND :end_date + INTERVAL 1 DAY";
$params[':start_date'] = $startDate;
$params[':end_date'] = $endDate;

// WhatsApp filter
if (!empty($whatsappFilter)) {
    $where[] = "whatsapp LIKE :whatsapp";
    $params[':whatsapp'] = "%{$whatsappFilter}%";
}

// Human message filter
if (!empty($humanFilter)) {
    $where[] = "human LIKE :human";
    $params[':human'] = "%{$humanFilter}%";
}

// AI response filter
if (!empty($aiFilter)) {
    $where[] = "ai LIKE :ai";
    $params[':ai'] = "%{$aiFilter}%";
}

// Only show non-deleted records
$where[] = "isdeleted = 0";

// Combine WHERE conditions
$whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Get total count
$countSql = "SELECT COUNT(*) FROM chat_history {$whereClause}";
$countStmt = $db->getConnection()->prepare($countSql);
foreach ($params as $k => $v) {
    $countStmt->bindValue($k, $v);
}
$countStmt->execute();
$total = (int)$countStmt->fetchColumn();
$totalPages = (int)ceil($total / $perPage);

// Get chat history data
$sql = "SELECT * FROM chat_history {$whereClause} ORDER BY chatdate DESC LIMIT :limit OFFSET :offset";
$stmt = $db->getConnection()->prepare($sql);

// Bind parameters
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$chats = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get token statistics
$tokenStats = $chatHistory->getTokenStatistics();

// Function to clean and format message content
function cleanMessageContent($text) {
    if (empty($text)) return '';
    
    // Remove HTML tags first
    $text = strip_tags($text);
    
    // Remove markdown formatting
    $text = preg_replace('/\*\*(.*?)\*\*/', '$1', $text); // Bold **text**
    $text = preg_replace('/\*(.*?)\*/', '$1', $text);     // Italic *text*
    $text = preg_replace('/__(.*?)__/', '$1', $text);     // Bold __text__
    $text = preg_replace('/_(.*?)_/', '$1', $text);       // Italic _text_
    $text = preg_replace('/`(.*?)`/', '$1', $text);       // Code `text`
    
    // Remove extra whitespace and line breaks
    $text = preg_replace('/\s+/', ' ', $text);
    $text = trim($text);
    
    // Remove common HTML entities
    $text = str_replace(['&nbsp;', '&lt;', '&gt;', '&amp;'], [' ', '<', '>', '&'], $text);
    
    return $text;
}

// Function to limit words with better handling
function limit_words($text, $limit = 100) {
    if (empty($text)) return '';
    
    $text = cleanMessageContent($text);
    
    if (mb_strlen($text) > $limit) {
        return mb_substr($text, 0, $limit) . '...';
    }
    return $text;
}




?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat History - Donat JLO Chatbot</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body {
            padding: 20px;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 1800px;
        }
        .table-responsive {
            margin-top: 20px;
        }
        .filter-section {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }
        .token-stats {
            background-color: #e9ecef;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .chat-message {
            max-width: 400px;
            white-space: pre-wrap;
            word-break: break-word;
        }
        /* Tambahkan atau ganti CSS yang ada dengan ini */
.message-preview {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
    cursor: pointer;
    color: #0d6efd;
    text-decoration: none; /* Hapus underline default */
    line-height: 1.4;
    max-height: 2.8em; /* Batasi tinggi untuk 2 baris */
}

.message-preview:hover {
    color: #0a58ca;
    text-decoration: underline; /* Hanya tampil saat hover */
}

/* Fix untuk modal content */
.modal-body pre {
    white-space: pre-wrap;
    word-break: break-word;
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    max-height: 70vh;
    overflow-y: auto;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    font-size: 14px;
    line-height: 1.5;
}

/* Improved table styling */
.chat-message {
    max-width: 300px;
    white-space: normal;
    word-break: break-word;
    vertical-align: top;
}

/* Fix untuk debugging elements yang tersisa */
td:before {
    content: none !important;
}

/* Remove any unwanted content from data attributes */
[data-message]::before,
[data-message]::after {
    content: none !important;
}
    </style>
</head>
<body class="d-flex">
  <?php include 'nav_admin.php'; ?>
    <div class="container">
        <h1 class="text-center mb-4">Chat History</h1>
        
        <!-- Token Statistics -->
        <div class="token-stats">
            <div class="row">
                <div class="col-md-3">
                    <strong>Total Chats:</strong> <?= number_format($total) ?>
                </div>
                <div class="col-md-3">
                    <strong>Prompt Tokens:</strong> <?= number_format($tokenStats['total_prompt_tokens'] ?? 0) ?>
                </div>
                <div class="col-md-3">
                    <strong>Completion Tokens:</strong> <?= number_format($tokenStats['total_completion_tokens'] ?? 0) ?>
                </div>
                <div class="col-md-3">
                    <strong>Total Tokens:</strong> <?= number_format($tokenStats['total_tokens'] ?? 0) ?>
                </div>
            </div>
        </div>
        
        <!-- Filter Section -->
        <div class="filter-section">
            <form method="get" class="row g-3">
                <div class="col-md-3">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="<?= htmlspecialchars($startDate) ?>">
                </div>
                <div class="col-md-3">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="<?= htmlspecialchars($endDate) ?>">
                </div>
                <div class="col-md-2">
                    <label for="whatsapp" class="form-label">WhatsApp</label>
                    <input type="text" class="form-control" id="whatsapp" name="whatsapp" value="<?= htmlspecialchars($whatsappFilter) ?>" placeholder="Filter by WhatsApp">
                </div>
                <div class="col-md-2">
                    <label for="human" class="form-label">Human Message</label>
                    <input type="text" class="form-control" id="human" name="human" value="<?= htmlspecialchars($humanFilter) ?>" placeholder="Filter human messages">
                </div>
                <div class="col-md-2">
                    <label for="ai" class="form-label">AI Response</label>
                    <input type="text" class="form-control" id="ai" name="ai" value="<?= htmlspecialchars($aiFilter) ?>" placeholder="Filter AI responses">
                </div>
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                    <a href="view_chat_history.php" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
        
        <!-- Pagination Top -->
        <?php if ($totalPages > 1): ?>
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>">First</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">Previous</a>
                    </li>
                <?php endif; ?>
                
                <?php for ($i = max(1, $page - 2); $i <= min($page + 2, $totalPages); $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">Next</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $totalPages])) ?>">Last</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>
        
        <!-- Chat History Table -->
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th>WhatsApp</th>
                        <th>Human Message</th>
                        <th>AI Response</th>
                        <th>Model</th>
                        <th>Tokens</th>
                       
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($chats as $chat):
                $ch = $chat['human'] ;
               $ch = trim(strip_tags($ch));

               //echo "<!-- Debug: [$ch] -->";

                $cai = $chat['ai'] ;
               $cai = trim(strip_tags($cai));
               $cai = str_replace("<br />","",$cai);
               $cai = str_replace("_","",$cai);
               $cai = htmlspecialchars($cai); // prevent XSS
$cai = str_replace('_', '&#95;', $cai); // aman, tidak diubah menjadi format Markdown atau underline
$cai = str_replace('_', '<span style="text-decoration:none;">_</span>', $cai);
$cai = strip_tags($cai); // buang semua HTML
$cai = htmlspecialchars($cai); // encode karakter khusus

$cai = str_replace('_', '&#95;', $cai);
$cai = preg_replace('/(_+)/', '<span style="text-decoration:none;">$1</span>', htmlspecialchars($cai));


                     ?>
                        <tr>
                            <td><?= htmlspecialchars($chat['id']) ?></td>
                            <td><?= htmlspecialchars($chat['chatdate']) ?></td>
                            <td>1111. <?= htmlspecialchars($chat['whatsapp'] ?? 'N/A') ?></td>
                            <td class="chat-message">22222.
                               <div class="message-preview"
       data-bs-toggle="modal"
       data-bs-target="#messageModal"
       data-message="<?= htmlspecialchars($ch) ?>"
       data-title="Human Message (ID: <?= htmlspecialchars($chat['id']) ?>)"
       style="cursor:pointer; color:blue;">
    <?= htmlspecialchars(limit_words($ch, 200)) ?>
  </div>
                            </td>
                            <td class="chat-message">
                                <div class="message-preview" data-bs-toggle="modal" data-bs-target="#messageModal" 
                                    data-message="<?= htmlspecialchars($cai) ?>" 
                                    data-title="AI Response (ID: <?= htmlspecialchars($chat['id']) ?>)">
                                    <?= htmlspecialchars(limit_words($cai)) ?>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($chat['modelgpt'] ?? 'N/A') ?></td>
                            <td>
                                P: <?= htmlspecialchars($chat['prompt_token']) ?><br>
                                C: <?= htmlspecialchars($chat['completion_token']) ?><br>
                                T: <?= htmlspecialchars($chat['num_token']) ?>
                            </td>
                            
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Message Modal -->
        <div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="messageModalLabel">Message Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <pre id="modalMessageContent"></pre>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Pagination Bottom -->
        <?php if ($totalPages > 1): ?>
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>">First</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">Previous</a>
                    </li>
                <?php endif; ?>
                
                <?php for ($i = max(1, $page - 2); $i <= min($page + 2, $totalPages); $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">Next</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $totalPages])) ?>">Last</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
       // Ganti JavaScript yang ada dengan ini
document.addEventListener('DOMContentLoaded', function() {
    const messageModal = document.getElementById('messageModal');
    if (messageModal) {
        messageModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const message = button.getAttribute('data-message');
            const title = button.getAttribute('data-title');
            
            const modalTitle = messageModal.querySelector('.modal-title');
            const modalBody = messageModal.querySelector('.modal-body pre');
            
            // Clean the message content
            const cleanedMessage = message
                .replace(/&lt;/g, '<')
                .replace(/&gt;/g, '>')
                .replace(/&amp;/g, '&')
                .replace(/&quot;/g, '"')
                .replace(/&#039;/g, "'")
                .trim();
            
            modalTitle.textContent = title;
            modalBody.textContent = cleanedMessage;
        });
    }
    
    // Debug: Remove any stray debugging content
    document.querySelectorAll('td').forEach(cell => {
        const text = cell.textContent;
        if (text.includes('1111.') || text.includes('22222.')) {
            cell.innerHTML = cell.innerHTML
                .replace('1111.', '')
                .replace('22222.', '');
        }
    });
});
    </script>
</body>
</html>