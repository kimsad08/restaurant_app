<?php
session_start();
require_once __DIR__ . '/../../config/paths.php';
require_once INCLUDES_PATH . '/auth.php';
requireLogin('manager');
require_once CONFIG_PATH . '/db.php';

$mgid = $_SESSION['user_id'];
$e    = mysqli_real_escape_string($conn, $mgid);
$msg  = '';

// ดึง RS_id ของ manager นี้
$mgr = mysqli_fetch_assoc(mysqli_query($conn,"SELECT m.*,r.RS_id,r.RS_name FROM Manager m LEFT JOIN Restaurant r ON m.MG_id=r.MG_id WHERE m.MG_id='$e' LIMIT 1"));
$rs_id = $mgr['RS_id'] ?? '';
$rs_e  = mysqli_real_escape_string($conn, $rs_id);

// ---- CRUD Menu ----
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add_menu') {
        $mn_id  = 'MN'.date('ymdHis').rand(10,99);
        $name   = mysqli_real_escape_string($conn, trim($_POST['MN_name']));
        $price  = floatval($_POST['MN_price']);
        $st     = mysqli_real_escape_string($conn, $_POST['MN_status']);
        $cal    = (int)$_POST['NT_calorie'];
        $pro    = floatval($_POST['NT_protein']);
        $carb   = floatval($_POST['NT_carb']);
        $fat    = floatval($_POST['NT_fat']);
        if(mysqli_query($conn,"INSERT INTO Menu(MN_id,RS_id,MN_name,MN_price,MN_calorie,MN_status)VALUES('$mn_id','$rs_e','$name','$price','$cal','$st')")) {
            $nt_id = 'NT'.date('ymdHis').rand(10,99);
            mysqli_query($conn,"INSERT INTO Nutrition(NT_id,MN_id,NT_calorie,NT_protein,NT_carb,NT_fat)VALUES('$nt_id','$mn_id','$cal','$pro','$carb','$fat')");
            $msg=['type'=>'success','text'=>'✅ เพิ่มเมนูสำเร็จ'];
        } else $msg=['type'=>'danger','text'=>'❌ '.mysqli_error($conn)];
    }
    if ($action === 'edit_menu') {
        $mn_id  = mysqli_real_escape_string($conn, $_POST['MN_id']);
        $name   = mysqli_real_escape_string($conn, trim($_POST['MN_name']));
        $price  = floatval($_POST['MN_price']);
        $st     = mysqli_real_escape_string($conn, $_POST['MN_status']);
        $cal    = (int)$_POST['NT_calorie'];
        $pro    = floatval($_POST['NT_protein']);
        $carb   = floatval($_POST['NT_carb']);
        $fat    = floatval($_POST['NT_fat']);
        mysqli_query($conn,"UPDATE Menu SET MN_name='$name',MN_price='$price',MN_calorie='$cal',MN_status='$st' WHERE MN_id='$mn_id'");
        $exists = mysqli_fetch_assoc(mysqli_query($conn,"SELECT NT_id FROM Nutrition WHERE MN_id='$mn_id'"));
        if($exists) {
            mysqli_query($conn,"UPDATE Nutrition SET NT_calorie='$cal',NT_protein='$pro',NT_carb='$carb',NT_fat='$fat' WHERE MN_id='$mn_id'");
        } else {
            $nt_id = 'NT'.date('ymdHis').rand(10,99);
            mysqli_query($conn,"INSERT INTO Nutrition(NT_id,MN_id,NT_calorie,NT_protein,NT_carb,NT_fat)VALUES('$nt_id','$mn_id','$cal','$pro','$carb','$fat')");
        }
        $msg=['type'=>'success','text'=>'✅ แก้ไขเมนูสำเร็จ'];
    }
    if ($action === 'delete_menu') {
        $mn_id = mysqli_real_escape_string($conn, $_POST['MN_id']);
        mysqli_query($conn,"DELETE FROM Nutrition WHERE MN_id='$mn_id'");
        mysqli_query($conn,"DELETE FROM Menu WHERE MN_id='$mn_id' AND RS_id='$rs_e'");
        $msg=['type'=>'success','text'=>'✅ ลบเมนูสำเร็จ'];
    }
}

// ดึงข้อมูล
$menus   = $rs_id ? mysqli_query($conn,"SELECT mn.*,COALESCE(n.NT_calorie,0) NT_calorie,COALESCE(n.NT_protein,0) NT_protein,COALESCE(n.NT_carb,0) NT_carb,COALESCE(n.NT_fat,0) NT_fat FROM Menu mn LEFT JOIN Nutrition n ON mn.MN_id=n.MN_id WHERE mn.RS_id='$rs_e' ORDER BY mn.created_at DESC") : null;
$orderStats = $rs_id ? mysqli_query($conn,"SELECT m.MN_name,SUM(od.ORT_quantity) qty,SUM(od.ORT_quantity*od.ORT_priceperunit) rev FROM OrderDetail od JOIN Menu m ON od.MN_id=m.MN_id WHERE m.RS_id='$rs_e' GROUP BY od.MN_id ORDER BY qty DESC LIMIT 8") : null;
$totalRevenue = $rs_id ? mysqli_fetch_assoc(mysqli_query($conn,"SELECT COALESCE(SUM(od.ORT_quantity*od.ORT_priceperunit),0) t FROM OrderDetail od JOIN Menu m ON od.MN_id=m.MN_id WHERE m.RS_id='$rs_e'"))['t'] : 0;
$totalOrders  = $rs_id ? mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(DISTINCT o.OR_id) c FROM Orders o JOIN OrderDetail od ON o.OR_id=od.OR_id JOIN Menu m ON od.MN_id=m.MN_id WHERE m.RS_id='$rs_e'"))['c'] : 0;

$pageTitle = "Manager Dashboard – RestoSys";
$activePage = "mgdash";
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?=$pageTitle?></title>
<link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&family=Prompt:wght@600;700;800&display=swap" rel="stylesheet">
<style>
:root{--bg:#0f0f13;--surface:#18181f;--surface2:#222230;--border:#2e2e3f;--accent:#f97316;--accent2:#fb923c;--text:#f1f0f5;--muted:#8b8ba0;--success:#22c55e;--danger:#ef4444;--info:#38bdf8;--radius:10px}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Sarabun',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;display:flex}
.sidebar{width:220px;background:var(--surface);border-right:1px solid var(--border);display:flex;flex-direction:column;position:fixed;height:100vh;z-index:100}
.sidebar-logo{padding:20px 18px;border-bottom:1px solid var(--border)}
.sidebar-logo h1{font-family:'Prompt',sans-serif;font-size:1.05rem;font-weight:800;color:var(--accent)}
.sidebar-logo p{font-size:0.68rem;color:var(--muted);margin-top:2px}
.user-info{padding:14px 16px;border-bottom:1px solid var(--border);background:rgba(56,189,248,0.04)}
.user-info .uname{font-weight:700;font-size:0.86rem}
.user-info .urole{font-size:0.68rem;color:var(--info)}
.sidebar-nav{flex:1;padding:14px 10px}
.nav-label{font-size:0.6rem;text-transform:uppercase;letter-spacing:1.5px;color:var(--muted);padding:8px 8px 4px}
.nav-link{display:flex;align-items:center;gap:10px;padding:9px 12px;border-radius:8px;color:var(--muted);text-decoration:none;font-size:0.85rem;font-weight:500;transition:all .15s;margin-bottom:2px}
.nav-link:hover{background:var(--surface2);color:var(--text)}
.nav-link.active{background:rgba(249,115,22,0.15);color:var(--accent)}
.nav-link .icon{font-size:1rem;width:20px;text-align:center}
.sidebar-footer{padding:12px;border-top:1px solid var(--border)}
.btn-logout{display:flex;align-items:center;gap:8px;width:100%;padding:9px 12px;border-radius:8px;background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.2);color:var(--danger);font-size:0.83rem;font-weight:600;cursor:pointer;transition:all .15s;text-decoration:none}
.main{margin-left:220px;flex:1;padding:26px 30px;min-height:100vh}
.page-header{margin-bottom:24px}
.page-header h2{font-family:'Prompt',sans-serif;font-size:1.4rem;font-weight:700}
.page-header p{color:var(--muted);font-size:0.85rem;margin-top:4px}
.card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:22px;margin-bottom:20px}
.card-title{font-family:'Prompt',sans-serif;font-size:0.95rem;font-weight:600;margin-bottom:16px;color:var(--text);display:flex;align-items:center;gap:8px}
.stats-row{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:14px;margin-bottom:20px}
.stat-card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:16px 18px}
.stat-card .label{font-size:0.7rem;color:var(--muted);margin-bottom:7px}
.stat-card .value{font-family:'Prompt',sans-serif;font-size:1.6rem;font-weight:700}
.stat-card .sub{font-size:0.68rem;color:var(--muted);margin-top:3px}
.table-wrap{overflow-x:auto}
table{width:100%;border-collapse:collapse;font-size:0.86rem}
thead th{background:var(--surface2);color:var(--muted);font-size:0.68rem;text-transform:uppercase;letter-spacing:.8px;padding:9px 13px;text-align:left;border-bottom:1px solid var(--border);font-weight:600}
tbody tr{border-bottom:1px solid var(--border);transition:background .1s}
tbody tr:hover{background:var(--surface2)}
tbody td{padding:10px 13px;vertical-align:middle}
tbody tr:last-child{border-bottom:none}
.btn{display:inline-flex;align-items:center;gap:5px;padding:8px 15px;border-radius:7px;border:none;cursor:pointer;font-family:'Sarabun',sans-serif;font-size:0.82rem;font-weight:600;text-decoration:none;transition:all .15s}
.btn-primary{background:var(--accent);color:#fff}.btn-primary:hover{background:var(--accent2)}
.btn-edit{background:rgba(56,189,248,0.12);color:var(--info);border:1px solid rgba(56,189,248,0.25)}.btn-edit:hover{background:rgba(56,189,248,0.22)}
.btn-danger{background:rgba(239,68,68,0.12);color:var(--danger);border:1px solid rgba(239,68,68,0.25)}
.btn-sm{padding:5px 10px;font-size:0.76rem}
.badge{display:inline-block;padding:2px 8px;border-radius:20px;font-size:0.68rem;font-weight:600}
.badge-success{background:rgba(34,197,94,0.15);color:var(--success)}
.badge-danger{background:rgba(239,68,68,0.15);color:var(--danger)}
.badge-info{background:rgba(56,189,248,0.12);color:var(--info)}
.alert{padding:10px 14px;border-radius:8px;font-size:0.84rem;margin-bottom:14px}
.alert-success{background:rgba(34,197,94,0.1);border:1px solid rgba(34,197,94,0.25);color:var(--success)}
.alert-danger{background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.25);color:var(--danger)}
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,0.75);z-index:999;align-items:center;justify-content:center}
.modal-overlay.open{display:flex}
.modal{background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:26px;width:95%;max-width:560px;max-height:92vh;overflow-y:auto}
.modal-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px}
.modal-title{font-family:'Prompt',sans-serif;font-size:1rem;font-weight:700}
.modal-close{background:none;border:none;color:var(--muted);cursor:pointer;font-size:1.2rem;padding:4px}
.modal-footer{display:flex;gap:10px;justify-content:flex-end;margin-top:18px}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.form-group{display:flex;flex-direction:column;gap:5px}
label{font-size:0.76rem;color:var(--muted);font-weight:500}
input,select{background:var(--surface2);border:1px solid var(--border);border-radius:7px;color:var(--text);padding:9px 12px;font-family:'Sarabun',sans-serif;font-size:0.875rem;outline:none;transition:border-color .15s;width:100%}
input:focus,select:focus{border-color:var(--accent)}
select option{background:var(--surface2)}
</style>
</head>
<body>
<nav class="sidebar">
  <div class="sidebar-logo"><h1>🍜 RestoSys</h1><p>Manager Panel</p></div>
  <div class="user-info">
    <div class="uname">🧑‍💼 <?=htmlspecialchars($_SESSION['user_name']??'')?></div>
    <div class="urole">ร้าน: <?=htmlspecialchars($mgr['RS_name']??'ยังไม่ผูกร้าน')?></div>
  </div>
  <div class="sidebar-nav">
    <div class="nav-label">เมนูหลัก</div>
    <a href="<?= URL_MANAGER_DASH ?>" class="nav-link active"><span class="icon">📊</span><span>Dashboard</span></a>
    <a href="<?= URL_MANAGER_DASH ?>" class="nav-link"><span class="icon">🍱</span><span>จัดการเมนู</span></a>
  </div>
  <div class="sidebar-footer"><a href="<?= URL_LOGOUT ?>" class="btn-logout"><span>🚪</span><span>ออกจากระบบ</span></a></div>
</nav>

<div class="main">
  <div class="page-header">
    <h2>📊 Manager Dashboard</h2>
    <p>ภาพรวมและจัดการเมนูร้าน <?=htmlspecialchars($mgr['RS_name']??'')?></p>
  </div>

  <?php if($msg): ?><div class="alert alert-<?=$msg['type']?>"><?=$msg['text']?></div><?php endif; ?>
  <?php if(!$rs_id): ?><div class="alert alert-danger">⚠️ บัญชีนี้ยังไม่ผูกกับร้านอาหาร กรุณาติดต่อ Admin</div><?php endif; ?>

  <div class="stats-row">
    <div class="stat-card"><div class="label">🍱 เมนูทั้งหมด</div><div class="value"><?=$menus?mysqli_num_rows($menus):0?></div><div class="sub">รายการ</div></div>
    <div class="stat-card"><div class="label">📋 ออเดอร์รวม</div><div class="value"><?=$totalOrders?></div><div class="sub">คำสั่งซื้อ</div></div>
    <div class="stat-card"><div class="label">💰 รายได้รวม</div><div class="value" style="font-size:1.3rem">฿<?=number_format($totalRevenue,0)?></div><div class="sub">บาท</div></div>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
    <!-- เมนูขายดี -->
    <div class="card">
      <div class="card-title">🏆 เมนูขายดี</div>
      <?php if(!$orderStats||mysqli_num_rows($orderStats)===0): ?>
      <div style="text-align:center;color:var(--muted);padding:20px;font-size:0.84rem">ยังไม่มีข้อมูล</div>
      <?php else: $i=1; mysqli_data_seek($orderStats,0); while($os=mysqli_fetch_assoc($orderStats)): ?>
      <div style="display:flex;align-items:center;gap:10px;padding:8px 0;<?=$i<8?'border-bottom:1px solid var(--border)':''?>">
        <div style="width:24px;height:24px;border-radius:50%;background:var(--surface2);display:flex;align-items:center;justify-content:center;font-size:0.7rem;font-weight:700;color:<?=$i<=3?'var(--accent)':'var(--muted)'?>"><?=$i?></div>
        <div style="flex:1;font-size:0.84rem"><strong><?=htmlspecialchars($os['MN_name'])?></strong></div>
        <span class="badge badge-info"><?=$os['qty']?> ชิ้น</span>
        <span style="font-size:0.8rem;color:var(--success)">฿<?=number_format($os['rev'],0)?></span>
      </div>
      <?php $i++; endwhile; endif; ?>
    </div>

    <!-- จัดการเมนู Quick -->
    <div class="card">
      <div class="card-title" style="justify-content:space-between">
        <span>🍱 เมนูทั้งหมด</span>
        <?php if($rs_id): ?><button class="btn btn-primary btn-sm" onclick="openModal('addMenuModal')">+ เพิ่มเมนู</button><?php endif; ?>
      </div>
      <?php if($menus): mysqli_data_seek($menus,0); ?>
      <div style="max-height:300px;overflow-y:auto">
        <?php if(mysqli_num_rows($menus)===0): ?>
        <div style="text-align:center;color:var(--muted);padding:20px;font-size:0.84rem">ยังไม่มีเมนู</div>
        <?php else: while($m=mysqli_fetch_assoc($menus)): ?>
        <div style="display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid var(--border)">
          <div style="flex:1">
            <div style="font-size:0.84rem;font-weight:600"><?=htmlspecialchars($m['MN_name'])?></div>
            <div style="font-size:0.72rem;color:var(--muted)">฿<?=number_format($m['MN_price'],2)?> · <?=number_format($m['NT_calorie'])?> kcal</div>
          </div>
          <span class="badge <?=$m['MN_status']==='available'?'badge-success':'badge-danger'?>"><?=$m['MN_status']==='available'?'เปิด':'ปิด'?></span>
          <button class="btn btn-edit btn-sm" onclick='openEditMenu(<?=json_encode($m)?>)'>✏️</button>
          <form method="POST" style="display:inline" onsubmit="return confirm('ลบเมนูนี้?')">
            <input type="hidden" name="action" value="delete_menu">
            <input type="hidden" name="MN_id" value="<?=htmlspecialchars($m['MN_id'])?>">
            <button type="submit" class="btn btn-danger btn-sm">🗑️</button>
          </form>
        </div>
        <?php endwhile; endif; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Add Menu Modal -->
<div class="modal-overlay" id="addMenuModal">
  <div class="modal">
    <div class="modal-header"><div class="modal-title">🍱 เพิ่มเมนูใหม่</div><button class="modal-close" onclick="closeModal('addMenuModal')">✕</button></div>
    <form method="POST">
      <input type="hidden" name="action" value="add_menu">
      <div class="form-grid">
        <div class="form-group" style="grid-column:1/-1"><label>ชื่อเมนู</label><input name="MN_name" required placeholder="ชื่ออาหาร"></div>
        <div class="form-group"><label>ราคา (บาท)</label><input name="MN_price" type="number" step="0.01" min="0" required placeholder="0.00"></div>
        <div class="form-group"><label>สถานะ</label>
          <select name="MN_status"><option value="available">เปิดขาย</option><option value="unavailable">ปิดขาย</option><option value="seasonal">ตามฤดูกาล</option></select>
        </div>
        <div class="form-group"><label>แคลอรี่ (kcal)</label><input name="NT_calorie" type="number" min="0" value="0"></div>
        <div class="form-group"><label>โปรตีน (g)</label><input name="NT_protein" type="number" step="0.1" value="0"></div>
        <div class="form-group"><label>คาร์โบ (g)</label><input name="NT_carb" type="number" step="0.1" value="0"></div>
        <div class="form-group"><label>ไขมัน (g)</label><input name="NT_fat" type="number" step="0.1" value="0"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn" style="background:var(--surface2);color:var(--text)" onclick="closeModal('addMenuModal')">ยกเลิก</button>
        <button type="submit" class="btn btn-primary">บันทึก</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Menu Modal -->
<div class="modal-overlay" id="editMenuModal">
  <div class="modal">
    <div class="modal-header"><div class="modal-title">✏️ แก้ไขเมนู</div><button class="modal-close" onclick="closeModal('editMenuModal')">✕</button></div>
    <form method="POST">
      <input type="hidden" name="action" value="edit_menu">
      <input type="hidden" name="MN_id" id="e_mn_id">
      <div class="form-grid">
        <div class="form-group" style="grid-column:1/-1"><label>ชื่อเมนู</label><input name="MN_name" id="e_mn_name" required></div>
        <div class="form-group"><label>ราคา (บาท)</label><input name="MN_price" id="e_mn_price" type="number" step="0.01" min="0" required></div>
        <div class="form-group"><label>สถานะ</label>
          <select name="MN_status" id="e_mn_status"><option value="available">เปิดขาย</option><option value="unavailable">ปิดขาย</option><option value="seasonal">ตามฤดูกาล</option></select>
        </div>
        <div class="form-group"><label>แคลอรี่ (kcal)</label><input name="NT_calorie" id="e_nt_cal" type="number" min="0"></div>
        <div class="form-group"><label>โปรตีน (g)</label><input name="NT_protein" id="e_nt_pro" type="number" step="0.1"></div>
        <div class="form-group"><label>คาร์โบ (g)</label><input name="NT_carb" id="e_nt_carb" type="number" step="0.1"></div>
        <div class="form-group"><label>ไขมัน (g)</label><input name="NT_fat" id="e_nt_fat" type="number" step="0.1"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn" style="background:var(--surface2);color:var(--text)" onclick="closeModal('editMenuModal')">ยกเลิก</button>
        <button type="submit" class="btn btn-primary">บันทึก</button>
      </div>
    </form>
  </div>
</div>

<script>
function openModal(id){document.getElementById(id).classList.add('open')}
function closeModal(id){document.getElementById(id).classList.remove('open')}
function openEditMenu(m){
  document.getElementById('e_mn_id').value=m.MN_id;
  document.getElementById('e_mn_name').value=m.MN_name;
  document.getElementById('e_mn_price').value=m.MN_price;
  document.getElementById('e_mn_status').value=m.MN_status;
  document.getElementById('e_nt_cal').value=m.NT_calorie;
  document.getElementById('e_nt_pro').value=m.NT_protein;
  document.getElementById('e_nt_carb').value=m.NT_carb;
  document.getElementById('e_nt_fat').value=m.NT_fat;
  openModal('editMenuModal');
}
document.querySelectorAll('.modal-overlay').forEach(el=>{el.addEventListener('click',function(e){if(e.target===this)this.classList.remove('open')})});
</script>
</body></html>