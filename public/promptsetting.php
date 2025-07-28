<?php
// public/promptsetting.php
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

$errors  = [];
$success = null;

// a simple PromptSettings wrapper (you'll need to create src/PromptSettings.php)
/** @var PromptSettings $promptsManager */
$promptsManager = new PromptSettings($db);

// handle updates to existing prompts
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $updated = 0;
    $allPrompts = $promptsManager->getAll();
    foreach ($allPrompts as $p) {
        $id   = (int)$p['id'];
        $instr = trim($_POST['instruction'][$id] ?? '');
        if ($instr === '') {
            $errors[] = "Instruction for “{$p['promptid']}” cannot be empty.";
            continue;
        }
        if ($promptsManager->set($p['promptid'], $instr)) {
            $updated++;
        }
    }
    if (empty($errors)) {
        if ($updated) {
            $success = "Updated {$updated} prompt(s).";
        } else {
            $errors[] = 'No changes detected.';
        }
    }
}

// handle adding a new prompt
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $newId   = trim($_POST['new_promptid'] ?? '');
    $newInstr = trim($_POST['new_instruction'] ?? '');
    if ($newId === '' || $newInstr === '') {
        $errors[] = 'Both Prompt ID and Instruction are required to add a new prompt.';
    } else {
        try {
            if ($promptsManager->set($newId, $newInstr)) {
                $success = "Added new prompt “{$newId}”.";
            } else {
                $errors[] = "Failed to add prompt “{$newId}”.";
            }
        } catch (\PDOException $e) {
            $errors[] = "Error adding new prompt: " . $e->getMessage();
            Logger::error("PromptSettings: error inserting {$newId}: " . $e->getMessage());
        }
    }
}

// reload current prompts
$currentPrompts = $promptsManager->getAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Prompt Settings</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <style> body { padding:2rem; } </style>
</head>
<body class="d-flex">
  <?php include 'nav_admin.php'; ?>

  <div id="content" class="flex-grow-1 p-4">

  <h1 class="mb-4">Prompt Settings</h1>

  <?php if ($errors): ?>
    <div class="alert alert-danger"><ul class="mb-0">
      <?php foreach ($errors as $e): ?>
        <li><?= htmlspecialchars($e) ?></li>
      <?php endforeach; ?>
    </ul></div>
  <?php endif; ?>

  <?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>

  <form method="post">
    <input type="hidden" name="action" value="update">
    <?php foreach ($currentPrompts as $p): ?>
      <div class="mb-3">
        <label for="instr_<?= $p['id'] ?>" class="form-label">
          <?= htmlspecialchars($p['promptid']) ?>
        </label>
        <textarea
          id="instr_<?= $p['id'] ?>"
          name="instruction[<?= $p['id'] ?>]"
          class="form-control"
          rows="9"
        ><?= htmlspecialchars($p['instruction']) ?></textarea>
      </div>
    <?php endforeach; ?>
    <button type="submit" class="btn btn-primary">Save Changes</button>
  </form>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
