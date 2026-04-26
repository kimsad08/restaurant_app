<?php
$pageTitle = "Orders – RestoSys";
$activePage = "orders";
require_once __DIR__ . '/../../config/paths.php';
require_once CONFIG_PATH . '/db.php';

$msg='';
if($_SERVER['REQUEST_METHOD']==='POST'&&($_POST['action']??'')==='add'){
    $id  =mysqli_real_escape_string($conn,trim($_POST['OR_id']));
    $uid =mysqli_real_escape_string($conn,trim($_POST['US_id']));
    $dt  =mysqli_real_escape_string($conn,trim($_POST['OR_datetime']));
    $tp  =mysqli_real_escape_string($conn,trim($_POST['OR_totalprice']));
    $tc  =mysqli_real_escape_string($conn,trim($_POST['OR_totalcalorie']));
    if(mysqli_query($conn,"INSERT INTO Orders(OR_id,US_id,OR_datetime,OR_totalprice,OR_totalcalorie)VALUES('$id','$uid','$dt','$tp','$tc')"))
        $msg=['type'=>'success','text'=>'✅ เพิ่ม Order สำเร็จ'];
    else $msg=['type'=>'danger','text'=>'❌ '.mysqli_error($conn)];
}
if($_SERVER['REQUEST_METHOD']==='POST'&&($_POST['action']??'')==='edit'){
    $id  =mysqli_real_escape_string($conn,trim($_POST['OR_id']));
    $uid =mysqli_real_escape_string($conn,trim($_POST['US_id']));
    $tp  =mysqli_real_escape_string($conn,trim($_POST['OR_totalprice']));
    $tc  =mysqli_real_escape_string($conn,trim($_POST['OR_totalcalorie']));
    if(mysqli_query($conn,"UPDATE Orders SET US_id='$uid',OR_totalprice='$tp',OR_totalcalorie='$tc' WHERE OR_id='$id'"))
        $msg=['type'=>'success','text'=>'✅ แก้ไขสำเร็จ'];
    else $msg=['type'=>'danger','text'=>'❌ '.mysqli_error($conn)];
}
if($_SERVER['REQUEST_METHOD']==='POST'&&($_POST['action']??'')==='delete'){
    $id=mysqli_real_escape_string($conn,$_POST['OR_id']);
    if(mysqli_query($conn,"DELETE FROM Orders WHERE OR_id='$id'")) $msg=['type'=>'success','text'=>'✅ ลบสำเร็จ'];
    else $msg=['type'=>'danger','text'=>'❌ '.mysqli_error($conn)];
}

$rows  = mysqli_query($conn,"SELECT o.*, u.US_name, u.US_studentID FROM Orders o LEFT JOIN Users u ON o.US_id=u.US_id ORDER BY o.OR_datetime DESC");
$users = mysqli_query($conn,"SELECT US_id, US_name, US_studentID FROM Users ORDER BY US_name");
require_once INCLUDES_PATH . '/header.php';
?>

<div class="main">
  <div class="page-header">
    <h2>📋 จัดการ Orders</h2>
    <p>เพิ่ม แก้ไข และลบคำสั่งซื้อ</p>
  </div>

  <?php if($msg): ?>
  <div class="alert alert-<?=$msg['type']?>"><?=$msg['text']?></div>
  <?php endif; ?>

  <div class="card">
    <div class="card-title" style="justify-content:space-between">
      <span>รายการคำสั่งซื้อทั้งหมด</span>
      <button class="btn btn-primary" onclick="openModal('addModal')">+ เพิ่ม Order</button>
    </div>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Order ID</th><th>นักศึกษา</th><th>รหัสนักศึกษา</th><th>วันที่/เวลา</th><th>ราคารวม</th><th>แคลอรี่รวม</th><th>จัดการ</th></tr></thead>
        <tbody>
        <?php if(mysqli_num_rows($rows)===0): ?>
          <tr><td colspan="7" style="text-align:center;color:var(--muted);padding:30px">ยังไม่มีข้อมูล</td></tr>
        <?php else: while($row=mysqli_fetch_assoc($rows)): ?>
          <tr>
            <td><code style="color:var(--accent);font-size:0.8rem"><?=htmlspecialchars($row['OR_id'])?></code></td>
            <td><?=htmlspecialchars($row['US_name']??'-')?></td>
            <td style="color:var(--muted)"><?=htmlspecialchars($row['US_studentID']??'-')?></td>
            <td style="font-size:0.82rem"><?=htmlspecialchars($row['OR_datetime'])?></td>
            <td><strong>฿<?=number_format($row['OR_totalprice'],2)?></strong></td>
            <td><span class="badge badge-info"><?=number_format($row['OR_totalcalorie'])?> kcal</span></td>
            <td>
              <a href="<?= URL_ORDER_DETAIL ?>?OR_id=<?=urlencode($row['OR_id'])?>" class="btn btn-sm" style="background:rgba(34,197,94,0.12);color:var(--success);border:1px solid rgba(34,197,94,0.25);font-size:0.78rem">📝 รายละเอียด</a>
              <button class="btn btn-edit btn-sm" onclick='openEditModal(<?=json_encode($row)?>)'>✏️</button>
              <form id="del-<?=htmlspecialchars($row['OR_id'])?>" method="POST" style="display:inline">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="OR_id" value="<?=htmlspecialchars($row['OR_id'])?>">
              </form>
              <button class="btn btn-danger btn-sm" onclick="confirmDelete('del-<?=htmlspecialchars($row['OR_id'])?>')">🗑️</button>
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
    <div class="modal-header"><div class="modal-title">📋 เพิ่ม Order</div><button class="modal-close" onclick="closeModal('addModal')">✕</button></div>
    <form method="POST">
      <input type="hidden" name="action" value="add">
      <div class="form-grid">
        <div class="form-group"><label>Order ID</label><input name="OR_id" maxlength="10" required placeholder="OR0000001"></div>
        <div class="form-group"><label>นักศึกษา</label>
          <select name="US_id" required>
            <option value="">-- เลือกนักศึกษา --</option>
            <?php mysqli_data_seek($users,0); while($u=mysqli_fetch_assoc($users)): ?>
            <option value="<?=htmlspecialchars($u['US_id'])?>"><?=htmlspecialchars($u['US_name'])?> (<?=htmlspecialchars($u['US_studentID'])?>)</option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="form-group"><label>วันที่/เวลา</label><input name="OR_datetime" type="datetime-local" required value="<?=date('Y-m-d\TH:i')?>"></div>
        <div class="form-group"><label>ราคารวม (บาท)</label><input name="OR_totalprice" type="number" step="0.01" value="0" min="0"></div>
        <div class="form-group"><label>แคลอรี่รวม (kcal)</label><input name="OR_totalcalorie" type="number" value="0" min="0"></div>
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
    <div class="modal-header"><div class="modal-title">✏️ แก้ไข Order</div><button class="modal-close" onclick="closeModal('editModal')">✕</button></div>
    <form method="POST">
      <input type="hidden" name="action" value="edit">
      <input type="hidden" name="OR_id" id="edit_OR_id">
      <div class="form-grid">
        <div class="form-group"><label>Order ID</label><input id="e_OR_id_s" disabled style="opacity:0.5"></div>
        <div class="form-group"><label>นักศึกษา</label>
          <select name="US_id" id="edit_US_id" required>
            <?php mysqli_data_seek($users,0); while($u=mysqli_fetch_assoc($users)): ?>
            <option value="<?=htmlspecialchars($u['US_id'])?>"><?=htmlspecialchars($u['US_name'])?> (<?=htmlspecialchars($u['US_studentID'])?>)</option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="form-group"><label>ราคารวม (บาท)</label><input name="OR_totalprice" id="edit_OR_tp" type="number" step="0.01" min="0"></div>
        <div class="form-group"><label>แคลอรี่รวม (kcal)</label><input name="OR_totalcalorie" id="edit_OR_tc" type="number" min="0"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn" style="background:var(--surface2);color:var(--text)" onclick="closeModal('editModal')">ยกเลิก</button>
        <button type="submit" class="btn btn-primary">บันทึกการแก้ไข</button>
      </div>
    </form>
  </div>
</div>

<script>
function openEditModal(row) {
  document.getElementById('edit_OR_id').value = row.OR_id;
  document.getElementById('e_OR_id_s').value = row.OR_id;
  document.getElementById('edit_US_id').value = row.US_id;
  document.getElementById('edit_OR_tp').value = row.OR_totalprice;
  document.getElementById('edit_OR_tc').value = row.OR_totalcalorie;
  openModal('editModal');
}
</script>
<?php require_once INCLUDES_PATH . '/footer.php'; ?>