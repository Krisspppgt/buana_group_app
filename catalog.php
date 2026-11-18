<?php
require_once 'includes/functions.php'; require_login(); page_title('Katalog Layanan');
$units=db()->query("SELECT * FROM business_units WHERE is_active=1 ORDER BY id")->fetchAll();
$st=db()->query("SELECT p.*,b.name unit_name,b.icon FROM products p JOIN business_units b ON b.id=p.unit_id WHERE p.is_active=1 ORDER BY b.id,p.name"); $products=$st->fetchAll();
include 'includes/header.php'; ?>
<div class="page-head"><div><h1>Katalog Layanan</h1><p class="sub">Pilih produk atau layanan dari unit usaha Buana Group.</p></div><a class="btn gold" href="order_create.php">＋ Pesan Sekarang</a></div>
<div class="grid unit-grid"><?php foreach($units as $u): ?><div class="card unit-card"><div class="unit-icon"><?=e($u['icon'])?></div><h3><?=e($u['name'])?></h3><p><?=e($u['description'])?></p></div><?php endforeach; ?></div>
<div class="card" style="margin-top:18px"><div class="toolbar"><h2 class="section-title" style="margin:0 auto 0 0">Produk & Paket</h2><input data-search=".product-row" placeholder="Cari produk..."></div><div class="table-wrap"><table><thead><tr><th>Produk / Layanan</th><th>Unit</th><th>Satuan</th><th>Harga mulai</th><th></th></tr></thead><tbody>
<?php foreach($products as $p): ?><tr class="product-row"><td><b><?=e($p['name'])?></b><div class="small-text"><?=e($p['description'])?></div></td><td><?=e($p['icon'].' '.$p['unit_name'])?></td><td><?=e($p['unit_label'])?></td><td class="money"><?=rupiah($p['price'])?></td><td><a class="btn small" href="order_create.php?product=<?=$p['id']?>">Pesan</a></td></tr><?php endforeach; ?>
<?php if(!$products): ?><tr><td colspan="5" class="empty">Produk belum tersedia.</td></tr><?php endif; ?></tbody></table></div></div>
<?php include 'includes/footer.php'; ?>