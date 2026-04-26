<?php
$pageTitle = "Menu – RestoSys";
$activePage = "menu";
require_once __DIR__ . '/../../config/paths.php';
require_once CONFIG_PATH . '/db.php';

$msg='';
if($_SERVER['REQUEST_METHOD']==='POST'&&($_POST['action']??'')==='add'){
    $id  =mysqli_real_escape_string($conn,trim($_POST['MN_id']));
    $rs  =mysqli_real_escape_string($conn,trim($_POST['RS_id']));
    $name=mysqli_real_escape_string($conn,trim($_POST['MN_name']));
    $pr  =mysqli_real_escape_string($conn,trim($_POST['MN_price']));
    $st  =mysqli_real_escape_string($conn,trim($_POST['MN_status']));
    if(mysqli_query($conn,"INSERT INTO Menu(MN_id,RS_id,MN_name,MN_price,MN_status)VALUES('$id','$rs','$name','$pr','$st')"))
        $msg=['type'=>'success','text'=>'✅ เพิ่มเมนูสำเร็จ'];
    else $msg=['type'=>'danger','text'=>'❌ '.mysqli_error($conn)];
}
if($_SERVER['REQUEST_METHOD']==='POST'&&($_POST['action']??'')==='edit'){
    $id  =mysqli_real_escape_string($conn,trim($_POST['MN_id']));
    $rs  =mysqli_real_escape_string($conn,trim($_POST['RS_id']));
    $name=mysqli_real_escape_string($conn,trim($_POST['MN_name']));
    $pr  =mysqli_real_escape_string($conn,trim($_POST['MN_price']));
    $st  =mysqli_real_escape_string($conn,trim($_POST['MN_status']));
    if(mysqli_query($conn,"UPDATE Menu SET RS_id='$rs',MN_name='$name',MN_price='$pr',MN_status='$st' WHERE MN_id='$id'"))
        $msg=['type'=>'success','text'=>'✅ แก้ไขสำเร็จ'];
    else $msg=['type'=>'danger','text'=>'❌ '.mysqli_error($conn)];
}
if($_SERVER['REQUEST_METHOD']==='POST'&&($_POST['action']??'')==='delete'){
    $id=mysqli_real_escape_string($conn,$_POST['MN_id']);
    if(mysqli_query($conn,"DELETE FROM Menu WHERE MN_id='$id'")) $msg=['type'=>'success','text'=>'✅ ลบสำเร็จ'];
    else $msg=['type'=>'danger','text'=>'❌ '.mysqli_error($conn)];
}

$rows = mysqli_query($conn,"SELECT m.*, r.RS_name FROM Menu m LEFT JOIN Restaurant r ON m.RS_id=r.RS_id ORDER BY m.created_at DESC");
$rests= mysqli_query($conn,"SELECT RS_id, RS_name FROM Restaurant ORDER BY RS_name");
require_once INCLUDES_PATH . '/header.php';
?>

<div class="main">
  <div class="page-header">
    <h2>🍱 จัดการเมนูอาหาร</h2>
    <p>เพิ่ม แก้ไข และลบเมนูอาหาร</p>
  </div>

  <?php if($msg): ?>
  <div class="alert alert-<?=$msg['type']?>"><?=$msg['text']?></div>
  <?php endif; ?>

  <div class="card">
    <div class="card-title" style="justify-content:space-between">
      <span>รายการเมนูทั้งหมด</span>
      <button class="btn btn-primary" onclick="openModal('addModal')">+ เพิ่มเมนู</button>
    </div>
    <div class="table-wrap">
      <table>
        <thead><tr><th>ID</th><th>ชื่อเมนู</th><th>ร้านอาหาร</th><th>ราคา</th><th>สถานะ</th><th>จัดการ</th></tr></thead>
        <tbody>
        <?php if(mysqli_num_rows($rows)===0): ?>
          <tr><td colspan="6" style="text-align:center;color:var(--muted);padding:30px">ยังไม่มีข้อมูล</td></tr>
        <?php else: while($row=mysqli_fetch_assoc($rows)): ?>
          <tr>
            <td><code style="color:var(--accent);font-size:0.8rem"><?=htmlspecialchars($row['MN_id'])?></code></td>
            <td><strong><?=htmlspecialchars($row['MN_name'])?></strong></td>
            <td><?=htmlspecialchars($row['RS_name']??$row['RS_id'])?></td>
            <td>฿<?=number_format($row['MN_price'],2)?></td>
            <td>
              <?php $s=$row['MN_status']; ?>
              <span class="badge <?=$s==='available'?'badge-success':($s==='unavailable'?'badge-danger':'badge-info')?>">
                <?=htmlspecialchars($s)?>
              </span>
            </td>
            <td>
              <button class="btn btn-edit btn-sm" onclick='openEditModal(<?=json_encode($row)?>)'>✏️</button>
              <form id="del-<?=htmlspecialchars($row['MN_id'])?>" method="POST" style="display:inline">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="MN_id" value="<?=htmlspecialchars($row['MN_id'])?>">
              </form>
              <button class="btn btn-danger btn-sm" onclick="confirmDelete('del-<?=htmlspecialchars($row['MN_id'])?>')">🗑️</button>
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
    <div class="modal-header"><div class="modal-title">🍱 เพิ่มเมนูใหม่</div><button class="modal-close" onclick="closeModal('addModal')">✕</button></div>
    <form method="POST">
      <input type="hidden" name="action" value="add">
      <div class="form-grid">
        <div class="form-group"><label>Menu ID</label><input name="MN_id" maxlength="10" required placeholder="MN0000001"></div>
        <div class="form-group"><label>ร้านอาหาร</label>
          <select name="RS_id" required>
            <option value="">-- เลือกร้าน --</option>
            <?php mysqli_data_seek($rests,0); while($r=mysqli_fetch_assoc($rests)): ?>
            <option value="<?=htmlspecialchars($r['RS_id'])?>"><?=htmlspecialchars($r['RS_name'])?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="form-group" style="grid-column:1/-1"><label>ชื่อเมนู</label><input name="MN_name" required placeholder="ชื่ออาหาร"></div>
        <div class="form-group"><label>ราคา (บาท)</label><input name="MN_price" type="number" step="0.01" min="0" required placeholder="0.00"></div>
        <div class="form-group"><label>สถานะ</label>
          <select name="MN_status" required>
            <option value="available">available</option>
            <option value="unavailable">unavailable</option>
            <option value="seasonal">seasonal</option>
          </select>
        </div>
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
    <div class="modal-header"><div class="modal-title">✏️ แก้ไขเมนู</div><button class="modal-close" onclick="closeModal('editModal')">✕</button></div>
    <form method="POST">
      <input type="hidden" name="action" value="edit">
      <input type="hidden" name="MN_id" id="edit_MN_id">
      <div class="form-grid">
        <div class="form-group"><label>Menu ID</label><input id="e_MN_id_s" disabled style="opacity:0.5"></div>
        <div class="form-group"><label>ร้านอาหาร</label>
          <select name="RS_id" id="edit_RS_id" required>
            <?php mysqli_data_seek($rests,0); while($r=mysqli_fetch_assoc($rests)): ?>
            <option value="<?=htmlspecialchars($r['RS_id'])?>"><?=htmlspecialchars($r['RS_name'])?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="form-group" style="grid-column:1/-1"><label>ชื่อเมนู</label><input name="MN_name" id="edit_MN_name" required></div>
        <div class="form-group"><label>ราคา</label><input name="MN_price" id="edit_MN_price" type="number" step="0.01" min="0" required></div>
        <div class="form-group"><label>สถานะ</label>
          <select name="MN_status" id="edit_MN_status" required>
            <option value="available">available</option>
            <option value="unavailable">unavailable</option>
            <option value="seasonal">seasonal</option>
          </select>
        </div>
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
  document.getElementById('edit_MN_id').value = row.MN_id;
  document.getElementById('e_MN_id_s').value = row.MN_id;
  document.getElementById('edit_RS_id').value = row.RS_id;
  document.getElementById('edit_MN_name').value = row.MN_name;
  document.getElementById('edit_MN_price').value = row.MN_price;
  document.getElementById('edit_MN_status').value = row.MN_status;
  openModal('editModal');
}
</script>
<?php require_once INCLUDES_PATH . '/footer.php'; ?>