<?php
require_once 'includes/functions.php'; require_login(); page_title('Buat Pesanan');
$pdo=db(); $units=$pdo->query("SELECT * FROM business_units WHERE is_active=1 ORDER BY id")->fetchAll();
$products=$pdo->query("SELECT * FROM products WHERE is_active=1 ORDER BY name")->fetchAll();
$customers=$pdo->query("SELECT id,name,email FROM users WHERE role='customer' AND is_active=1 ORDER BY name")->fetchAll();
$selected=(int)($_GET['product']??0); $error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();
 $product_id=(int)($_POST['product_id']??0); $qty=max(1,(int)($_POST['qty']??1));
 $ps=$pdo->prepare("SELECT p.*,b.id unit_id FROM products p JOIN business_units b ON b.id=p.unit_id WHERE p.id=? AND p.is_active=1"); $ps->execute([$product_id]); $p=$ps->fetch();
 $customer_id=(current_user()['role']==='customer')?current_user()['id']:(int)($_POST['customer_id']??0);
 if(!$p || !$customer_id){$error='Pilih produk dan pelanggan yang valid.';}
 else {
  $no='BG-'.date('ymd').'-'.strtoupper(bin2hex(random_bytes(3)));
  $total=$p['price']*$qty;
  $st=$pdo->prepare("INSERT INTO orders(order_no,customer_id,unit_id,status,total,notes,created_by) VALUES(?,?,?,'pending',?,?,?)");
  $st->execute([$no,$customer_id,$p['unit_id'],$total,trim($_POST['notes']??''),current_user()['id']]); $oid=(int)$pdo->lastInsertId();
  $pdo->prepare("INSERT INTO order_items(order_id,product_id,product_name,qty,unit_price,subtotal) VALUES(?,?,?,?,?,?)")->execute([$oid,$p['id'],$p['name'],$qty,$p['price'],$total]);
  $pdo->prepare("INSERT INTO order_status_history(order_id,status,notes,changed_by) VALUES(?,'pending','Pesanan dibuat',?)")->execute([$oid,current_user()['id']]);
  log_activity('Buat pesanan',$no); flash('Pesanan berhasil dibuat. Admin dapat meninjau dan menyetujui pesanan.'); header('Location: order_view.php?id='.$oid); exit;
 }
}
include 'includes/header.php'; ?>
<div class="page-head"><div><h1>Buat Pesanan</h1><p class="sub">Isi permintaan pesanan untuk unit usaha terkait.</p></div><a class="btn secondary" href="orders.php">← Kembali</a></div>
<div class="card form-card"><?php if($error): ?><div class="alert error"><?=e($error)?></div><?php endif; ?>
<form method="post"><?=csrf_field()?><div class="form-grid">
<?php if(current_user()['role']!=='customer'): ?><div class="field"><label>Pelanggan</label><select name="customer_id" required><option value="">Pilih pelanggan</option><?php foreach($customers as $c): ?><option value="<?=$c['id']?>"><?=e($c['name'].' — '.$c['email'])?></option><?php endforeach; ?></select></div><?php endif; ?>
<div class="field"><label>Produk / Layanan</label><select name="product_id" id="productSelect" required><option value="">Pilih produk</option><?php foreach($products as $p): ?><option value="<?=$p['id']?>" data-price="<?=$p['price']?>" <?=($selected===$p['id'])?'selected':''?>><?=e($p['name'])?> — <?=rupiah($p['price'])?></option><?php endforeach; ?></select></div>
<div class="field"><label>Jumlah</label><input type="number" name="qty" id="qty" min="1" value="1" required></div>
<div class="field"><label>Estimasi total</label><input id="estimate" readonly value="Pilih produk"></div>
<div class="field full"><label>Catatan / Spesifikasi / Tanggal kebutuhan</label><textarea name="notes" placeholder="Contoh: tanggal pengiriman, lokasi, jumlah peserta, ukuran, preferensi menu..."></textarea></div>
</div><div class="form-actions"><button class="btn" type="submit">Kirim Pesanan</button><a class="btn secondary" href="catalog.php">Lihat Katalog</a></div></form></div>
<script>
const ps=document.getElementById('productSelect'),qty=document.getElementById('qty'),est=document.getElementById('estimate');
function calc(){let o=ps.options[ps.selectedIndex],p=Number(o?.dataset.price||0),q=Number(qty.value||1);est.value=p?'Rp '+(p*q).toLocaleString('id-ID'):'Pilih produk';}ps.addEventListener('change',calc);qty.addEventListener('input',calc);calc();
</script>
<?php include 'includes/footer.php'; ?>