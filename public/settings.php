<?php
// public/settings.php
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

// mapping database key → label dan input name



// mapping database key → label dan input name (lower_snake_case)
$fields = [
    'OPEN_AI_KEY'         => ['label' => 'OpenAI API Key',        'input' => 'openai_api_key',        'type' => 'password'],
    'HOST_DOMAIN'         => ['label' => 'Host Domain',           'input' => 'host_domain',           'type' => 'text'],
    'PINECONE_NAMESPACE'  => ['label' => 'Pinecone Namespace',    'input' => 'pinecone_namespace',    'type' => 'text'],
    'PINECONE_API_KEY'    => ['label' => 'Pinecone API Key',      'input' => 'pinecone_api_key',      'type' => 'password'],
    'PINECONE_INDEX_NAME' => ['label' => 'Pinecone Index Name',   'input' => 'pinecone_index_name',   'type' => 'text'],
    'modelgpt'            => ['label' => 'Model GPT',             'input' => 'modelgpt',              'type' => 'text'],
];

// proses submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $updated = 0;
    foreach ($fields as $dbKey => $info) {
        $val = trim($_POST[$info['input']] ?? '');
        // (optional) validasi kosong
        if ($info['type'] !== 'password' && $val === '') {
            $errors[] = "{$info['label']} tidak boleh kosong.";
            continue;
        }
        try {
            if ($settings->set($dbKey, $val)) {
                $updated++;
            }
        } catch (PDOException $e) {
            $errors[] = "Gagal menyimpan {$info['label']}: " . $e->getMessage();
            Logger::error("Settings: error updating {$dbKey}: " . $e->getMessage());
        }
    }
    if (empty($errors)) {
        if ($updated) {
            $success = 'Settings berhasil disimpan.';
            Logger::info("Settings updated ({$updated} keys).");
        } else {
            $errors[] = 'Tidak ada perubahan yang disimpan.';
            Logger::warning('Settings: no rows affected.');
        }
    }
}

// ambil nilai terkini
$current = [];
foreach ($fields as $dbKey => $info) {
    $current[$dbKey] = $settings->get($dbKey) ?? '';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Settings</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <style>
    body { min-height:100vh; display:flex; }
    #sidebar { width:250px; }
    #content { flex:1; padding:2rem; }
    .password-toggle .toggle-icon { cursor:pointer; }
  </style>
</head>
<body>
  <?php include(__DIR__ . '/nav_admin.php'); ?>
  <div id="content">
    <div class="container-fluid">
      <h1 class="mb-4">Settings</h1>

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
        <?php foreach ($fields as $dbKey => $info): ?>
          <div class="mb-3 <?= $info['type'] === 'password' ? 'password-toggle' : '' ?>">
            <label for="<?= $info['input'] ?>" class="form-label"><?= $info['label'] ?></label>
            <div class="input-group">
              <input
                type="<?= $info['type'] ?>"
                id="<?= $info['input'] ?>"
                name="<?= $info['input'] ?>"
                class="form-control"
                value="<?= htmlspecialchars($current[$dbKey]) ?>"
                <?= $info['type'] !== 'password' ? 'required' : '' ?>>
              <?php if ($info['type'] === 'password'): ?>
                <span class="input-group-text toggle-icon"><i class="bi bi-eye"></i></span>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>

        <div class="d-grid">
          <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
      </form>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.querySelectorAll('.password-toggle .toggle-icon').forEach(function(el) {
      el.addEventListener('click', function() {
        const input = this.closest('.input-group').querySelector('input');
        const icon  = this.querySelector('i');
        if (input.type === 'password') {
          input.type = 'text';
          icon.classList.replace('bi-eye', 'bi-eye-slash');
        } else {
          input.type = 'password';
          icon.classList.replace('bi-eye-slash', 'bi-eye');
        }
      });
    });
  </script>
</body>
</html>
