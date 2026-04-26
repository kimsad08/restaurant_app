<?php
$pageTitle = "Nutrition – RestoSys";
$activePage = "nutrition";
require_once __DIR__ . '/../../config/paths.php';
require_once CONFIG_PATH . '/db.php';

$msg='';
if($_SERVER['REQUEST_METHOD']==='POST'&&($_POST['action']??'')==='add'){
    $id  =mysqli_real_escape_string($conn,trim($_POST['NT_id']));
    $mn  =mysqli_real_escape_string($conn,trim($_POST['MN_id']));
    $cal =mysqli_real_escape_string($conn,trim($_POST['NT_calorie']));
    $pro =mysqli_real_escape_string($conn,trim($_POST['NT_protein']));
    $carb=mysqli_real_escape_string($conn,trim($_POST['NT_carb']));
    $fat =mysqli_real_escape_string($conn,trim($_POST['NT_fat']));
    if(mysqli_query($conn,"INSERT INTO Nutrition(NT_id,MN_id,NT_calorie,NT_protein,NT_carb,NT_fat)VALUES('$id','$mn','$cal','$pro','$carb','$fat')"))
        $msg=['type'=>'success','text'=>'✅ เพิ่มข้อมูลโภชนาการสำเร็จ'];
    else $msg=['type'=>'danger','text'=>'❌ '.mysqli_error($conn)];
}
if($_SERVER['REQUEST_METHOD']==='POST'&&($_POST['action']??'')==='edit'){
    $id  =mysqli_real_escape_string($conn,trim($_POST['NT_id']));
    $mn  =mysqli_real_escape_string($conn,trim($_POST['MN_id']));
    $cal =mysqli_real_escape_string($conn,trim($_POST['NT_calorie']));
    $pro =mysqli_real_escape_string($conn,trim($_POST['NT_protein']));
    $carb=mysqli_real_escape_string($conn,trim($_POST['NT_carb']));
    $fat =mysqli_real_escape_string($conn,trim($_POST['NT_fat']));
    if(mysqli_query($conn,"UPDATE Nutrition SET MN_id='$mn',NT_calorie='$cal',NT_protein='$pro',NT_carb='$carb',NT_fat='$fat' WHERE NT_id='$id'"))
        $msg=['type'=>'success','text'=>'✅ แก้ไขสำเร็จ'];
    else $msg=['type'=>'danger','text'=>'❌ '.mysqli_error($conn)];
}
if($_SERVER['REQUEST_METHOD']==='POST'&&($_POST['action']??'')==='delete'){
    $id=mysqli_real_escape_string($conn,$_POST['NT_id']);
    if(mysqli_query($conn,"DELETE FROM Nutrition WHERE NT_id='$id'")) $msg=['type'=>'success','text'=>'✅ ลบสำเร็จ'];
    else $msg=['type'=>'danger','text'=>'❌ '.mysqli_error($conn)];
}

$rows = mysqli_query($conn,"SELECT n.*, m.MN_name FROM Nutrition n LEFT JOIN Menu m ON n.MN_id=m.MN_id ORDER BY n.NT_id");
$menus= mysqli_query($conn,"SELECT MN_id, MN_name FROM Menu WHERE MN_id NOT IN (SELECT MN_id FROM Nutrition) ORDER BY MN_name");
require_once INCLUDES_PATH . '/header.php';
?>

<div class="main">
  <div class="page-header">
    <h2>🥗 ข้อมูลโภชนาการ</h2>
    <p>จัดการข้อมูลแคลอรี่และสารอาหารของแต่ละเมนู</p>
  </div>

  <?php if($msg): ?>
  <div class="alert alert-<?=$msg['type']?>"><?=$msg['text']?></div>
  <?php endif; ?>

  <div class="card">
    <div class="card-title" style="justify-content:space-between">
      <span>รายการโภชนาการ</span>
      <button class="btn btn-primary" onclick="openModal('addModal')">+ เพิ่มโภชนาการ</button>
    </div>
    <div class="table-wrap">
      <table>
        <thead><tr><th>ID</th><th>เมนู</th><th>แคลอรี่ (kcal)</th><th>โปรตีน (g)</th><th>คาร์โบ (g)</th><th>ไขมัน (g)</th><th>จัดการ</th></tr></thead>
        <tbody>
        <?php if(mysqli_num_rows($rows)===0): ?>
          <tr><td colspan="7" style="text-align:center;color:var(--muted);padding:30px">ยังไม่มีข้อมูล</td></tr>
        <?php else: while($row=mysqli_fetch_assoc($rows)): ?>
          <tr>
            <td><code style="color:var(--accent);font-size:0.8rem"><?=htmlspecialchars($row['NT_id'])?></code></td>
            <td><strong><?=htmlspecialchars($row['MN_name']??$row['MN_id'])?></strong></td>
            <td><span class="badge badge-success"><?=number_format($row['NT_calorie'])?></span></td>
            <td><?=number_format($row['NT_protein'],1)?></td>
            <td><?=number_format($row['NT_carb'],1)?></td>
            <td><?=number_format($row['NT_fat'],1)?></td>
            <td>
              <button class="btn btn-edit btn-sm" onclick='openEditModal(<?=json_encode($row)?>)'>✏️</button>
              <form id="del-<?=htmlspecialchars($row['NT_id'])?>" method="POST" style="display:inline">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="NT_id" value="<?=htmlspecialchars($row['NT_id'])?>">
              </form>
              <button class="btn btn-danger btn-sm" onclick="confirmDelete('del-<?=htmlspecialchars($row['NT_id'])?>')">🗑️</button>
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
    <div class="modal-header"><div class="modal-title">🥗 เพิ่มโภชนาการ</div><button class="modal-close" onclick="closeModal('addModal')">✕</button></div>
    <form method="POST">
      <input type="hidden" name="action" value="add">
      <div class="form-grid">
        <div class="form-group"><label>Nutrition ID</label><input name="NT_id" maxlength="10" required placeholder="NT0000001"></div>
        <div class="form-group"><label>เมนูอาหาร</label>
          <select name="MN_id" required>
            <option value="">-- เลือกเมนู (ที่ยังไม่มีข้อมูล) --</option>
            <?php mysqli_data_seek($menus,0); while($m=mysqli_fetch_assoc($menus)): ?>
            <option value="<?=htmlspecialchars($m['MN_id'])?>"><?=htmlspecialchars($m['MN_name'])?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="form-group"><label>แคลอรี่ (kcal)</label><input name="NT_calorie" type="number" required placeholder="0"></div>
        <div class="form-group"><label>โปรตีน (g)</label><input name="NT_protein" type="number" step="0.01" value="0" placeholder="0.00"></div>
        <div class="form-group"><label>คาร์โบไฮเดรต (g)</label><input name="NT_carb" type="number" step="0.01" value="0" placeholder="0.00"></div>
        <div class="form-group"><label>ไขมัน (g)</label><input name="NT_fat" type="number" step="0.01" value="0" placeholder="0.00"></div>
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
    <div class="modal-header"><div class="modal-title">✏️ แก้ไขโภชนาการ</div><button class="modal-close" onclick="closeModal('editModal')">✕</button></div>
    <form method="POST">
      <input type="hidden" name="action" value="edit">
      <input type="hidden" name="NT_id" id="edit_NT_id">
      <input type="hidden" name="MN_id" id="edit_MN_id_h">
      <div class="form-grid">
        <div class="form-group"><label>เมนู</label><input id="e_MN_name" disabled style="opacity:0.5"></div>
        <div class="form-group"><label>แคลอรี่ (kcal)</label><input name="NT_calorie" id="edit_NT_calorie" type="number" required></div>
        <div class="form-group"><label>โปรตีน (g)</label><input name="NT_protein" id="edit_NT_protein" type="number" step="0.01"></div>
        <div class="form-group"><label>คาร์โบไฮเดรต (g)</label><input name="NT_carb" id="edit_NT_carb" type="number" step="0.01"></div>
        <div class="form-group"><label>ไขมัน (g)</label><input name="NT_fat" id="edit_NT_fat" type="number" step="0.01"></div>
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
  document.getElementById('edit_NT_id').value = row.NT_id;
  document.getElementById('edit_MN_id_h').value = row.MN_id;
  document.getElementById('e_MN_name').value = row.MN_name||row.MN_id;
  document.getElementById('edit_NT_calorie').value = row.NT_calorie;
  document.getElementById('edit_NT_protein').value = row.NT_protein;
  document.getElementById('edit_NT_carb').value = row.NT_carb;
  document.getElementById('edit_NT_fat').value = row.NT_fat;
  openModal('editModal');
}
</script>
<?php require_once INCLUDES_PATH . '/footer.php'; ?>