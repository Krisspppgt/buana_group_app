<?php
require_once 'includes/functions.php';
require_login();
if (!can_manage()) {
    http_response_code(403);
    exit('Akses ditolak');
}
page_title('Tambah Pelanggan');
$pdo = db();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
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
}

include 'includes/header.php';
?>
<div class="page-head">
  <div>
    <h1>Tambah Pelanggan</h1>
    <p class="sub">Pendaftaran pelanggan baru untuk unit usaha Buana Group.</p>
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
    <div class="form-actions">
      <button class="btn gold" type="submit">Simpan Pelanggan</button>
      <a class="btn secondary" href="customers.php">Batal</a>
    </div>
  </form>
</div>
<?php include 'includes/footer.php'; ?>
