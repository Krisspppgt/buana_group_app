<?php
require_once 'includes/functions.php'; require_login(); page_title('Manajemen Pesanan');
$sql="SELECT o.*,u.name customer,b.name unit_name FROM orders o LEFT JOIN users u ON u.id=o.customer_id LEFT JOIN business_units b ON b.id=o.unit_id";
$args=[]; if(current_user()['role']==='customer'){$sql.=" WHERE o.customer_id=?";$args[]=current_user()['id'];}
$sql.=" ORDER BY o.created_at DESC"; $st=db()->prepare($sql);$st->execute($args);$rows=$st->fetchAll();
include 'includes/header.php'; ?>
<div class="page-head"><div><h1>Manajemen Pesanan</h1><p class="sub">Pantau dan kelola seluruh transaksi pelanggan.</p></div><a class="btn gold" href="order_create.php">＋ Buat Pesanan</a></div>
<div class="card"><div class="toolbar"><input data-search="tbody tr" placeholder="Cari nomor, pelanggan, unit..."></div><div class="table-wrap"><table><thead><tr><th>No. Pesanan</th><th>Tanggal</th><th>Pelanggan</th><th>Unit Usaha</th><th>Total</th><th>Status</th><th></th></tr></thead><tbody>
<?php foreach($rows as $r): ?><tr><td><b><?=e($r['order_no'])?></b></td><td><?=date('d/m/Y',strtotime($r['created_at']))?></td><td><?=e($r['customer'])?></td><td><?=e($r['unit_name'])?></td><td class="money"><?=rupiah($r['total'])?></td><td><?=status_badge($r['status'])?></td><td><a class="btn small secondary" href="order_view.php?id=<?=$r['id']?>">Detail</a></td></tr><?php endforeach; ?>
<?php if(!$rows): ?><tr><td colspan="7" class="empty">Belum ada data pesanan.</td></tr><?php endif; ?></tbody></table></div></div>
<?php include 'includes/footer.php'; ?>