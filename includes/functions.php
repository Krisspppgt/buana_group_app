<?php
require_once __DIR__ . '/../config.php';

function e($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function rupiah($n): string { return 'Rp '.number_format((float)$n, 0, ',', '.'); }
function current_user(): ?array { return $_SESSION['user'] ?? null; }
function require_login(): void {
    if (!current_user()) { header('Location: login.php'); exit; }
}
function can_manage(): bool {
    return current_user() && in_array(current_user()['role'], ['super_admin','admin_unit','keuangan','operasional'], true);
}
function is_admin(): bool {
    return current_user() && current_user()['role'] === 'super_admin';
}
function flash($message, $type='success'): void { $_SESSION['flash'] = ['message'=>$message,'type'=>$type]; }
function get_flash(): ?array { $f=$_SESSION['flash']??null; unset($_SESSION['flash']); return $f; }
function log_activity(string $action, string $detail=''): void {
    if (!current_user()) return;
    $s=db()->prepare('INSERT INTO activity_logs(user_id, action, detail) VALUES(?,?,?)');
    $s->execute([current_user()['id'],$action,$detail]);
}
function csrf_field(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf']=bin2hex(random_bytes(32));
    return '<input type="hidden" name="csrf" value="'.e($_SESSION['csrf']).'">';
}
function verify_csrf(): void {
    if ($_SERVER['REQUEST_METHOD']==='POST') {
        if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
            http_response_code(419); exit('Sesi formulir tidak valid. Muat ulang halaman.');
        }
    }
}
function status_badge(string $status): string {
    $labels = [
      'pending'=>'Menunggu','approved'=>'Disetujui','processing'=>'Diproses',
      'ready'=>'Siap','completed'=>'Selesai','cancelled'=>'Dibatalkan',
      'unpaid'=>'Belum Bayar','partial'=>'Dibayar Sebagian','paid'=>'Lunas'
    ];
    $class = in_array($status,['completed','paid','ready'],true) ? 'green' :
             (in_array($status,['cancelled'],true) ? 'red' :
             (in_array($status,['processing','approved','partial'],true) ? 'blue' : 'orange'));
    return '<span class="badge '.$class.'">'.e($labels[$status] ?? ucfirst($status)).'</span>';
}
function page_title(string $title): void { $GLOBALS['page_title']=$title; }
