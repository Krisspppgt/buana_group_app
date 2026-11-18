<?php
require_once 'includes/functions.php';
require_login();
if (!can_manage()) {
    http_response_code(403);
    exit('Akses ditolak');
}
page_title('Edit Pelanggan');
$pdo = db();
$id = (int)($_GET['id'] ?? 0);

$st = $pdo->prepare("SELECT * FROM users WHERE id=? AND role='customer'");
$st->execute([$id]);
$cust = $st->fetch();

if (!$cust) {
    flash('Data pelanggan tidak ditemukan.', 'error');
    header('Location: customers.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $type = in_array($_POST['customer_type'] ?? '', ['umum', 'institusi'], true) ? $_POST['customer_type'] : 'umum';
    $credit_limit = max(0, (float)($_POST['credit_limit'] ?? 0));
    $is_active = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;
    $pass = $_POST['password'] ?? '';

    if (!$name || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Nama dan email yang valid wajib diisi.';
    } elseif ($pass !== '' && strlen($pass) < 8) {
        $error = 'Password baru minimal 8 karakter.';
    } else {
        $chk = $pdo->prepare("SELECT id FROM users WHERE email=? AND id<>?");
        $chk->execute([$email, $id]);
        if ($chk->fetch()) {
            $error = 'Email sudah digunakan oleh pengguna lain.';
        } else {
            try {
                if ($pass !== '') {
                    $hash = password_hash($pass, PASSWORD_DEFAULT);
                    $pdo->prepare("UPDATE users SET name=?, email=?, password=?, phone=?, customer_type=?, credit_limit=?, is_active=? WHERE id=?")->execute([$name, $email, $hash, $phone, $type, $credit_limit, $is_active, $id]);
                } else {
                    $pdo->prepare("UPDATE users SET name=?, email=?, phone=?, customer_type=?, credit_limit=?, is_active=? WHERE id=?")->execute([$name, $email, $phone, $type, $credit_limit, $is_active, $id]);
                }
                log_activity('Ubah pelanggan', $name . ' (' . $email . ')');
                flash('Data pelanggan berhasil diperbarui.');
                header('Location: customers.php');
                exit;
            } catch (PDOException $e) {
                $error = 'Gagal menyimpan perubahan data pelanggan.';
            }
        }
    }
}

include 'includes/header.php';
?>
<div class="page-head">
  <div>
    <h1>Edit Pelanggan</h1>
    <p class="sub">Perbarui informasi data dan limit kredit pelanggan.</p>
  </div>
  <a class="btn secondary" href="customers.php">← Kembali</a>
</div>

<div class="card form-card">
  <?php if ($error): ?>
    <div class="alert error"><?=e($error)?></div>
  <?php endif; ?>
  <form method="post">
    <?=csrf_field()?>
    <div class="form-grid">
      <div class="field">
        <label>Nama Pelanggan / Institusi</label>
        <input name="name" required value="<?=e($_POST['name'] ?? $cust['name'])?>">
      </div>
      <div class="field">
        <label>Email</label>
        <input type="email" name="email" required value="<?=e($_POST['email'] ?? $cust['email'])?>">
      </div>
      <div class="field">
        <label>Password Baru (Kosongkan bila tidak diubah)</label>
        <input type="password" name="password" minlength="8" placeholder="Minimal 8 karakter jika ingin ganti">
      </div>
      <div class="field">
        <label>Nomor Telepon / WhatsApp</label>
        <input name="phone" value="<?=e($_POST['phone'] ?? $cust['phone'])?>">
      </div>
      <div class="field">
        <label>Tipe Pelanggan</label>
        <?php $curType = $_POST['customer_type'] ?? $cust['customer_type']; ?>
        <select name="customer_type">
          <option value="umum" <?=$curType === 'umum' ? 'selected' : ''?>>Umum</option>
          <option value="institusi" <?=$curType === 'institusi' ? 'selected' : ''?>>Institusi / Perusahaan</option>
        </select>
      </div>
      <div class="field">
        <label>Limit Tempo / Piutang (Rp)</label>
        <input type="number" name="credit_limit" min="0" step="1000" value="<?=e($_POST['credit_limit'] ?? $cust['credit_limit'])?>">
      </div>
      <div class="field">
        <label>Status Akun</label>
        <?php $curActive = (int)($_POST['is_active'] ?? $cust['is_active']); ?>
        <select name="is_active">
          <option value="1" <?=$curActive === 1 ? 'selected' : ''?>>Aktif</option>
          <option value="0" <?=$curActive === 0 ? 'selected' : ''?>>Nonaktif</option>
        </select>
      </div>
    </div>
    <div class="form-actions">
      <button class="btn" type="submit">Simpan Perubahan</button>
      <a class="btn secondary" href="customers.php">Batal</a>
    </div>
  </form>
</div>
<?php include 'includes/footer.php'; ?>
