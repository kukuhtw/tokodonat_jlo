<?php
/**
 * public/view_order.php
 *
 * 🍩 Aplikasi Chatbot Toko Donat JLO Jakarta
 * (Melayani pertanyaan & pesanan donat secara otomatis)
 *
 * 📧 Email     : kukuhtw@gmail.com
 * 📱 WhatsApp  : https://wa.me/628129893706
 * 📷 Instagram : @kukuhtw
 * 🐦 X/Twitter : @kukuhtw
 * 👍 Facebook  : https://www.facebook.com/kukuhtw
 */

require __DIR__ . '/../bootstrap.php';
session_start();

/* ---------- Admin-only gate ---------- */
if (empty($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

/* ---------- Helpers ---------- */
$startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-7 days'));
$endDate   = $_GET['end_date']   ?? date('Y-m-d');
$waFilter  = trim($_GET['wa'] ?? '');
$page      = max(1, (int)($_GET['page'] ?? 1));
$perPage   = 20;
$offset    = ($page - 1) * $perPage;

/* ---------- Prepare SQL ---------- */
$where = [];
$params = [];

$where[] = "o.order_date BETWEEN :start AND :end";
$params[':start'] = $startDate . ' 00:00:00';
$params[':end']   = $endDate   . ' 23:59:59';

if ($waFilter !== '') {
    $where[] = 'u.whatsapp LIKE :wa';
    $params[':wa'] = "%{$waFilter}%";
}
/* ---------- NEW LINES – fix collation ---------- */
$sqlCount = "
    SELECT COUNT(*) AS total
    FROM `order` o
    JOIN `users` u ON u.whatsapp COLLATE utf8mb4_unicode_ci = o.sender COLLATE utf8mb4_unicode_ci
    " . ($where ? 'WHERE ' . implode(' AND ', $where) : '');

$sqlData = "
    SELECT
        o.id,
        o.order_id,
        o.sender,
        o.order_description,
        o.order_date,
        o.ispaid,
        o.paid_date,
        o.note,
        o.json_order,
        u.name,
        u.email,
        u.address_delivery
    FROM `order` o
    JOIN `users` u ON u.whatsapp COLLATE utf8mb4_unicode_ci = o.sender COLLATE utf8mb4_unicode_ci
    " . ($where ? 'WHERE ' . implode(' AND ', $where) : '') . "
    ORDER BY o.order_date DESC
    LIMIT :lim OFFSET :off
";


/* ---------- Run queries ---------- */
$stmt = $pdo->prepare($sqlCount);
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
}
$stmt->execute();
$totalRows = (int) $stmt->fetchColumn();

$stmt = $pdo->prepare($sqlData);
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
}
$stmt->bindValue(':lim', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':off', $offset,  PDO::PARAM_INT);
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalPages = max(1, ceil($totalRows / $perPage));


?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Daftar Pesanan</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="d-flex">
  <?php include 'nav_admin.php'; ?>

  <div id="content" class="flex-grow-1 p-4">
    <h1 class="mb-3">📦 Daftar Pesanan</h1>

    <!-- Filter -->
    <form method="get" class="row g-3 mb-3">
      <div class="col-md-3">
        <label class="form-label">Start</label>
        <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($startDate) ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">End</label>
        <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($endDate) ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">WhatsApp</label>
        <input type="text" name="wa" class="form-control" placeholder="+62812…" value="<?= htmlspecialchars($waFilter) ?>">
      </div>
      <div class="col-md-2 d-flex align-items-end">
        <button class="btn btn-primary w-100">Filter</button>
      </div>
    </form>

    <!-- Table -->
    <div class="table-responsive">
      <table class="table table-bordered table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Order&nbsp;ID</th>
            <th>WhatsApp</th>
            <th>Nama</th>
            <th>Deskripsi</th>
            <th>Tanggal</th>
            <th>Status</th>
            <th>Note</th>
            <th width="150">Aksi</th>

          </tr>
        </thead>
        <tbody>
          <?php if (!$orders): ?>
            <tr>
              <td colspan="8" class="text-center text-muted">Belum ada pesanan</td>
            </tr>
          <?php else: ?>
            <?php foreach ($orders as $idx => $row): ?>
              <?php
                $badge = $row['ispaid']
                  ? '<span class="badge bg-success">Paid</span>'
                  : '<span class="badge bg-warning text-dark">Unpaid</span>';
              ?>
              <tr>
                <td><?= $offset + $idx + 1 ?></td>
                <td>
                  <a href="order_detail.php?id=<?= $row['id'] ?>" class="text-decoration-none fw-bold">
                    <?= htmlspecialchars($row['order_id']) ?>
                  </a>
                </td>
                <td>
                  <a target="_blank" href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $row['sender']) ?>">
                    <?= htmlspecialchars($row['sender']) ?>
                  </a>
                </td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td>
                  <small><?= nl2br(htmlspecialchars(substr($row['order_description'], 0, 100))) ?>
                    <?= strlen($row['order_description']) > 100 ? '…' : '' ?>
                  </small>
                </td>
                <td><?= date('d/m/Y H:i', strtotime($row['order_date'])) ?></td>
                <td><?= $badge ?></td>
                <td><small><?= nl2br(htmlspecialchars($row['note'])) ?></small></td>

                <td class="text-center">
  <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#viewModal"
          data-desc="<?= htmlspecialchars($row['order_description'], ENT_QUOTES) ?>"
          data-note="<?= htmlspecialchars($row['note'], ENT_QUOTES) ?>">
    <i class="bi bi-eye"></i> View
  </button>
  <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal"
          data-id="<?= $row['id'] ?>"
          data-ispaid="<?= $row['ispaid'] ?>"
          data-paid="<?= $row['paid_date'] ?>"
          data-note="<?= htmlspecialchars($row['note'], ENT_QUOTES) ?>"
          data-desc="<?= htmlspecialchars($row['order_description'], ENT_QUOTES) ?>">
    <i class="bi bi-pencil"></i> Edit
  </button>
</td>

              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
      <nav>
        <ul class="pagination justify-content-center">
          <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
              <a class="page-link" href="?page=<?= $i ?>&start_date=<?= $startDate ?>&end_date=<?= $endDate ?>&wa=<?= urlencode($waFilter) ?>">
                <?= $i ?>
              </a>
            </li>
          <?php endfor; ?>
        </ul>
      </nav>
    <?php endif; ?>
  </div>
<!-- Modal View -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Detail Pesanan</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <h6>Deskripsi:</h6>
        <div id="view-desc" class="bg-light p-2 rounded" style="white-space: pre-wrap;"></div>
        <h6 class="mt-3">Catatan:</h6>
        <div id="view-note" class="bg-light p-2 rounded" style="white-space: pre-wrap;"></div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" method="post" action="update_order.php">
      <div class="modal-header"><h5 class="modal-title">Edit Pesanan</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id" id="edit-id">
        <div class="mb-2">
          <label class="form-label">Status Bayar</label>
          <select name="ispaid" class="form-select" id="edit-ispaid">
            <option value="1">Paid</option>
            <option value="0">Unpaid</option>
          </select>
        </div>
        <div class="mb-2">
          <label class="form-label">Tanggal Bayar</label>
         <input type="datetime-local" name="paid_date" class="form-control" id="edit-paid"
       value="">




        </div>
        <div class="mb-2">
          <label class="form-label">Deskripsi Order</label>
          <textarea name="order_description" rows="3" class="form-control" id="edit-desc"></textarea>
        </div>
        <div class="mb-2">
          <label class="form-label">Catatan</label>
          <textarea name="note" rows="2" class="form-control" id="edit-note"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button class="btn btn-primary" type="submit">Simpan</button>
      </div>
    </form>
  </div>
</div>
<script>
document.getElementById('viewModal').addEventListener('show.bs.modal', function (event) {
  const btn = event.relatedTarget;
  document.getElementById('view-desc').textContent = btn.getAttribute('data-desc');
  document.getElementById('view-note').textContent = btn.getAttribute('data-note');
});


document.getElementById('editModal').addEventListener('show.bs.modal', function (event) {
  const btn = event.relatedTarget;

  document.getElementById('edit-id').value     = btn.getAttribute('data-id');
  document.getElementById('edit-ispaid').value = btn.getAttribute('data-ispaid');
  document.getElementById('edit-desc').value   = btn.getAttribute('data-desc');
  document.getElementById('edit-note').value   = btn.getAttribute('data-note');

  // Format paid_date (contoh: 2025-07-27T20:54)
  const rawPaid = btn.getAttribute('data-paid');
  if (rawPaid) {
    const dt = new Date(rawPaid);
    const formatted = dt.toISOString().slice(0, 16); // YYYY-MM-DDTHH:MM
    document.getElementById('edit-paid').value = formatted;
  } else {
    document.getElementById('edit-paid').value = '';
  }
});

</script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

