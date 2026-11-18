<?php
require_once 'includes/functions.php'; require_login(); page_title('Invoice & Pembayaran'); $pdo=db();
if($_SERVER['REQUEST_METHOD']==='POST' && in_array(current_user()['role'],['super_admin','keuangan'],true)){
 verify_csrf();$iid=(int)$_POST['invoice_id'];$amount=(float)$_POST['amount'];$method=trim($_POST['method']??'Transfer');$note=trim($_POST['note']??'');
 $st=$pdo->prepare("SELECT * FROM invoices WHERE id=?");$st->execute([$iid]);$inv=$st->fetch();
 if($inv && $amount>0 && $amount<=($inv['total']-$inv['paid_amount'])){
  $pdo->prepare("INSERT INTO payments(invoice_id,amount,method,note,received_by) VALUES(?,?,?,?,?)")->execute([$iid,$amount,$method,$note,current_user()['id']]);
  $paid=$inv['paid_amount']+$amount;$status=$paid >= $inv['total']?'paid':($paid>0?'partial':'unpaid');
  $pdo->prepare("UPDATE invoices SET paid_amount=?,status=? WHERE id=?")->execute([$paid,$status,$iid]);
  log_activity('Catat pembayaran',$inv['invoice_no'].' '.rupiah($amount));flash('Pembayaran berhasil dicatat.');
 }else flash('Nominal pembayaran tidak valid.','error');
 header('Location: invoices.php');exit;
}
$sql="SELECT i.*,o.order_no,u.name customer FROM invoices i JOIN orders o ON o.id=i.order_id JOIN users u ON u.id=o.customer_id";
$args=[];if(current_user()['role']==='customer'){$sql.=" WHERE o.customer_id=?";$args[]=current_user()['id'];}$sql.=" ORDER BY i.created_at DESC";
$st=$pdo->prepare($sql);$st->execute($args);$rows=$st->fetchAll();
include 'includes/header.php'; ?>
<div class="page-head"><div><h1>Invoice & Pembayaran</h1><p class="sub">Pantau tagihan dan rekonsiliasi penerimaan pembayaran.</p></div></div>
<div class="card"><div class="table-wrap"><table><thead><tr><th>No. Invoice</th><th>No. Pesanan</th><th>Pelanggan</th><th>Jatuh Tempo</th><th>Total</th><th>Dibayar</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
<?php foreach($rows as $r): ?><tr><td><b><?=e($r['invoice_no'])?></b></td><td><?=e($r['order_no'])?></td><td><?=e($r['customer'])?></td><td><?=date('d/m/Y',strtotime($r['due_date']))?></td><td><?=rupiah($r['total'])?></td><td><?=rupiah($r['paid_amount'])?></td><td><?=status_badge($r['status'])?></td><td><?php if(in_array(current_user()['role'],['super_admin','keuangan'],true) && $r['status']!=='paid'): ?><button class="btn small" onclick="document.getElementById('pay<?=$r['id']?>').style.display='block'">Catat Bayar</button><?php endif; ?></td></tr>
<?php if(in_array(current_user()['role'],['super_admin','keuangan'],true) && $r['status']!=='paid'): ?><tr id="pay<?=$r['id']?>" style="display:none"><td colspan="8"><form method="post" class="form-grid"><?=csrf_field()?><input type="hidden" name="invoice_id" value="<?=$r['id']?>"><div class="field"><label>Nominal (maks. <?=rupiah($r['total']-$r['paid_amount'])?>)</label><input type="number" name="amount" min="1" max="<?=$r['total']-$r['paid_amount']?>" required></div><div class="field"><label>Metode</label><select name="method"><option>Transfer</option><option>Cash</option><option>QRIS (manual)</option><option>Virtual Account (manual)</option></select></div><div class="field"><label>Catatan / referensi</label><input name="note"></div><div class="field" style="justify-content:end"><button class="btn">Simpan Pembayaran</button></div></form></td></tr><?php endif; endforeach; ?>
<?php if(!$rows): ?><tr><td colspan="8" class="empty">Invoice akan dibuat setelah pesanan disetujui.</td></tr><?php endif; ?></tbody></table></div></div>
<?php include 'includes/footer.php'; ?>