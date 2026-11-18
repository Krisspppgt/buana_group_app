<?php
require_once 'includes/functions.php'; require_login(); page_title('Detail Pesanan'); $pdo=db();$id=(int)($_GET['id']??0);
$st=$pdo->prepare("SELECT o.*,u.name customer,u.email,b.name unit_name FROM orders o JOIN users u ON u.id=o.customer_id JOIN business_units b ON b.id=o.unit_id WHERE o.id=?");$st->execute([$id]);$o=$st->fetch();
if(!$o || (current_user()['role']==='customer' && $o['customer_id']!=current_user()['id'])){http_response_code(404);exit('Pesanan tidak ditemukan');}
if($_SERVER['REQUEST_METHOD']==='POST' && can_manage()){
 verify_csrf();$new=$_POST['status']??'';$allowed=['pending','approved','processing','ready','completed','cancelled'];
 if(in_array($new,$allowed,true)){
  $pdo->prepare("UPDATE orders SET status=?,updated_at=NOW() WHERE id=?")->execute([$new,$id]);
  $pdo->prepare("INSERT INTO order_status_history(order_id,status,notes,changed_by) VALUES(?,?,?,?)")->execute([$id,$new,trim($_POST['status_note']??''),current_user()['id']]);
  if($new==='approved' && !$pdo->query("SELECT id FROM invoices WHERE order_id=".(int)$id)->fetch()){
    $pdo->prepare("INSERT INTO invoices(invoice_no,order_id,total,paid_amount,status,due_date) VALUES(?,?,?,0,'unpaid',DATE_ADD(CURDATE(),INTERVAL 14 DAY))")->execute(['INV-'.$o['order_no'],$id,$o['total']]);
  }
  log_activity('Ubah status pesanan',$o['order_no'].' menjadi '.$new);flash('Status pesanan diperbarui.');header('Location: order_view.php?id='.$id);exit;
 }
}
$items=$pdo->prepare("SELECT * FROM order_items WHERE order_id=?");$items->execute([$id]);$items=$items->fetchAll();
$hist=$pdo->prepare("SELECT h.*,u.name FROM order_status_history h LEFT JOIN users u ON u.id=h.changed_by WHERE h.order_id=? ORDER BY h.created_at DESC");$hist->execute([$id]);$hist=$hist->fetchAll();
include 'includes/header.php'; ?>
<div class="page-head"><div><h1>Detail Pesanan</h1><p class="sub"><?=e($o['order_no'])?> • <?=date('d F Y H:i',strtotime($o['created_at']))?></p></div><a class="btn secondary" href="orders.php">← Daftar Pesanan</a></div>
<div class="grid two-col"><div class="card"><h2 class="section-title">Informasi Pesanan</h2><p><span class="muted">Pelanggan</span><br><b><?=e($o['customer'])?></b> <span class="small-text">(<?=e($o['email'])?>)</span></p><p><span class="muted">Unit usaha</span><br><b><?=e($o['unit_name'])?></b></p><p><span class="muted">Status</span><br><?=status_badge($o['status'])?></p><p><span class="muted">Catatan</span><br><?=nl2br(e($o['notes']?:'-'))?></p>
<h2 class="section-title" style="margin-top:25px">Rincian Item</h2><div class="table-wrap"><table><thead><tr><th>Produk</th><th>Qty</th><th>Harga</th><th>Subtotal</th></tr></thead><tbody><?php foreach($items as $it): ?><tr><td><?=e($it['product_name'])?></td><td><?=number_format($it['qty'])?></td><td><?=rupiah($it['unit_price'])?></td><td><?=rupiah($it['subtotal'])?></td></tr><?php endforeach; ?></tbody></table></div><h2 style="text-align:right;font-size:20px">Total: <?=rupiah($o['total'])?></h2></div>
<div class="card"><h2 class="section-title">Alur Status</h2><div class="progress-list"><?php foreach($hist as $h): ?><div><b><?=status_badge($h['status'])?></b><div class="small-text"><?=date('d/m/Y H:i',strtotime($h['created_at']))?> • <?=e($h['name']??'Sistem')?></div><div><?=e($h['notes'])?></div></div><?php endforeach; ?></div>
<?php if(can_manage()): ?><hr style="border:0;border-top:1px solid #edf1f5;margin:20px 0"><h2 class="section-title">Perbarui Status</h2><form method="post"><?=csrf_field()?><div class="field"><label>Status baru</label><select name="status"><?php foreach(['pending'=>'Menunggu','approved'=>'Disetujui','processing'=>'Diproses','ready'=>'Siap','completed'=>'Selesai','cancelled'=>'Dibatalkan'] as $k=>$v): ?><option value="<?=$k?>" <?=$o['status']===$k?'selected':''?>><?=$v?></option><?php endforeach; ?></select></div><div class="field" style="margin-top:12px"><label>Catatan perubahan</label><input name="status_note" placeholder="Catatan untuk riwayat"></div><button class="btn" style="margin-top:14px">Simpan Status</button></form><?php endif; ?>
</div></div><?php include 'includes/footer.php'; ?>