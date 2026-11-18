<?php
require_once 'includes/functions.php'; require_login(); page_title('Laporan');$pdo=db();
$rows=$pdo->query("SELECT b.name,COUNT(o.id) orders_count,COALESCE(SUM(CASE WHEN o.status<>'cancelled' THEN o.total ELSE 0 END),0) revenue FROM business_units b LEFT JOIN orders o ON o.unit_id=b.id GROUP BY b.id ORDER BY b.id")->fetchAll();
$total=0;foreach($rows as $r)$total+=(float)$r['revenue'];
$payments=$pdo->query("SELECT COALESCE(SUM(amount),0) FROM payments")->fetchColumn();
$unpaid=$pdo->query("SELECT COALESCE(SUM(total-paid_amount),0) FROM invoices WHERE status<>'paid'")->fetchColumn();
include 'includes/header.php'; ?>
<div class="page-head"><div><h1>Laporan Ringkas</h1><p class="sub">Rekap transaksi per unit usaha. Angka mengikuti data yang dimasukkan ke aplikasi.</p></div></div>
<div class="grid stats"><div class="card"><div class="stat-label">Nilai Pesanan</div><div class="stat-value"><?=rupiah($total)?></div></div><div class="card"><div class="stat-label">Pembayaran Diterima</div><div class="stat-value"><?=rupiah($payments)?></div></div><div class="card"><div class="stat-label">Piutang Berjalan</div><div class="stat-value"><?=rupiah($unpaid)?></div></div><div class="card"><div class="stat-label">Unit Usaha</div><div class="stat-value"><?=count($rows)?></div></div></div>
<div class="card"><h2 class="section-title">Rekap per Unit Usaha</h2><div class="table-wrap"><table><thead><tr><th>Unit Usaha</th><th>Jumlah Pesanan</th><th>Nilai Transaksi</th><th>Kontribusi ke Total</th></tr></thead><tbody><?php foreach($rows as $r): ?><tr><td><b><?=e($r['name'])?></b></td><td><?=number_format($r['orders_count'])?></td><td><?=rupiah($r['revenue'])?></td><td><?= $total>0?number_format($r['revenue']/$total*100,1):'0.0' ?>%</td></tr><?php endforeach; ?></tbody></table></div></div>
<div class="alert warning" style="margin-top:16px">Catatan: laporan ini merupakan rekap transaksi dasar, belum merupakan laporan laba rugi akuntansi. Biaya vendor, biaya variabel, pajak, dan rekonsiliasi bank perlu dimasukkan pada pengembangan berikutnya.</div>
<?php include 'includes/footer.php'; ?>