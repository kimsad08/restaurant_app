<?php
$pageTitle = "Order Detail – RestoSys";
$activePage = "orderdetail";
require_once __DIR__ . '/../../config/paths.php';
require_once CONFIG_PATH . '/db.php';

$filterOR = isset($_GET['OR_id']) ? mysqli_real_escape_string($conn, $_GET['OR_id']) : '';
$msg='';

if($_SERVER['REQUEST_METHOD']==='POST'&&($_POST['action']??'')==='add'){
    $id  =mysqli_real_escape_string($conn,trim($_POST['ORT_id']));
    $oid =mysqli_real_escape_string($conn,trim($_POST['OR_id']));
    $mid =mysqli_real_escape_string($conn,trim($_POST['MN_id']));
    $qty =mysqli_real_escape_string($conn,trim($_POST['ORT_quantity']));
    $ppu =mysqli_real_escape_string($conn,trim($_POST['ORT_priceperunit']));
    if(mysqli_query($conn,"INSERT INTO OrderDetail(ORT_id,OR_id,MN_id,ORT_quantity,ORT_priceperunit)VALUES('$id','$oid','$mid','$qty','$ppu')"))
        $msg=['type'=>'success','text'=>'✅ เพิ่ม Order Detail สำเร็จ'];
    else $msg=['type'=>'danger','text'=>'❌ '.mysqli_error($conn)];
    if($filterOR) header("Location: " . URL_ORDER_DETAIL . "?OR_id=$filterOR");
}
if($_SERVER['REQUEST_METHOD']==='POST'&&($_POST['action']??'')==='edit'){
    $id  =mysqli_real_escape_string($conn,trim($_POST['ORT_id']));
    $oid =mysqli_real_escape_string($conn,trim($_POST['OR_id']));
    $mid =mysqli_real_escape_string($conn,trim($_POST['MN_id']));
    $qty =mysqli_real_escape_string($conn,trim($_POST['ORT_quantity']));
    $ppu =mysqli_real_escape_string($conn,trim($_POST['ORT_priceperunit']));
    if(mysqli_query($conn,"UPDATE OrderDetail SET OR_id='$oid',MN_id='$mid',ORT_quantity='$qty',ORT_priceperunit='$ppu' WHERE ORT_id='$id'"))
        $msg=['type'=>'success','text'=>'✅ แก้ไขสำเร็จ'];
    else $msg=['type'=>'danger','text'=>'❌ '.mysqli_error($conn)];
}
if($_SERVER['REQUEST_METHOD']==='POST'&&($_POST['action']??'')==='delete'){
    $id=mysqli_real_escape_string($conn,$_POST['ORT_id']);
    $backOR = mysqli_real_escape_string($conn,$_POST['back_OR_id']??'');
    if(mysqli_query($conn,"DELETE FROM OrderDetail WHERE ORT_id='$id'")) {
        if($backOR) { header("Location: " . URL_ORDER_DETAIL . "?OR_id=$backOR"); exit; }
        $msg=['type'=>'success','text'=>'✅ ลบสำเร็จ'];
    } else $msg=['type'=>'danger','text'=>'❌ '.mysqli_error($conn)];
}

$where = $filterOR ? "WHERE od.OR_id='$filterOR'" : '';
$rows  = mysqli_query($conn,
    "SELECT od.*, m.MN_name, o.OR_datetime, u.US_name
     FROM OrderDetail od
     LEFT JOIN Menu m ON od.MN_id=m.MN_id
     LEFT JOIN Orders o ON od.OR_id=o.OR_id
     LEFT JOIN Users u ON o.US_id=u.US_id
     $where
     ORDER BY od.created_at DESC");

$orders= mysqli_query($conn,"SELECT OR_id FROM Orders ORDER BY OR_datetime DESC");
$menus = mysqli_query($conn,"SELECT MN_id, MN_name, MN_price FROM Menu WHERE MN_status='available' ORDER BY MN_name");

$filterInfo = null;
if($filterOR) {
    $fi = mysqli_fetch_assoc(mysqli_query($conn,"SELECT o.OR_id, u.US_name, o.OR_datetime FROM Orders o LEFT JOIN Users u ON o.US_id=u.US_id WHERE o.OR_id='$filterOR'"));
    $filterInfo = $fi;
}
require_once INCLUDES_PATH . '/header.php';
?>

<div class="main">
  <div class="page-header">
    <h2>📝 Order Detail</h2>
    <p>รายละเอียดสินค้าในแต่ละคำสั่งซื้อ</p>
  </div>

  <?php if($msg): ?>
  <div class="alert alert-<?=$msg['type']?>"><?=$msg['text']?></div>
  <?php endif; ?>

  <?php if($filterInfo): ?>
  <div style="background:rgba(249,115,22,0.08);border:1px solid rgba(249,115,22,0.25);border-radius:10px;padding:14px 18px;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between">
    <div>
      <span style="color:var(--accent);font-weight:700">Order: <?=htmlspecialchars($filterInfo['OR_id'])?></span>
      <span style="color:var(--muted);margin-left:14px;font-size:0.85rem"><?=htmlspecialchars($filterInfo['US_name']??'-')?> · <?=htmlspecialchars($filterInfo['OR_datetime'])?></span>
    </div>
    <a href="<?= URL_ORDER_DETAIL ?>" class="btn btn-sm" style="background:var(--surface2);color:var(--muted)">✕ ดูทั้งหมด</a>
  </div>
  <?php endif; ?>

  <div class="card">
    <div class="card-title" style="justify-content:space-between">
      <span>รายการสินค้า</span>
      <button class="btn btn-primary" onclick="openModal('addModal')">+ เพิ่มรายการ</button>
    </div>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Detail ID</th><th>Order ID</th><th>เมนู</th><th>จำนวน</th><th>ราคา/หน่วย</th><th>รวม</th><th>จัดการ</th></tr></thead>
        <tbody>
        <?php if(mysqli_num_rows($rows)===0): ?>
          <tr><td colspan="7" style="text-align:center;color:var(--muted);padding:30px">ยังไม่มีข้อมูล</td></tr>
        <?php else: while($row=mysqli_fetch_assoc($rows)): ?>
          <tr>
            <td><code style="color:var(--accent);font-size:0.8rem"><?=htmlspecialchars($row['ORT_id'])?></code></td>
            <td><a href="<?= URL_ORDER_DETAIL ?>?OR_id=<?=urlencode($row['OR_id'])?>" style="color:var(--info);font-size:0.82rem"><?=htmlspecialchars($row['OR_id'])?></a></td>
            <td><strong><?=htmlspecialchars($row['MN_name']??$row['MN_id'])?></strong></td>
            <td><?=htmlspecialchars($row['ORT_quantity'])?></td>
            <td>฿<?=number_format($row['ORT_priceperunit'],2)?></td>
            <td><strong>฿<?=number_format($row['ORT_quantity']*$row['ORT_priceperunit'],2)?></strong></td>
            <td>
              <button class="btn btn-edit btn-sm" onclick='openEditModal(<?=json_encode($row)?>)'>✏️</button>
              <form id="del-<?=htmlspecialchars($row['ORT_id'])?>" method="POST" style="display:inline">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="ORT_id" value="<?=htmlspecialchars($row['ORT_id'])?>">
                <input type="hidden" name="back_OR_id" value="<?=htmlspecialchars($filterOR)?>">
              </form>
              <button class="btn btn-danger btn-sm" onclick="confirmDelete('del-<?=htmlspecialchars($row['ORT_id'])?>')">🗑️</button>
            </td>
          </tr>
        <?php endwhile; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="modal-overlay" id="addModal">
  <div class="modal">
    <div class="modal-header"><div class="modal-title">📝 เพิ่มรายการในออเดอร์</div><button class="modal-close" onclick="closeModal('addModal')">✕</button></div>
    <form method="POST">
      <input type="hidden" name="action" value="add">
      <div class="form-grid">
        <div class="form-group"><label>Detail ID</label><input name="ORT_id" maxlength="10" required placeholder="ORT000001"></div>
        <div class="form-group"><label>Order</label>
          <select name="OR_id" id="add_OR_id" required onchange="updatePrice()">
            <option value="">-- เลือก Order --</option>
            <?php mysqli_data_seek($orders,0); while($o=mysqli_fetch_assoc($orders)): ?>
            <option value="<?=htmlspecialchars($o['OR_id'])?>" <?=$filterOR===$o['OR_id']?'selected':''?>><?=htmlspecialchars($o['OR_id'])?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="form-group"><label>เมนู</label>
          <select name="MN_id" id="add_MN_id" required onchange="updatePrice()">
            <option value="">-- เลือกเมนู --</option>
            <?php mysqli_data_seek($menus,0); while($m=mysqli_fetch_assoc($menus)): ?>
            <option value="<?=htmlspecialchars($m['MN_id'])?>" data-price="<?=htmlspecialchars($m['MN_price'])?>"><?=htmlspecialchars($m['MN_name'])?> (฿<?=number_format($m['MN_price'],2)?>)</option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="form-group"><label>จำนวน</label><input name="ORT_quantity" id="add_qty" type="number" min="1" value="1" required onchange="updatePrice()"></div>
        <div class="form-group"><label>ราคา/หน่วย</label><input name="ORT_priceperunit" id="add_ppu" type="number" step="0.01" min="0" required></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn" style="background:var(--surface2);color:var(--text)" onclick="closeModal('addModal')">ยกเลิก</button>
        <button type="submit" class="btn btn-primary">บันทึก</button>
      </div>
    </form>
  </div>
</div>

<div class="modal-overlay" id="editModal">
  <div class="modal">
    <div class="modal-header"><div class="modal-title">✏️ แก้ไขรายการ</div><button class="modal-close" onclick="closeModal('editModal')">✕</button></div>
    <form method="POST">
      <input type="hidden" name="action" value="edit">
      <input type="hidden" name="ORT_id" id="edit_ORT_id">
      <div class="form-grid">
        <div class="form-group"><label>Detail ID</label><input id="e_ORT_id_s" disabled style="opacity:0.5"></div>
        <div class="form-group"><label>Order ID</label>
          <select name="OR_id" id="edit_OR_id" required>
            <?php mysqli_data_seek($orders,0); while($o=mysqli_fetch_assoc($orders)): ?>
            <option value="<?=htmlspecialchars($o['OR_id'])?>"><?=htmlspecialchars($o['OR_id'])?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="form-group"><label>เมนู</label>
          <select name="MN_id" id="edit_MN_id" required>
            <?php mysqli_data_seek($menus,0); while($m=mysqli_fetch_assoc($menus)): ?>
            <option value="<?=htmlspecialchars($m['MN_id'])?>"><?=htmlspecialchars($m['MN_name'])?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="form-group"><label>จำนวน</label><input name="ORT_quantity" id="edit_qty" type="number" min="1" required></div>
        <div class="form-group"><label>ราคา/หน่วย</label><input name="ORT_priceperunit" id="edit_ppu" type="number" step="0.01" min="0" required></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn" style="background:var(--surface2);color:var(--text)" onclick="closeModal('editModal')">ยกเลิก</button>
        <button type="submit" class="btn btn-primary">บันทึกการแก้ไข</button>
      </div>
    </form>
  </div>
</div>

<script>
function updatePrice() {
  const sel = document.getElementById('add_MN_id');
  const opt = sel.options[sel.selectedIndex];
  const price = opt ? opt.getAttribute('data-price') : 0;
  if(price) document.getElementById('add_ppu').value = parseFloat(price).toFixed(2);
}
function openEditModal(row) {
  document.getElementById('edit_ORT_id').value = row.ORT_id;
  document.getElementById('e_ORT_id_s').value = row.ORT_id;
  document.getElementById('edit_OR_id').value = row.OR_id;
  document.getElementById('edit_MN_id').value = row.MN_id;
  document.getElementById('edit_qty').value = row.ORT_quantity;
  document.getElementById('edit_ppu').value = row.ORT_priceperunit;
  openModal('editModal');
}
</script>
<?php require_once INCLUDES_PATH . '/footer.php'; ?>