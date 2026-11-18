<?php
require_once 'includes/functions.php';
require_login();
page_title('Data Pelanggan');
$pdo = db();
$error = '';
$edit_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!can_manage()) {
        http_response_code(403);
        exit('Akses ditolak');
    }
    verify_csrf();
    $action = $_POST['action'] ?? 'create';

    if ($action === 'create') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $pass = $_POST['password'] ?? '';
        $phone = trim($_POST['phone'] ?? '');
        $type = in_array($_POST['customer_type'] ?? '', ['umum', 'institusi'], true) ? $_POST['customer_type'] : 'umum';
        $credit_limit = max(0, (float)($_POST['credit_limit'] ?? 0));

        if (!$name || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pass) < 8) {
            $error = 'Lengkapi data pelanggan. Password minimal 8 karakter dan email harus valid.';
        } else {
            try {
                $hash = password_hash($pass, PASSWORD_DEFAULT);
                $st = $pdo->prepare("INSERT INTO users(name,email,password,role,phone,customer_type,credit_limit,is_active) VALUES(?,?,?,'customer',?,?,?,1)");
                $st->execute([$name, $email, $hash, $phone, $type, $credit_limit]);
                log_activity('Tambah pelanggan', $name . ' (' . $email . ')');
                flash('Pelanggan berhasil ditambahkan.');
                header('Location: customers.php');
                exit;
            } catch (PDOException $e) {
                $error = 'Email sudah digunakan atau data tidak valid.';
            }
        }
    } elseif ($action === 'update') {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $type = in_array($_POST['customer_type'] ?? '', ['umum', 'institusi'], true) ? $_POST['customer_type'] : 'umum';
        $credit_limit = max(0, (float)($_POST['credit_limit'] ?? 0));
        $is_active = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;
        $pass = $_POST['password'] ?? '';

        if (!$id || !$name || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $edit_error = 'Nama dan email yang valid wajib diisi.';
        } elseif ($pass !== '' && strlen($pass) < 8) {
            $edit_error = 'Password baru minimal 8 karakter.';
        } else {
            $chk = $pdo->prepare("SELECT id FROM users WHERE email=? AND id<>?");
            $chk->execute([$email, $id]);
            if ($chk->fetch()) {
                $edit_error = 'Email sudah digunakan oleh pengguna lain.';
            } else {
                try {
                    if ($pass !== '') {
                        $hash = password_hash($pass, PASSWORD_DEFAULT);
                        $pdo->prepare("UPDATE users SET name=?, email=?, password=?, phone=?, customer_type=?, credit_limit=?, is_active=? WHERE id=? AND role='customer'")->execute([$name, $email, $hash, $phone, $type, $credit_limit, $is_active, $id]);
                    } else {
                        $pdo->prepare("UPDATE users SET name=?, email=?, phone=?, customer_type=?, credit_limit=?, is_active=? WHERE id=? AND role='customer'")->execute([$name, $email, $phone, $type, $credit_limit, $is_active, $id]);
                    }
                    log_activity('Ubah pelanggan', $name . ' (' . $email . ')');
                    flash('Data pelanggan berhasil diperbarui.');
                    header('Location: customers.php');
                    exit;
                } catch (PDOException $e) {
                    $edit_error = 'Gagal menyimpan perubahan pelanggan.';
                }
            }
        }
    }
}

$rows = $pdo->query("SELECT u.*,COUNT(o.id) order_count,COALESCE(SUM(CASE WHEN o.status<>'cancelled' THEN o.total ELSE 0 END),0) total_order FROM users u LEFT JOIN orders o ON o.customer_id=u.id WHERE u.role='customer' GROUP BY u.id ORDER BY u.name")->fetchAll();
include 'includes/header.php';
?>
<div class="page-head">
  <div>
    <h1>Data Pelanggan</h1>
    <p class="sub">Pelanggan institusi dan umum dalam satu ekosistem.</p>
  </div>
  <?php if (can_manage()): ?>
    <a class="btn gold" href="customer_create.php" onclick="openCustomerModal(event)">＋ Tambah Pelanggan</a>
  <?php endif; ?>
</div>

<div class="card">
  <div class="toolbar">
    <input data-search="tbody tr" placeholder="Cari pelanggan...">
  </div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Nama</th>
          <th>Email</th>
          <th>Telepon</th>
          <th>Tipe Akun</th>
          <th>Limit Tempo</th>
          <th>Jumlah Pesanan</th>
          <th>Nilai Transaksi</th>
          <?php if (can_manage()): ?>
            <th>Aksi</th>
          <?php endif; ?>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td>
              <b><?=e($r['name'])?></b>
              <?php if (empty($r['is_active'])): ?>
                <span class="badge red" style="margin-left:4px">Nonaktif</span>
              <?php endif; ?>
            </td>
            <td><?=e($r['email'])?></td>
            <td><?=e($r['phone'] ?: '-')?></td>
            <td><span class="badge <?=($r['customer_type'] === 'institusi') ? 'blue' : 'green'?>"><?=e(ucfirst($r['customer_type'] ?? 'umum'))?></span></td>
            <td><?=rupiah($r['credit_limit'])?></td>
            <td><?=number_format($r['order_count'])?></td>
            <td class="money"><?=rupiah($r['total_order'])?></td>
            <?php if (can_manage()): ?>
              <td>
                <div class="actions">
                  <button type="button" class="btn small secondary" onclick='openEditCustomerModal(<?=htmlspecialchars(json_encode([
                    "id" => (int)$r["id"],
                    "name" => $r["name"],
                    "email" => $r["email"],
                    "phone" => $r["phone"] ?? "",
                    "customer_type" => $r["customer_type"] ?? "umum",
                    "credit_limit" => (float)$r["credit_limit"],
                    "is_active" => (int)$r["is_active"]
                  ]), ENT_QUOTES, "UTF-8")?>)'>Edit</button>
              
                </div>
              </td>
            <?php endif; ?>
          </tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?>
          <tr>
            <td colspan="<?=can_manage() ? 8 : 7?>" class="empty">Belum ada pelanggan.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php if (can_manage()): ?>
<!-- Modal Tambah Pelanggan -->
<div id="customerModal" class="modal-overlay <?=$error ? 'show' : ''?>" onclick="if(event.target===this)closeCustomerModal()">
  <div class="modal-card">
    <div class="modal-header">
      <h2>Tambah Pelanggan Baru</h2>
      <button type="button" class="modal-close" onclick="closeCustomerModal()">&times;</button>
    </div>
    <?php if ($error): ?>
      <div class="alert error"><?=e($error)?></div>
    <?php endif; ?>
    <form method="post" action="customers.php">
      <?=csrf_field()?>
      <input type="hidden" name="action" value="create">
      <div class="form-grid">
        <div class="field">
          <label>Nama Pelanggan / Institusi</label>
          <input name="name" required value="<?=e($_POST['name'] ?? '')?>" placeholder="Contoh: CV Mitra Abadi / Ahmad Dahlan">
        </div>
        <div class="field">
          <label>Email</label>
          <input type="email" name="email" required value="<?=e($_POST['email'] ?? '')?>" placeholder="pelanggan@example.com">
        </div>
        <div class="field">
          <label>Password (min. 8 karakter)</label>
          <input type="password" name="password" minlength="8" required placeholder="Minimal 8 karakter">
        </div>
        <div class="field">
          <label>Nomor Telepon / WhatsApp</label>
          <input name="phone" value="<?=e($_POST['phone'] ?? '')?>" placeholder="081234567890">
        </div>
        <div class="field">
          <label>Tipe Pelanggan</label>
          <select name="customer_type">
            <option value="umum" <?=(($_POST['customer_type'] ?? '') === 'umum') ? 'selected' : ''?>>Umum</option>
            <option value="institusi" <?=(($_POST['customer_type'] ?? '') === 'institusi') ? 'selected' : ''?>>Institusi / Perusahaan</option>
          </select>
        </div>
        <div class="field">
          <label>Limit Tempo / Piutang (Rp)</label>
          <input type="number" name="credit_limit" min="0" step="1000" value="<?=e($_POST['credit_limit'] ?? '0')?>">
        </div>
      </div>
      <div class="form-actions" style="margin-top:20px">
        <button class="btn" type="submit">Simpan Pelanggan</button>
        <button class="btn secondary" type="button" onclick="closeCustomerModal()">Batal</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Edit Pelanggan -->
<div id="editCustomerModal" class="modal-overlay <?=$edit_error ? 'show' : ''?>" onclick="if(event.target===this)closeEditCustomerModal()">
  <div class="modal-card">
    <div class="modal-header">
      <h2>Edit Data Pelanggan</h2>
      <button type="button" class="modal-close" onclick="closeEditCustomerModal()">&times;</button>
    </div>
    <?php if ($edit_error): ?>
      <div class="alert error"><?=e($edit_error)?></div>
    <?php endif; ?>
    <form method="post" action="customers.php">
      <?=csrf_field()?>
      <input type="hidden" name="action" value="update">
      <input type="hidden" name="id" id="edit_id" value="<?=e($_POST['id'] ?? '')?>">
      <div class="form-grid">
        <div class="field">
          <label>Nama Pelanggan / Institusi</label>
          <input name="name" id="edit_name" required value="<?=e($_POST['name'] ?? '')?>">
        </div>
        <div class="field">
          <label>Email</label>
          <input type="email" name="email" id="edit_email" required value="<?=e($_POST['email'] ?? '')?>">
        </div>
        <div class="field">
          <label>Password Baru (Kosongkan jika tidak diubah)</label>
          <input type="password" name="password" id="edit_password" minlength="8" placeholder="Minimal 8 karakter jika ingin diubah">
        </div>
        <div class="field">
          <label>Nomor Telepon / WhatsApp</label>
          <input name="phone" id="edit_phone" value="<?=e($_POST['phone'] ?? '')?>">
        </div>
        <div class="field">
          <label>Tipe Pelanggan</label>
          <select name="customer_type" id="edit_customer_type">
            <option value="umum">Umum</option>
            <option value="institusi">Institusi / Perusahaan</option>
          </select>
        </div>
        <div class="field">
          <label>Limit Tempo / Piutang (Rp)</label>
          <input type="number" name="credit_limit" id="edit_credit_limit" min="0" step="1000" value="0">
        </div>
        <div class="field full">
          <label>Status Akun</label>
          <select name="is_active" id="edit_is_active">
            <option value="1">Aktif</option>
            <option value="0">Nonaktif</option>
          </select>
        </div>
      </div>
      <div class="form-actions" style="margin-top:20px">
        <button class="btn" type="submit">Simpan Perubahan</button>
        <button class="btn secondary" type="button" onclick="closeEditCustomerModal()">Batal</button>
      </div>
    </form>
  </div>
</div>

<script>
function openCustomerModal(e) {
  if (e) e.preventDefault();
  document.getElementById('customerModal').classList.add('show');
}
function closeCustomerModal() {
  document.getElementById('customerModal').classList.remove('show');
}

function openEditCustomerModal(data) {
  document.getElementById('edit_id').value = data.id || '';
  document.getElementById('edit_name').value = data.name || '';
  document.getElementById('edit_email').value = data.email || '';
  document.getElementById('edit_password').value = '';
  document.getElementById('edit_phone').value = data.phone || '';
  document.getElementById('edit_customer_type').value = data.customer_type || 'umum';
  document.getElementById('edit_credit_limit').value = data.credit_limit || 0;
  document.getElementById('edit_is_active').value = (data.is_active !== undefined) ? data.is_active : 1;
  document.getElementById('editCustomerModal').classList.add('show');
}
function closeEditCustomerModal() {
  document.getElementById('editCustomerModal').classList.remove('show');
}

window.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    closeCustomerModal();
    closeEditCustomerModal();
  }
});
</script>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
