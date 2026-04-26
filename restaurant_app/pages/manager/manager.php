<?php
$pageTitle = "Manager – RestoSys";
$activePage = "manager";
require_once __DIR__ . '/../../config/paths.php';
require_once CONFIG_PATH . '/db.php';

$msg = '';

if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='add') {
    $id    = mysqli_real_escape_string($conn, trim($_POST['MG_id']));
    $ad_id = mysqli_real_escape_string($conn, trim($_POST['AD_id']));
    $name  = mysqli_real_escape_string($conn, trim($_POST['MG_name']));
    $email = mysqli_real_escape_string($conn, trim($_POST['MG_email']));
    $addr  = mysqli_real_escape_string($conn, trim($_POST['MG_address']));
    $phone = mysqli_real_escape_string($conn, trim($_POST['MG_phone']));
    $sql = "INSERT INTO Manager (MG_id,AD_id,MG_name,MG_email,MG_address,MG_phone) VALUES ('$id','$ad_id','$name','$email','$addr','$phone')";
    if (mysqli_query($conn,$sql)) $msg=['type'=>'success','text'=>'✅ เพิ่ม Manager สำเร็จ'];
    else $msg=['type'=>'danger','text'=>'❌ '.mysqli_error($conn)];
}
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='edit') {
    $id    = mysqli_real_escape_string($conn, trim($_POST['MG_id']));
    $ad_id = mysqli_real_escape_string($conn, trim($_POST['AD_id']));
    $name  = mysqli_real_escape_string($conn, trim($_POST['MG_name']));
    $email = mysqli_real_escape_string($conn, trim($_POST['MG_email']));
    $addr  = mysqli_real_escape_string($conn, trim($_POST['MG_address']));
    $phone = mysqli_real_escape_string($conn, trim($_POST['MG_phone']));
    $sql = "UPDATE Manager SET AD_id='$ad_id',MG_name='$name',MG_email='$email',MG_address='$addr',MG_phone='$phone' WHERE MG_id='$id'";
    if (mysqli_query($conn,$sql)) $msg=['type'=>'success','text'=>'✅ แก้ไขสำเร็จ'];
    else $msg=['type'=>'danger','text'=>'❌ '.mysqli_error($conn)];
}
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='delete') {
    $id = mysqli_real_escape_string($conn, $_POST['MG_id']);
    if (mysqli_query($conn,"DELETE FROM Manager WHERE MG_id='$id'")) $msg=['type'=>'success','text'=>'✅ ลบสำเร็จ'];
    else $msg=['type'=>'danger','text'=>'❌ '.mysqli_error($conn)];
}

$rows    = mysqli_query($conn,"SELECT m.*, a.AD_name FROM Manager m LEFT JOIN Admin a ON m.AD_id=a.AD_id ORDER BY m.created_at DESC");
$admins  = mysqli_query($conn,"SELECT AD_id, AD_name FROM Admin ORDER BY AD_name");
require_once INCLUDES_PATH . '/header.php';
?>

<div class="main">
  <div class="page-header">
    <h2>🧑‍💼 จัดการ Manager</h2>
    <p>เพิ่ม แก้ไข และลบผู้จัดการร้านอาหาร</p>
  </div>

  <?php if($msg): ?>
  <div class="alert alert-<?=$msg['type']?>"><?=$msg['text']?></div>
  <?php endif; ?>

  <div class="card">
    <div class="card-title" style="justify-content:space-between">
      <span>รายชื่อ Manager</span>
      <button class="btn btn-primary" onclick="openModal('addModal')">+ เพิ่ม Manager</button>
    </div>
    <div class="table-wrap">
      <table>
        <thead><tr><th>ID</th><th>ชื่อ</th><th>Email</th><th>Admin</th><th>เบอร์โทร</th><th>วันที่สร้าง</th><th>จัดการ</th></tr></thead>
        <tbody>
        <?php if(mysqli_num_rows($rows)===0): ?>
          <tr><td colspan="7" style="text-align:center;color:var(--muted);padding:30px">ยังไม่มีข้อมูล</td></tr>
        <?php else: while($row=mysqli_fetch_assoc($rows)): ?>
          <tr>
            <td><code style="color:var(--accent);font-size:0.8rem"><?=htmlspecialchars($row['MG_id'])?></code></td>
            <td><?=htmlspecialchars($row['MG_name']??'-')?></td>
            <td><?=htmlspecialchars($row['MG_email'])?></td>
            <td><span class="badge badge-info"><?=htmlspecialchars($row['AD_name']??$row['AD_id'])?></span></td>
            <td><?=htmlspecialchars($row['MG_phone'])?></td>
            <td style="color:var(--muted);font-size:0.8rem"><?=htmlspecialchars($row['created_at'])?></td>
            <td>
              <button class="btn btn-edit btn-sm" onclick='openEditModal(<?=json_encode($row)?>)'>✏️</button>
              <form id="del-<?=htmlspecialchars($row['MG_id'])?>" method="POST" style="display:inline">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="MG_id" value="<?=htmlspecialchars($row['MG_id'])?>">
              </form>
              <button class="btn btn-danger btn-sm" onclick="confirmDelete('del-<?=htmlspecialchars($row['MG_id'])?>')">🗑️</button>
            </td>
          </tr>
        <?php endwhile; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Add Modal -->
<div class="modal-overlay" id="addModal">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-title">🧑‍💼 เพิ่ม Manager ใหม่</div>
      <button class="modal-close" onclick="closeModal('addModal')">✕</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="add">
      <div class="form-grid">
        <div class="form-group"><label>Manager ID</label><input name="MG_id" maxlength="10" required placeholder="MG0000001"></div>
        <div class="form-group">
          <label>Admin (ผู้ดูแล)</label>
          <select name="AD_id" required>
            <option value="">-- เลือก Admin --</option>
            <?php mysqli_data_seek($admins,0); while($a=mysqli_fetch_assoc($admins)): ?>
            <option value="<?=htmlspecialchars($a['AD_id'])?>"><?=htmlspecialchars($a['AD_name'])?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="form-group"><label>ชื่อ Manager</label><input name="MG_name" required placeholder="ชื่อ-นามสกุล"></div>
        <div class="form-group"><label>Email</label><input name="MG_email" type="email" required placeholder="manager@example.com"></div>
        <div class="form-group"><label>ที่อยู่</label><input name="MG_address" required placeholder="ที่อยู่"></div>
        <div class="form-group"><label>เบอร์โทร</label><input name="MG_phone" maxlength="10" required placeholder="0812345678"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn" style="background:var(--surface2);color:var(--text)" onclick="closeModal('addModal')">ยกเลิก</button>
        <button type="submit" class="btn btn-primary">บันทึก</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal-overlay" id="editModal">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-title">✏️ แก้ไข Manager</div>
      <button class="modal-close" onclick="closeModal('editModal')">✕</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="edit">
      <input type="hidden" name="MG_id" id="edit_MG_id">
      <div class="form-grid">
        <div class="form-group"><label>Manager ID</label><input id="e_MG_id_show" disabled style="opacity:0.5"></div>
        <div class="form-group">
          <label>Admin</label>
          <select name="AD_id" id="edit_AD_id" required>
            <?php mysqli_data_seek($admins,0); while($a=mysqli_fetch_assoc($admins)): ?>
            <option value="<?=htmlspecialchars($a['AD_id'])?>"><?=htmlspecialchars($a['AD_name'])?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="form-group"><label>ชื่อ Manager</label><input name="MG_name" id="edit_MG_name" required></div>
        <div class="form-group"><label>Email</label><input name="MG_email" id="edit_MG_email" type="email" required></div>
        <div class="form-group"><label>ที่อยู่</label><input name="MG_address" id="edit_MG_address" required></div>
        <div class="form-group"><label>เบอร์โทร</label><input name="MG_phone" id="edit_MG_phone" maxlength="10" required></div>
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
  document.getElementById('edit_MG_id').value = row.MG_id;
  document.getElementById('e_MG_id_show').value = row.MG_id;
  document.getElementById('edit_AD_id').value = row.AD_id;
  document.getElementById('edit_MG_name').value = row.MG_name||'';
  document.getElementById('edit_MG_email').value = row.MG_email;
  document.getElementById('edit_MG_address').value = row.MG_address;
  document.getElementById('edit_MG_phone').value = row.MG_phone;
  openModal('editModal');
}
</script>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>