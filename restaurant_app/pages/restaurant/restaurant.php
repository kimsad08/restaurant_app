<?php
$pageTitle = "Restaurant – RestoSys";
$activePage = "restaurant";
require_once __DIR__ . '/../../config/paths.php';
require_once CONFIG_PATH . '/db.php';

$msg = '';
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='add') {
    $id   = mysqli_real_escape_string($conn, trim($_POST['RS_id']));
    $mg   = mysqli_real_escape_string($conn, trim($_POST['MG_id']));
    $name = mysqli_real_escape_string($conn, trim($_POST['RS_name']));
    $desc = mysqli_real_escape_string($conn, trim($_POST['RS_description']));
    $ph   = mysqli_real_escape_string($conn, trim($_POST['RS_phone']));
    if(mysqli_query($conn,"INSERT INTO Restaurant (RS_id,MG_id,RS_name,RS_description,RS_phone) VALUES ('$id','$mg','$name','$desc','$ph')"))
        $msg=['type'=>'success','text'=>'✅ เพิ่มร้านอาหารสำเร็จ'];
    else $msg=['type'=>'danger','text'=>'❌ '.mysqli_error($conn)];
}
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='edit') {
    $id   = mysqli_real_escape_string($conn, trim($_POST['RS_id']));
    $mg   = mysqli_real_escape_string($conn, trim($_POST['MG_id']));
    $name = mysqli_real_escape_string($conn, trim($_POST['RS_name']));
    $desc = mysqli_real_escape_string($conn, trim($_POST['RS_description']));
    $ph   = mysqli_real_escape_string($conn, trim($_POST['RS_phone']));
    if(mysqli_query($conn,"UPDATE Restaurant SET MG_id='$mg',RS_name='$name',RS_description='$desc',RS_phone='$ph' WHERE RS_id='$id'"))
        $msg=['type'=>'success','text'=>'✅ แก้ไขสำเร็จ'];
    else $msg=['type'=>'danger','text'=>'❌ '.mysqli_error($conn)];
}
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='delete') {
    $id=mysqli_real_escape_string($conn,$_POST['RS_id']);
    if(mysqli_query($conn,"DELETE FROM Restaurant WHERE RS_id='$id'")) $msg=['type'=>'success','text'=>'✅ ลบสำเร็จ'];
    else $msg=['type'=>'danger','text'=>'❌ '.mysqli_error($conn)];
}

$rows = mysqli_query($conn,"SELECT r.*, m.MG_name FROM Restaurant r LEFT JOIN Manager m ON r.MG_id=m.MG_id ORDER BY r.created_at DESC");
$mgrs = mysqli_query($conn,"SELECT MG_id, MG_name FROM Manager ORDER BY MG_name");
require_once INCLUDES_PATH . '/header.php';
?>

<div class="main">
  <div class="page-header">
    <h2>🏪 จัดการร้านอาหาร</h2>
    <p>เพิ่ม แก้ไข และลบข้อมูลร้านอาหาร</p>
  </div>

  <?php if($msg): ?>
  <div class="alert alert-<?=$msg['type']?>"><?=$msg['text']?></div>
  <?php endif; ?>

  <div class="card">
    <div class="card-title" style="justify-content:space-between">
      <span>รายชื่อร้านอาหาร</span>
      <button class="btn btn-primary" onclick="openModal('addModal')">+ เพิ่มร้านอาหาร</button>
    </div>
    <div class="table-wrap">
      <table>
        <thead><tr><th>ID</th><th>ชื่อร้าน</th><th>คำอธิบาย</th><th>Manager</th><th>เบอร์โทร</th><th>จัดการ</th></tr></thead>
        <tbody>
        <?php if(mysqli_num_rows($rows)===0): ?>
          <tr><td colspan="6" style="text-align:center;color:var(--muted);padding:30px">ยังไม่มีข้อมูล</td></tr>
        <?php else: while($row=mysqli_fetch_assoc($rows)): ?>
          <tr>
            <td><code style="color:var(--accent);font-size:0.8rem"><?=htmlspecialchars($row['RS_id'])?></code></td>
            <td><strong><?=htmlspecialchars($row['RS_name'])?></strong></td>
            <td style="color:var(--muted);font-size:0.85rem"><?=htmlspecialchars($row['RS_description'])?></td>
            <td><span class="badge badge-info"><?=htmlspecialchars($row['MG_name']??$row['MG_id'])?></span></td>
            <td><?=htmlspecialchars($row['RS_phone'])?></td>
            <td>
              <button class="btn btn-edit btn-sm" onclick='openEditModal(<?=json_encode($row)?>)'>✏️</button>
              <form id="del-<?=htmlspecialchars($row['RS_id'])?>" method="POST" style="display:inline">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="RS_id" value="<?=htmlspecialchars($row['RS_id'])?>">
              </form>
              <button class="btn btn-danger btn-sm" onclick="confirmDelete('del-<?=htmlspecialchars($row['RS_id'])?>')">🗑️</button>
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
    <div class="modal-header"><div class="modal-title">🏪 เพิ่มร้านอาหาร</div><button class="modal-close" onclick="closeModal('addModal')">✕</button></div>
    <form method="POST">
      <input type="hidden" name="action" value="add">
      <div class="form-grid">
        <div class="form-group"><label>Restaurant ID</label><input name="RS_id" maxlength="10" required placeholder="RS0000001"></div>
        <div class="form-group"><label>Manager</label>
          <select name="MG_id" required>
            <option value="">-- เลือก Manager --</option>
            <?php mysqli_data_seek($mgrs,0); while($m=mysqli_fetch_assoc($mgrs)): ?>
            <option value="<?=htmlspecialchars($m['MG_id'])?>"><?=htmlspecialchars($m['MG_name']??$m['MG_id'])?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="form-group" style="grid-column:1/-1"><label>ชื่อร้าน</label><input name="RS_name" required placeholder="ชื่อร้านอาหาร"></div>
        <div class="form-group" style="grid-column:1/-1"><label>คำอธิบาย</label><input name="RS_description" required placeholder="รายละเอียดร้าน"></div>
        <div class="form-group"><label>เบอร์โทร</label><input name="RS_phone" maxlength="10" required placeholder="0812345678"></div>
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
    <div class="modal-header"><div class="modal-title">✏️ แก้ไขร้านอาหาร</div><button class="modal-close" onclick="closeModal('editModal')">✕</button></div>
    <form method="POST">
      <input type="hidden" name="action" value="edit">
      <input type="hidden" name="RS_id" id="edit_RS_id">
      <div class="form-grid">
        <div class="form-group"><label>Restaurant ID</label><input id="e_RS_id_show" disabled style="opacity:0.5"></div>
        <div class="form-group"><label>Manager</label>
          <select name="MG_id" id="edit_MG_id" required>
            <?php mysqli_data_seek($mgrs,0); while($m=mysqli_fetch_assoc($mgrs)): ?>
            <option value="<?=htmlspecialchars($m['MG_id'])?>"><?=htmlspecialchars($m['MG_name']??$m['MG_id'])?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="form-group" style="grid-column:1/-1"><label>ชื่อร้าน</label><input name="RS_name" id="edit_RS_name" required></div>
        <div class="form-group" style="grid-column:1/-1"><label>คำอธิบาย</label><input name="RS_description" id="edit_RS_desc" required></div>
        <div class="form-group"><label>เบอร์โทร</label><input name="RS_phone" id="edit_RS_phone" maxlength="10" required></div>
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
  document.getElementById('edit_RS_id').value = row.RS_id;
  document.getElementById('e_RS_id_show').value = row.RS_id;
  document.getElementById('edit_MG_id').value = row.MG_id;
  document.getElementById('edit_RS_name').value = row.RS_name;
  document.getElementById('edit_RS_desc').value = row.RS_description;
  document.getElementById('edit_RS_phone').value = row.RS_phone;
  openModal('editModal');
}
</script>
<?php require_once INCLUDES_PATH . '/footer.php'; ?>