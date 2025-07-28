<?php
// public/chat.php
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

// Redirect to registration if session is missing
if (empty($_SESSION['testing_wa'])) {
    header('Location: form_chat.php');
    exit;
}

// Fetch the current user row so we can display name / email / whatsapp
$whatsapp = $_SESSION['testing_wa'];
$name  =  $_SESSION['testing_name'] ;
$email =   $_SESSION['testing_email'];
$stmt = $pdo->prepare('SELECT name, email, whatsapp FROM users WHERE whatsapp = :wa');
$stmt->execute([':wa' => $whatsapp]);
$user = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['name' => 'Guest', 'email' => '-', 'whatsapp' => $whatsapp];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Chat Donat JLO</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body, html { height: 100%; }
    .chat-wrapper {
      display: flex;
      flex-direction: column;
      height: 100%;
      background: #f8f9fa;
    }
    .chat-header {
      background: #fff;
      padding: .75rem 1rem;
      border-bottom: 1px solid #dee2e6;
    }
    .chat-body {
      flex: 1;
      overflow-y: auto;
      padding: 1rem;
    }
    .msg-row { display: flex; margin-bottom: .75rem; }
    .msg-row.user { justify-content: flex-end; }
    .msg {
      max-width: 75%;
      padding: .75rem 1rem;
      border-radius: 1rem;
      word-wrap: break-word;
    }
    .msg.bot {
      background: #ffffff;
      color: #212529;
    }
    .msg.user {
      background: #0d6efd;
      color: #fff;
    }
    .typing-indicator {
      display: none;
      align-items: center;
      gap: .3rem;
      padding: .75rem 1rem;
      background: #ffffff;
      border-radius: 1rem;
      max-width: 75%;
      margin-bottom: .75rem;
    }
    .typing-indicator span {
      width: 8px;
      height: 8px;
      background: #6c757d;
      border-radius: 50%;
      animation: typing 1.2s infinite;
    }
    .typing-indicator span:nth-child(2) { animation-delay: .2s; }
    .typing-indicator span:nth-child(3) { animation-delay: .4s; }

    @keyframes typing {
      0%, 60%, 100% { transform: translateY(0); }
      30% { transform: translateY(-6px); }
    }
  </style>
</head>
<body>

<div class="chat-wrapper">
  <!-- Header with user info -->
  <div class="chat-header">
    <h5 class="mb-0">Chat Donat JLO</h5>
    <small class="text-muted">
      <strong><?= htmlspecialchars($user['name']) ?></strong> |
      <?= htmlspecialchars($user['email']) ?> |
      <?= htmlspecialchars($user['whatsapp']) ?>
    </small>
  </div>

  <!-- Chat messages -->
  <div class="chat-body" id="chat">
    <!-- Messages go here -->
  </div>

  <!-- Typing indicator -->
  <div class="typing-indicator" id="typingIndicator">
    <span></span><span></span><span></span>
  </div>

  <!-- Footer input -->
  <div class="chat-footer">
    <div class="input-group">
      <input type="text" id="messageInput" class="form-control" placeholder="Ketik pesan Anda..." autocomplete="off">
      <button class="btn btn-primary" id="sendBtn">Kirim</button>
    </div>
  </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
  const chatEl        = document.getElementById('chat');
  const inputEl       = document.getElementById('messageInput');
  const sendBtn       = document.getElementById('sendBtn');
  const typingEl      = document.getElementById('typingIndicator');

  /* ---------- helpers ---------- */
  // Fungsi untuk me-append HTML
  function appendHtmlMessage(html, sender) {
    const row = document.createElement('div');
    row.className = 'msg-row ' + sender;

    const msg = document.createElement('div');
    msg.className = 'msg ' + sender;
    msg.innerHTML = html;   // HTML langsung dirender

    row.appendChild(msg);
    chatEl.appendChild(row);
    chatEl.scrollTop = chatEl.scrollHeight;
  }

  function showTyping() {
    typingEl.style.display = 'flex';
    chatEl.scrollTop = chatEl.scrollHeight;
  }

  function hideTyping() {
    typingEl.style.display = 'none';
  }

  /* ---------- send ---------- */
  async function sendMessage() {
    const text = inputEl.value.trim();
    if (!text) return;

    appendHtmlMessage(text, 'user');
    inputEl.value = '';
    sendBtn.disabled = true;

    showTyping();

    try {
      const res = await fetch('backend_chat.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ message: text })
      });
      const data = await res.json();
      hideTyping();

      if (data.error) {
        appendHtmlMessage(`❌ ${data.error}`, 'bot');
      } else {
        appendHtmlMessage(data.result || data.reply || '...', 'bot');
      }
    } catch (e) {
      hideTyping();
      appendHtmlMessage('Network error, coba lagi nanti.', 'bot');
    } finally {
      sendBtn.disabled = false;
      inputEl.focus();
    }
  }

  /* ---------- events ---------- */
  sendBtn.addEventListener('click', sendMessage);
  inputEl.addEventListener('keydown', e => {
    if (e.key === 'Enter') {
      e.preventDefault();
      sendMessage();
    }
  });

  /* ---------- init ---------- */
  window.addEventListener('load', () => inputEl.focus());
</script>

</body>
</html>