<?php
// public/view_information.php

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

// Get PINECONE_NAMESPACE from settings
$pineconeNamespace = ''; // default value
try {
    $stmt = $db->getConnection()->prepare("SELECT value FROM settings WHERE `key` = 'PINECONE_NAMESPACE' LIMIT 1");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result) {
        $pineconeNamespace = $result['value'];
    }
} catch (PDOException $e) {
    error_log('Error getting PINECONE_NAMESPACE: ' . $e->getMessage());
}

// Notifikasi
$info = $_SESSION['info'] ?? null;
unset($_SESSION['info']);

// 1. Ambil filter & paging
$search  = trim($_GET['q'] ?? '');
$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;
$offset  = ($page - 1) * $perPage;

// Siapkan WHERE + params
$where  = '';
$params = [];
if ($search !== '') {
    $where = " WHERE (
        id = :exact_id
        OR namespace LIKE :kw_namespace
        OR content_information LIKE :kw_content
        OR judul LIKE :kw_judul
    )";
    $params[':exact_id']     = ctype_digit($search) ? (int)$search : -1;
    $params[':kw_namespace'] = "%{$search}%";
    $params[':kw_content']   = "%{$search}%";
    $params[':kw_judul']     = "%{$search}%";
}

// Hitung total
$countSql  = "SELECT COUNT(*) FROM information" . $where;
$countStmt = $db->getConnection()->prepare($countSql);
foreach ($params as $k => $v) {
    $countStmt->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
}
$countStmt->execute();
$total = (int)$countStmt->fetchColumn();
$totalPages = (int)ceil($total / $perPage);

// Status Pinecone (opsional)
$isp1 = (int)$db->getConnection()
    ->query("SELECT COUNT(*) FROM information WHERE ispinecone = 1")
    ->fetchColumn();
$isp0 = (int)$db->getConnection()
    ->query("SELECT COUNT(*) FROM information WHERE ispinecone = 0")
    ->fetchColumn();

// Ambil data dengan paging
$sql = "SELECT id, namespace, content_information, judul, ispinecone, regdate
        FROM information"
     . $where .
     " ORDER BY lastupdate DESC
        LIMIT :limit OFFSET :offset";
$stmt = $db->getConnection()->prepare($sql);
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
}
$stmt->bindValue(':limit',  $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset,  PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Daftar Informasi</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <style>
    .content-preview {
      max-height: 100px;
      overflow: hidden;
      text-overflow: ellipsis;
      display: -webkit-box;
      -webkit-line-clamp: 3;
      -webkit-box-orient: vertical;
    }
  </style>
</head>
<body class="d-flex">
  <?php include 'nav_admin.php'; ?>

  <div id="content" class="flex-grow-1 p-4">
    <h1 class="mb-3">Daftar Informasi</h1>

    <div class="mb-3">
      <span class="badge bg-primary">Total: <?= $total ?></span>
      <span class="badge bg-success">Diproses: <?= $isp1 ?></span>
      <span class="badge bg-warning text-dark">Pending: <?= $isp0 ?></span>
      <a href="add_information.php" class="btn btn-sm btn-success float-end">
        <i class="bi bi-plus"></i> Tambah Baru
      </a>
    </div>

    <?php if ($info): ?>
      <div class="alert alert-<?= $info['status'] ?>"><?= htmlspecialchars($info['message']) ?></div>
    <?php endif; ?>

    <!-- Filter -->
    <form method="get" class="mb-3">
      <div class="input-group">
        <input type="text" name="q" class="form-control" placeholder="Cari ID, Namespace, Konten, Judul"
               value="<?= htmlspecialchars($_GET['q'] ?? '', ENT_QUOTES) ?>">
        <button class="btn btn-primary" type="submit">Cari</button>
        <a href="view_information.php" class="btn btn-outline-secondary">Reset</a>
      </div>
    </form>

    <!-- Tabel Data -->
    <div class="table-responsive">
      <table class="table table-striped table-bordered">
        <thead class="table-light">
          <tr>
            <th width="50">ID</th>
            <th width="150">Namespace</th>
            <th width="200">Judul</th>
            <th>Konten</th>
            <th width="100">Pinecone</th>
            <th width="150">Reg Date</th>
            <th width="150">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!$rows): ?>
            <tr><td colspan="7" class="text-center">Tidak ada informasi.</td></tr>
          <?php else: foreach($rows as $row): ?>
            <tr>
              <td><?= $row['id'] ?></td>
              <td><?= htmlspecialchars($row['namespace']) ?></td>
              <td>
                <div class="content-preview">
                  <?= htmlspecialchars($row['judul']) ?>
                </div>
                <button type="button" class="btn btn-sm btn-info mt-1"
                        data-bs-toggle="modal" data-bs-target="#titleModal"
                        data-title-full="<?= htmlspecialchars($row['judul'],ENT_QUOTES) ?>">
                  View Full
                </button>
              </td>
              <td>
                <div class="content-preview">
                  <?= htmlspecialchars($row['content_information']) ?>
                </div>
                <button type="button" class="btn btn-sm btn-info mt-1"
                        data-bs-toggle="modal" data-bs-target="#contentModal"
                        data-content-full="<?= htmlspecialchars($row['content_information'],ENT_QUOTES) ?>">
                  View Full
                </button>
              </td>
              <td class="text-center">
    <?php if ($row['ispinecone']): ?>
        <span class="badge bg-success">Yes</span>
    <?php else: ?>
        <span class="badge bg-warning text-dark">No</span>
    <?php endif; ?>
    
    <?php if (trim($row['ispinecone']) === '0'): ?>
        <a href="proses_store_pinecone.php?data_id=<?= $row['id'] ?>" 
           class="btn btn-sm btn-warning mt-2"
           onclick="return confirm('Store this information to Pinecone?')">
            <i class="bi bi-upload"></i> Store to Pinecone
        </a>
    <?php endif; ?>
</td>
              <td><?= date('d M Y H:i', strtotime($row['regdate'])) ?></td>
              <td class="text-center">
                <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#editModal"
                        data-id="<?= $row['id'] ?>"
                        data-judul="<?= htmlspecialchars($row['judul'],ENT_QUOTES) ?>"
                        data-content="<?= htmlspecialchars($row['content_information'],ENT_QUOTES) ?>">
                  <i class="bi bi-pencil"></i> Edit
                </button>
                <form method="post" action="delete_information.php" class="d-inline" onsubmit="return confirm('Hapus informasi ini?');">
                  <input type="hidden" name="id" value="<?= $row['id'] ?>">
                  <input type="hidden" name="page" value="<?= $page ?>">
                  <button type="submit" class="btn btn-sm btn-danger">
                    <i class="bi bi-trash"></i> Hapus
                  </button>
                </form>
              </td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <nav><ul class="pagination justify-content-center">
      <li class="page-item<?= $page<=1?' disabled':'' ?>">
        <a class="page-link" href="?q=<?= urlencode($search) ?>&page=<?= $page-1 ?>">&laquo;</a>
      </li>
      <?php for ($i=1; $i<=$totalPages; $i++): ?>
      <li class="page-item<?= $i===$page?' active':'' ?>">
        <a class="page-link" href="?q=<?= urlencode($search) ?>&page=<?= $i ?>"><?= $i ?></a>
      </li>
      <?php endfor; ?>
      <li class="page-item<?= $page>=$totalPages?' disabled':'' ?>">
        <a class="page-link" href="?q=<?= urlencode($search) ?>&page=<?= $page+1 ?>">&raquo;</a>
      </li>
    </ul></nav>
    <?php endif; ?>
  </div>

  <!-- Modal View Full Title -->
  <div class="modal fade" id="titleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Judul Lengkap</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div id="title-full-content" class="p-3 bg-light rounded"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal View Full Content -->
  <div class="modal fade" id="contentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Konten Lengkap</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div id="content-full-content" class="p-3 bg-light rounded" style="white-space: pre-wrap;"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Edit Informasi -->
  <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <form class="modal-content" method="post" action="edit_information.php">
        <div class="modal-header">
          <h5 class="modal-title">Edit Informasi</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id" id="edit-id">
          <input type="hidden" name="page" value="<?= $page ?>">
          <input type="hidden" name="namespace" value="<?= htmlspecialchars($pineconeNamespace) ?>">
          <input type="hidden" name="ispinecone" value="0">
          
          <div class="mb-3">
            <label class="form-label">Namespace</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($pineconeNamespace) ?>" readonly disabled>
            <div class="form-text">Namespace tidak dapat diubah</div>
          </div>
          
          <div class="mb-3">
            <label for="edit-judul" class="form-label">Judul</label>
            <input type="text" name="judul" id="edit-judul" class="form-control" required>
          </div>
          
          <div class="mb-3">
            <label for="edit-content" class="form-label">Konten Informasi</label>
            <textarea name="content" id="edit-content" class="form-control" rows="8" required></textarea>
          </div>
          
          <div class="mb-3">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="edit-ispinecone-display" disabled>
              <label class="form-check-label" for="edit-ispinecone-display">
                Status Pinecone (tidak dapat diubah)
              </label>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </div>
      </form>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Modal untuk melihat judul lengkap
    const titleModal = document.getElementById('titleModal');
    if (titleModal) {
      titleModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const fullTitle = button.getAttribute('data-title-full');
        const modalBody = titleModal.querySelector('#title-full-content');
        modalBody.textContent = fullTitle;
      });
    }

    // Modal untuk melihat konten lengkap
    const contentModal = document.getElementById('contentModal');
    if (contentModal) {
      contentModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const fullContent = button.getAttribute('data-content-full');
        const modalBody = contentModal.querySelector('#content-full-content');
        modalBody.textContent = fullContent;
      });
    }

    // Modal untuk edit informasi
    const editModal = document.getElementById('editModal');
    if (editModal) {
      editModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        
        document.getElementById('edit-id').value = button.getAttribute('data-id');
        document.getElementById('edit-judul').value = button.getAttribute('data-judul');
        document.getElementById('edit-content').value = button.getAttribute('data-content');
        
        // Show Pinecone status but keep it disabled
        const isPinecone = button.getAttribute('data-ispinecone') === '1';
        document.getElementById('edit-ispinecone-display').checked = isPinecone;
      });
    }
  </script>
</body>
</html>