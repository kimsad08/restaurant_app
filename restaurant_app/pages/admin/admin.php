<?php
$pageTitle = "Admin – RestoSys";
$activePage = "admin";
require_once __DIR__ . '/../../config/paths.php';
require_once CONFIG_PATH . '/db.php';

$msg = '';

// ---- ADD ----
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='add') {
    $id    = mysqli_real_escape_string($conn, trim($_POST['AD_id']));
    $name  = mysqli_real_escape_string($conn, trim($_POST['AD_name']));
    $email = mysqli_real_escape_string($conn, trim($_POST['AD_email']));
    $addr  = mysqli_real_escape_string($conn, trim($_POST['AD_address']));
    $phone = mysqli_real_escape_string($conn, trim($_POST['AD_phone']));
    $sql = "INSERT INTO Admin (AD_id,AD_name,AD_email,AD_address,AD_phone) VALUES ('$id','$name','$email','$addr','$phone')";
    if (mysqli_query($conn,$sql)) $msg = ['type'=>'success','text'=>'✅ เพิ่ม Admin สำเร็จ'];
    else $msg = ['type'=>'danger','text'=>'❌ '.mysqli_error($conn)];
}

// ---- EDIT ----
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='edit') {
    $id    = mysqli_real_escape_string($conn, trim($_POST['AD_id']));
    $name  = mysqli_real_escape_string($conn, trim($_POST['AD_name']));
    $email = mysqli_real_escape_string($conn, trim($_POST['AD_email']));
    $addr  = mysqli_real_escape_string($conn, trim($_POST['AD_address']));
    $phone = mysqli_real_escape_string($conn, trim($_POST['AD_phone']));
    $sql = "UPDATE Admin SET AD_name='$name',AD_email='$email',AD_address='$addr',AD_phone='$phone' WHERE AD_id='$id'";
    if (mysqli_query($conn,$sql)) $msg = ['type'=>'success','text'=>'✅ แก้ไข Admin สำเร็จ'];
    else $msg = ['type'=>'danger','text'=>'❌ '.mysqli_error($conn)];
}

// ---- DELETE ----
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='delete') {
    $id = mysqli_real_escape_string($conn, $_POST['AD_id']);
    if (mysqli_query($conn,"DELETE FROM Admin WHERE AD_id='$id'"))
        $msg = ['type'=>'success','text'=>'✅ ลบ Admin สำเร็จ'];
    else $msg = ['type'=>'danger','text'=>'❌ '.mysqli_error($conn)];
}

$rows = mysqli_query($conn,"SELECT * FROM Admin ORDER BY created_at DESC");
require_once INCLUDES_PATH . '/header.php';
?>

<div class="main">
  <div class="page-header">
    <h2>👑 จัดการ Admin</h2>
    <p>เพิ่ม แก้ไข และลบผู้ดูแลระบบ</p>
  </div>

  <?php if($msg): ?>
  <div class="alert alert-<?=$msg['type']?>"><?=$msg['text']?></div>
  <?php endif; ?>

  <div class="card">
    <div class="card-title" style="justify-content:space-between">
      <span>รายชื่อ Admin</span>
      <button class="btn btn-primary" onclick="openModal('addModal')">+ เพิ่ม Admin</button>
    </div>
    <div class="table-wrap">
      <table>
        <thead><tr><th>ID</th><th>ชื่อ</th><th>Email</th><th>ที่อยู่</th><th>เบอร์โทร</th><th>วันที่สร้าง</th><th>จัดการ</th></tr></thead>
        <tbody>
        <?php if(mysqli_num_rows($rows)===0): ?>
          <tr><td colspan="7" style="text-align:center;color:var(--muted);padding:30px">ยังไม่มีข้อมูล</td></tr>
        <?php else: while($row=mysqli_fetch_assoc($rows)): ?>
          <tr>
            <td><code style="color:var(--accent);font-size:0.8rem"><?=htmlspecialchars($row['AD_id'])?></code></td>
            <td><?=htmlspecialchars($row['AD_name'])?></td>
            <td><?=htmlspecialchars($row['AD_email'])?></td>
            <td><?=htmlspecialchars($row['AD_address'])?></td>
            <td><?=htmlspecialchars($row['AD_phone'])?></td>
            <td style="color:var(--muted);font-size:0.8rem"><?=htmlspecialchars($row['created_at'])?></td>
            <td>
              <button class="btn btn-edit btn-sm" onclick='openEditModal(<?=json_encode($row)?>)'>✏️ แก้ไข</button>
              <form id="del-<?=htmlspecialchars($row['AD_id'])?>" method="POST" style="display:inline">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="AD_id" value="<?=htmlspecialchars($row['AD_id'])?>">
              </form>
              <button class="btn btn-danger btn-sm" onclick="confirmDelete('del-<?=htmlspecialchars($row['AD_id'])?>')">🗑️ ลบ</button>
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
      <div class="modal-title">👑 เพิ่ม Admin ใหม่</div>
      <button class="modal-close" onclick="closeModal('addModal')">✕</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="add">
      <div class="form-grid">
        <div class="form-group"><label>Admin ID (10 ตัวอักษร)</label><input name="AD_id" maxlength="10" required placeholder="AD0000001"></div>
        <div class="form-group"><label>ชื่อ Admin</label><input name="AD_name" required placeholder="ชื่อ-นามสกุล"></div>
        <div class="form-group"><label>Email</label><input name="AD_email" type="email" required placeholder="admin@example.com"></div>
        <div class="form-group"><label>ที่อยู่</label><input name="AD_address" required placeholder="บ้านเลขที่ ถนน จังหวัด"></div>
        <div class="form-group"><label>เบอร์โทร</label><input name="AD_phone" maxlength="10" required placeholder="0812345678"></div>
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
      <div class="modal-title">✏️ แก้ไข Admin</div>
      <button class="modal-close" onclick="closeModal('editModal')">✕</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="edit">
      <input type="hidden" name="AD_id" id="edit_AD_id">
      <div class="form-grid">
        <div class="form-group"><label>Admin ID</label><input id="edit_AD_id_show" disabled style="opacity:0.5"></div>
        <div class="form-group"><label>ชื่อ Admin</label><input name="AD_name" id="edit_AD_name" required></div>
        <div class="form-group"><label>Email</label><input name="AD_email" id="edit_AD_email" type="email" required></div>
        <div class="form-group"><label>ที่อยู่</label><input name="AD_address" id="edit_AD_address" required></div>
        <div class="form-group"><label>เบอร์โทร</label><input name="AD_phone" id="edit_AD_phone" maxlength="10" required></div>
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
  document.getElementById('edit_AD_id').value = row.AD_id;
  document.getElementById('edit_AD_id_show').value = row.AD_id;
  document.getElementById('edit_AD_name').value = row.AD_name;
  document.getElementById('edit_AD_email').value = row.AD_email;
  document.getElementById('edit_AD_address').value = row.AD_address;
  document.getElementById('edit_AD_phone').value = row.AD_phone;
  openModal('editModal');
}
</script>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>