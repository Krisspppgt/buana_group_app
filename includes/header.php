<?php
require_once __DIR__.'/functions.php';
require_login();
$title = $GLOBALS['page_title'] ?? 'Dashboard';
$me=current_user();
?>
<!doctype html><html lang="id"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?=e($title)?> - Buana Group</title><link rel="stylesheet" href="assets/style.css">
</head><body>
<div class="app-shell">
<aside class="sidebar" id="sidebar">
  <div class="brand"><div class="brand-mark">🌿</div><div><strong>BUANA GROUP</strong><small>Indonesian Buana Group</small></div></div>
  <div class="nav-label">MENU UTAMA</div>
  <a href="index.php" class="nav-link">▦ <span>Dashboard</span></a>
  <a href="catalog.php" class="nav-link">▤ <span>Katalog Layanan</span></a>
  <a href="orders.php" class="nav-link">🛒 <span>Pesanan</span></a>
  <a href="quotations.php" class="nav-link">▧ <span>Quotation</span></a>
  <div class="nav-label">OPERASIONAL</div>
  <a href="invoices.php" class="nav-link">▣ <span>Invoice & Pembayaran</span></a>
  <a href="customers.php" class="nav-link">♙ <span>Pelanggan</span></a>
  <a href="reports.php" class="nav-link">▥ <span>Laporan</span></a>
  <?php if (is_admin()): ?>
  <div class="nav-label">PENGATURAN</div>
  <a href="products.php" class="nav-link">▦ <span>Produk & Unit</span></a>
  <a href="users.php" class="nav-link">♧ <span>Pengguna</span></a>
  <?php endif; ?>
  <div class="sidebar-bottom"><small>Versi awal • Local XAMPP</small></div>
</aside>
<main class="main">
<header class="topbar"><button class="menu-toggle" onclick="document.getElementById('sidebar').classList.toggle('show')">☰</button>
<div><strong><?=e($title)?></strong><small>PT Buana Sejahtera Semarang</small></div>
<div class="top-user"><div class="avatar"><?=e(strtoupper(substr($me['name'],0,1)))?></div><div><b><?=e($me['name'])?></b><small><?=e(str_replace('_',' ',$me['role']))?></small></div><a class="logout" href="logout.php">Keluar</a></div>
</header>
<section class="content">
<?php if ($f=get_flash()): ?><div class="alert <?=e($f['type'])?>"><?=e($f['message'])?></div><?php endif; ?>
