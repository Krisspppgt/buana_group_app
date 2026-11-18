<?php
require_once 'includes/functions.php';
if (current_user()) { header('Location: index.php'); exit; }
$error='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    verify_csrf();
    $st=db()->prepare('SELECT * FROM users WHERE email=? AND is_active=1 LIMIT 1');
    $st->execute([trim($_POST['email']??'')]); $u=$st->fetch();
    if ($u && password_verify($_POST['password']??'', $u['password'])) {
        unset($u['password']); $_SESSION['user']=$u; log_activity('Login','Masuk ke aplikasi'); header('Location: index.php'); exit;
    } else $error='Email atau password tidak sesuai.';
}
?><!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Login - Buana Group</title><link rel="stylesheet" href="assets/style.css"></head><body class="login-page"><div class="login-card">
<div class="brand"><div class="brand-mark">🌿</div><div><strong>BUANA GROUP</strong><small>Indonesian Buana Group</small></div></div>
<h1>Selamat datang</h1><p class="sub">Masuk untuk mengelola ekosistem layanan Buana Group.</p>
<?php if($error): ?><div class="alert error" style="margin-top:15px"><?=e($error)?></div><?php endif; ?>
<form method="post"><?=csrf_field()?><div class="field"><label>Email</label><input type="email" name="email" required value="<?=e($_POST['email']??'')?>" placeholder="admin@buana.local"></div>
<div class="field"><label>Password</label><input type="password" name="password" required placeholder="Masukkan password"></div><button class="btn" type="submit">Masuk ke Dashboard →</button></form>
<div class="login-help">Akun awal: admin@buana.local / password: password<br>Segera ganti password setelah login pertama.</div>
</div></body></html>