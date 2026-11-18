<?php
require_once 'includes/functions.php'; require_login(); page_title('Dashboard');
$pdo=db();
$orders=(int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$pending=(int)$pdo->query("SELECT COUNT(*) FROM orders WHERE status IN ('pending','approved','processing')")->fetchColumn();
$sales=(float)$pdo->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE status<>'cancelled'")->fetchColumn();
$receivable=(float)$pdo->query("SELECT COALESCE(SUM(total-paid_amount),0) FROM invoices WHERE status<>'paid'")->fetchColumn();
$recent=$pdo->query("SELECT o.*,u.name customer,bu.name unit_name FROM orders o LEFT JOIN users u ON u.id=o.customer_id LEFT JOIN business_units bu ON bu.id=o.unit_id ORDER BY o.created_at DESC LIMIT 8")->fetchAll();
$units=$pdo->query("SELECT bu.*,COUNT(o.id) order_count,COALESCE(SUM(CASE WHEN o.status<>'cancelled' THEN o.total ELSE 0 END),0) revenue FROM business_units bu LEFT JOIN orders o ON o.unit_id=bu.id GROUP BY bu.id ORDER BY bu.id")->fetchAll();
include 'includes/header.php';
?>
<div class="page-head"><div><h1>Ringkasan Bisnis</h1><p class="sub">Pantau aktivitas seluruh unit usaha Buana Group.</p></div><a class="btn gold" href="order_create.php">＋ Buat Pesanan</a></div>
<div class="grid stats">
<div class="card"><span class="stat-icon">▤</span><div class="stat-label">Total Pesanan</div><div class="stat-value"><?=number_format($orders)?></div><div class="stat-note">Seluruh status pesanan</div></div>
<div class="card"><span class="stat-icon">◷</span><div class="stat-label">Pesanan Aktif</div><div class="stat-value"><?=number_format($pending)?></div><div class="stat-note">Menunggu hingga diproses</div></div>
<div class="card"><span class="stat-icon">↗</span><div class="stat-label">Nilai Transaksi</div><div class="stat-value"><?=rupiah($sales)?></div><div class="stat-note">Tidak termasuk pesanan batal</div></div>
<div class="card"><span class="stat-icon">▣</span><div class="stat-label">Sisa Tagihan</div><div class="stat-value"><?=rupiah($receivable)?></div><div class="stat-note">Invoice belum lunas</div></div>
</div>
<div class="grid two-col">
<div class="card"><h2 class="section-title">Pesanan Terbaru</h2><div class="table-wrap"><table><thead><tr><th>No. Pesanan</th><th>Pelanggan</th><th>Unit</th><th>Total</th><th>Status</th></tr></thead><tbody>
<?php foreach($recent as $r): ?><tr><td><a href="order_view.php?id=<?=$r['id']?>"><?=e($r['order_no'])?></a></td><td><?=e($r['customer']??'-')?></td><td><?=e($r['unit_name']??'-')?></td><td class="money"><?=rupiah($r['total'])?></td><td><?=status_badge($r['status'])?></td></tr><?php endforeach; ?>
<?php if(!$recent): ?><tr><td colspan="5" class="empty">Belum ada pesanan. Mulai dengan membuat pesanan pertama.</td></tr><?php endif; ?></tbody></table></div></div>
<div class="card"><h2 class="section-title">Aktivitas per Unit Usaha</h2><div class="progress-list">
<?php $max=max(1,...array_map(fn($x)=>(float)$x['revenue'],$units)); foreach($units as $u): ?><div><div class="progress-row"><b><?=e($u['name'])?></b><span><?=number_format($u['order_count'])?> pesanan</span></div><div class="bar"><span style="width:<?=min(100,((float)$u['revenue']/$max)*100)?>%"></span></div><div class="small-text"><?=rupiah($u['revenue'])?></div></div><?php endforeach; ?>
</div></div></div>
<div class="card" style="margin-top:17px"><h2 class="section-title">Akses Cepat Unit Usaha</h2><div class="grid unit-grid">
<?php foreach($units as $u): ?><div class="unit-card"><div class="unit-icon"><?=e($u['icon'])?></div><h3><?=e($u['name'])?></h3><p><?=e($u['description'])?></p></div><?php endforeach; ?>
</div></div>
<?php include 'includes/footer.php'; ?>