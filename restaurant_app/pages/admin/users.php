<?php
$pageTitle = "Users – RestoSys";
$activePage = "users";
require_once __DIR__ . '/../../config/paths.php';
require_once CONFIG_PATH . '/db.php';

$msg='';
if($_SERVER['REQUEST_METHOD']==='POST'&&($_POST['action']??'')==='add'){
    $id  =mysqli_real_escape_string($conn,trim($_POST['US_id']));
    $sid =mysqli_real_escape_string($conn,trim($_POST['US_studentID']));
    $name=mysqli_real_escape_string($conn,trim($_POST['US_name']));
    $em  =mysqli_real_escape_string($conn,trim($_POST['US_email']));
    $wal =mysqli_real_escape_string($conn,trim($_POST['US_wallet']));
    $cal =mysqli_real_escape_string($conn,trim($_POST['US_caloriegoal']));
    if(mysqli_query($conn,"INSERT INTO Users(US_id,US_studentID,US_name,US_email,US_wallet,US_caloriegoal)VALUES('$id','$sid','$name','$em','$wal','$cal')"))
        $msg=['type'=>'success','text'=>'✅ เพิ่มผู้ใช้สำเร็จ'];
    else $msg=['type'=>'danger','text'=>'❌ '.mysqli_error($conn)];
}
if($_SERVER['REQUEST_METHOD']==='POST'&&($_POST['action']??'')==='edit'){
    $id  =mysqli_real_escape_string($conn,trim($_POST['US_id']));
    $sid =mysqli_real_escape_string($conn,trim($_POST['US_studentID']));
    $name=mysqli_real_escape_string($conn,trim($_POST['US_name']));
    $em  =mysqli_real_escape_string($conn,trim($_POST['US_email']));
    $wal =mysqli_real_escape_string($conn,trim($_POST['US_wallet']));
    $cal =mysqli_real_escape_string($conn,trim($_POST['US_caloriegoal']));
    if(mysqli_query($conn,"UPDATE Users SET US_studentID='$sid',US_name='$name',US_email='$em',US_wallet='$wal',US_caloriegoal='$cal' WHERE US_id='$id'"))
        $msg=['type'=>'success','text'=>'✅ แก้ไขสำเร็จ'];
    else $msg=['type'=>'danger','text'=>'❌ '.mysqli_error($conn)];
}
if($_SERVER['REQUEST_METHOD']==='POST'&&($_POST['action']??'')==='delete'){
    $id=mysqli_real_escape_string($conn,$_POST['US_id']);
    if(mysqli_query($conn,"DELETE FROM Users WHERE US_id='$id'")) $msg=['type'=>'success','text'=>'✅ ลบสำเร็จ'];
    else $msg=['type'=>'danger','text'=>'❌ '.mysqli_error($conn)];
}

$rows = mysqli_query($conn,"SELECT * FROM Users ORDER BY created_at DESC");
require_once INCLUDES_PATH . '/header.php';
?>

<div class="main">
  <div class="page-header">
    <h2>🎓 จัดการนักศึกษา</h2>
    <p>เพิ่ม แก้ไข และลบข้อมูลผู้ใช้งาน (นักศึกษา)</p>
  </div>

  <?php if($msg): ?>
  <div class="alert alert-<?=$msg['type']?>"><?=$msg['text']?></div>
  <?php endif; ?>

  <div class="card">
    <div class="card-title" style="justify-content:space-between">
      <span>รายชื่อนักศึกษา</span>
      <button class="btn btn-primary" onclick="openModal('addModal')">+ เพิ่มนักศึกษา</button>
    </div>
    <div class="table-wrap">
      <table>
        <thead><tr><th>User ID</th><th>รหัสนักศึกษา</th><th>ชื่อ</th><th>Email</th><th>เงินกระเป๋า</th><th>เป้าหมายแคลอรี่</th><th>จัดการ</th></tr></thead>
        <tbody>
        <?php if(mysqli_num_rows($rows)===0): ?>
          <tr><td colspan="7" style="text-align:center;color:var(--muted);padding:30px">ยังไม่มีข้อมูล</td></tr>
        <?php else: while($row=mysqli_fetch_assoc($rows)): ?>
          <tr>
            <td><code style="color:var(--accent);font-size:0.8rem"><?=htmlspecialchars($row['US_id'])?></code></td>
            <td><strong><?=htmlspecialchars($row['US_studentID'])?></strong></td>
            <td><?=htmlspecialchars($row['US_name'])?></td>
            <td style="color:var(--muted);font-size:0.82rem"><?=htmlspecialchars($row['US_email'])?></td>
            <td>฿<?=number_format($row['US_wallet'],2)?></td>
            <td><span class="badge badge-success"><?=number_format($row['US_caloriegoal'])?> kcal</span></td>
            <td>
              <button class="btn btn-edit btn-sm" onclick='openEditModal(<?=json_encode($row)?>)'>✏️</button>
              <form id="del-<?=htmlspecialchars($row['US_id'])?>" method="POST" style="display:inline">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="US_id" value="<?=htmlspecialchars($row['US_id'])?>">
              </form>
              <button class="btn btn-danger btn-sm" onclick="confirmDelete('del-<?=htmlspecialchars($row['US_id'])?>')">🗑️</button>
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
    <div class="modal-header"><div class="modal-title">🎓 เพิ่มนักศึกษา</div><button class="modal-close" onclick="closeModal('addModal')">✕</button></div>
    <form method="POST">
      <input type="hidden" name="action" value="add">
      <div class="form-grid">
        <div class="form-group"><label>User ID</label><input name="US_id" maxlength="10" required placeholder="US0000001"></div>
        <div class="form-group"><label>รหัสนักศึกษา</label><input name="US_studentID" maxlength="10" required placeholder="6400000001"></div>
        <div class="form-group"><label>ชื่อ-นามสกุล</label><input name="US_name" required placeholder="ชื่อ นามสกุล"></div>
        <div class="form-group"><label>Email</label><input name="US_email" type="email" required placeholder="student@university.ac.th"></div>
        <div class="form-group"><label>เงินกระเป๋า (บาท)</label><input name="US_wallet" type="number" step="0.01" value="0" min="0"></div>
        <div class="form-group"><label>เป้าหมายแคลอรี่ (kcal/วัน)</label><input name="US_caloriegoal" type="number" required placeholder="2000"></div>
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
    <div class="modal-header"><div class="modal-title">✏️ แก้ไขข้อมูลนักศึกษา</div><button class="modal-close" onclick="closeModal('editModal')">✕</button></div>
    <form method="POST">
      <input type="hidden" name="action" value="edit">
      <input type="hidden" name="US_id" id="edit_US_id">
      <div class="form-grid">
        <div class="form-group"><label>User ID</label><input id="e_US_id_s" disabled style="opacity:0.5"></div>
        <div class="form-group"><label>รหัสนักศึกษา</label><input name="US_studentID" id="edit_US_studentID" maxlength="10" required></div>
        <div class="form-group"><label>ชื่อ-นามสกุล</label><input name="US_name" id="edit_US_name" required></div>
        <div class="form-group"><label>Email</label><input name="US_email" id="edit_US_email" type="email" required></div>
        <div class="form-group"><label>เงินกระเป๋า (บาท)</label><input name="US_wallet" id="edit_US_wallet" type="number" step="0.01" min="0"></div>
        <div class="form-group"><label>เป้าหมายแคลอรี่ (kcal)</label><input name="US_caloriegoal" id="edit_US_calorie" type="number" required></div>
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
  document.getElementById('edit_US_id').value = row.US_id;
  document.getElementById('e_US_id_s').value = row.US_id;
  document.getElementById('edit_US_studentID').value = row.US_studentID;
  document.getElementById('edit_US_name').value = row.US_name;
  document.getElementById('edit_US_email').value = row.US_email;
  document.getElementById('edit_US_wallet').value = row.US_wallet;
  document.getElementById('edit_US_calorie').value = row.US_caloriegoal;
  openModal('editModal');
}
</script>
<?php require_once INCLUDES_PATH . '/footer.php'; ?>