<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= $pageTitle ?? 'RestoSys' ?></title>
<link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&family=Prompt:wght@600;700;800&display=swap" rel="stylesheet">
<style>
:root{
  --bg:#0f0f13;--surface:#18181f;--surface2:#222230;--border:#2e2e3f;
  --accent:#f97316;--accent2:#fb923c;--text:#f1f0f5;--muted:#8b8ba0;
  --success:#22c55e;--danger:#ef4444;--info:#38bdf8;--radius:10px;
  --warn:#eab308;
}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Sarabun',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;display:flex}
.sidebar{width:230px;background:var(--surface);border-right:1px solid var(--border);display:flex;flex-direction:column;position:fixed;height:100vh;z-index:100}
.sidebar-logo{padding:20px 18px;border-bottom:1px solid var(--border)}
.sidebar-logo h1{font-family:'Prompt',sans-serif;font-size:1.1rem;font-weight:800;color:var(--accent)}
.sidebar-logo p{font-size:0.7rem;color:var(--muted);margin-top:2px}
.user-info{padding:14px 16px;border-bottom:1px solid var(--border);background:rgba(249,115,22,0.05)}
.user-info .uname{font-weight:700;font-size:0.88rem;color:var(--text)}
.user-info .urole{font-size:0.7rem;color:var(--accent)}
.wallet-bar{display:flex;justify-content:space-between;align-items:center;padding:10px 16px;background:rgba(34,197,94,0.06);border-bottom:1px solid var(--border)}
.wallet-bar .wlabel{font-size:0.7rem;color:var(--muted)}
.wallet-bar .wval{font-family:'Prompt',sans-serif;font-size:0.95rem;font-weight:700;color:var(--success)}
.sidebar-nav{flex:1;padding:14px 10px;overflow-y:auto}
.nav-label{font-size:0.62rem;text-transform:uppercase;letter-spacing:1.5px;color:var(--muted);padding:8px 8px 4px}
.nav-link{display:flex;align-items:center;gap:10px;padding:9px 12px;border-radius:8px;color:var(--muted);text-decoration:none;font-size:0.86rem;font-weight:500;transition:all .15s;margin-bottom:2px}
.nav-link:hover{background:var(--surface2);color:var(--text)}
.nav-link.active{background:rgba(249,115,22,0.15);color:var(--accent)}
.nav-link .icon{font-size:1rem;width:20px;text-align:center}
.nav-link.highlight{background:linear-gradient(135deg,rgba(249,115,22,0.15),rgba(249,115,22,0.05));color:var(--accent);border:1px solid rgba(249,115,22,0.25)}
.nav-link.highlight:hover{background:linear-gradient(135deg,rgba(249,115,22,0.25),rgba(249,115,22,0.1))}
.sidebar-footer{padding:12px;border-top:1px solid var(--border)}
.btn-logout{display:flex;align-items:center;gap:8px;width:100%;padding:9px 12px;border-radius:8px;background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.2);color:var(--danger);font-family:'Sarabun',sans-serif;font-size:0.83rem;font-weight:600;cursor:pointer;transition:all .15s;text-decoration:none}
.btn-logout:hover{background:rgba(239,68,68,0.16)}
.main{margin-left:230px;flex:1;padding:26px 30px;min-height:100vh}
.page-header{margin-bottom:24px}
.page-header h2{font-family:'Prompt',sans-serif;font-size:1.45rem;font-weight:700}
.page-header p{color:var(--muted);font-size:0.86rem;margin-top:4px}
.card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:22px;margin-bottom:22px}
.card-title{font-family:'Prompt',sans-serif;font-size:0.95rem;font-weight:600;margin-bottom:16px;color:var(--text);display:flex;align-items:center;gap:8px}
.stats-row{display:grid;grid-template-columns:repeat(auto-fill,minmax(170px,1fr));gap:14px;margin-bottom:22px}
.stat-card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:16px 18px}
.stat-card .label{font-size:0.72rem;color:var(--muted);margin-bottom:7px}
.stat-card .value{font-family:'Prompt',sans-serif;font-size:1.7rem;font-weight:700;color:var(--text)}
.stat-card .sub{font-size:0.7rem;color:var(--muted);margin-top:3px}
.progress-bar{height:8px;background:var(--surface2);border-radius:999px;overflow:hidden;margin-top:8px}
.progress-fill{height:100%;border-radius:999px;background:var(--accent);transition:width .5s ease}
.progress-fill.warn{background:var(--warn)}
.progress-fill.over{background:var(--danger)}
.table-wrap{overflow-x:auto}
table{width:100%;border-collapse:collapse;font-size:0.87rem}
thead th{background:var(--surface2);color:var(--muted);font-size:0.7rem;text-transform:uppercase;letter-spacing:0.8px;padding:9px 13px;text-align:left;border-bottom:1px solid var(--border);font-weight:600}
tbody tr{border-bottom:1px solid var(--border);transition:background .1s}
tbody tr:hover{background:var(--surface2)}
tbody td{padding:10px 13px;color:var(--text);vertical-align:middle}
tbody tr:last-child{border-bottom:none}
.btn{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:7px;border:none;cursor:pointer;font-family:'Sarabun',sans-serif;font-size:0.83rem;font-weight:600;text-decoration:none;transition:all .15s}
.btn-primary{background:var(--accent);color:#fff}
.btn-primary:hover{background:var(--accent2)}
.btn-success{background:rgba(34,197,94,0.15);color:var(--success);border:1px solid rgba(34,197,94,0.3)}
.btn-success:hover{background:rgba(34,197,94,0.25)}
.btn-danger{background:rgba(239,68,68,0.12);color:var(--danger);border:1px solid rgba(239,68,68,0.25)}
.btn-sm{padding:5px 10px;font-size:0.76rem}
.badge{display:inline-block;padding:2px 8px;border-radius:20px;font-size:0.7rem;font-weight:600}
.badge-success{background:rgba(34,197,94,0.15);color:var(--success)}
.badge-danger{background:rgba(239,68,68,0.15);color:var(--danger)}
.badge-info{background:rgba(56,189,248,0.12);color:var(--info)}
.badge-warn{background:rgba(234,179,8,0.15);color:var(--warn)}
.alert{padding:11px 15px;border-radius:8px;font-size:0.85rem;margin-bottom:14px;display:flex;align-items:center;gap:8px}
.alert-success{background:rgba(34,197,94,0.1);border:1px solid rgba(34,197,94,0.25);color:var(--success)}
.alert-danger{background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.25);color:var(--danger)}
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,0.75);z-index:999;align-items:center;justify-content:center}
.modal-overlay.open{display:flex}
.modal{background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:26px;width:95%;max-width:580px;max-height:92vh;overflow-y:auto}
.modal-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px}
.modal-title{font-family:'Prompt',sans-serif;font-size:1.05rem;font-weight:700}
.modal-close{background:none;border:none;color:var(--muted);cursor:pointer;font-size:1.2rem;padding:4px}
.modal-close:hover{color:var(--text)}
.modal-footer{display:flex;gap:10px;justify-content:flex-end;margin-top:20px}
.form-group{display:flex;flex-direction:column;gap:5px;margin-bottom:14px}
label{font-size:0.78rem;color:var(--muted);font-weight:500}
input,select,textarea{background:var(--surface2);border:1px solid var(--border);border-radius:7px;color:var(--text);padding:9px 12px;font-family:'Sarabun',sans-serif;font-size:0.875rem;transition:border-color .15s;outline:none;width:100%}
input:focus,select:focus{border-color:var(--accent)}
select option{background:var(--surface2)}
@media(max-width:768px){.sidebar{width:55px}.sidebar-logo h1,.sidebar-logo p,.user-info,.wallet-bar span,.nav-label,.nav-link span{display:none}.main{margin-left:55px;padding:16px}}
</style>
</head>
<body>

<nav class="sidebar">
  <div class="sidebar-logo">
    <h1>🍜 RestoSys</h1>
    <p>Nutrition & Food Order</p>
  </div>
  <div class="user-info">
    <div class="uname">👤 <?=htmlspecialchars($_SESSION['user_name']??'User')?></div>
    <div class="urole">🎓 นักศึกษา</div>
  </div>
  <?php
  $wdata = mysqli_fetch_assoc(mysqli_query($conn,"SELECT US_wallet,US_caloriegoal,US_calorie_today FROM Users WHERE US_id='".mysqli_real_escape_string($conn,$_SESSION['user_id'])."'"));
  $wallet = $wdata['US_wallet'] ?? 0;
  $calGoal = $wdata['US_caloriegoal'] ?? 2000;
  $calToday = $wdata['US_calorie_today'] ?? 0;
  ?>
  <div class="wallet-bar">
    <span class="wlabel">💳 กระเป๋าเงิน</span>
    <span class="wval">฿<?=number_format($wallet,2)?></span>
  </div>
  <div class="sidebar-nav">
    <div class="nav-label">เมนูหลัก</div>
    <a href="<?= URL_USER_DASHBOARD ?>" class="nav-link <?=($activePage??'')==='udash'?'active':''?>"><span class="icon">📊</span><span>Dashboard</span></a>
    <a href="<?= URL_USER_SCAN ?>" class="nav-link highlight <?=($activePage??'')==='uscan'?'active':''?>"><span class="icon">📷</span><span>บันทึกอาหาร</span></a>
    <a href="<?= URL_USER_SHOP ?>" class="nav-link <?=($activePage??'')==='ushop'?'active':''?>"><span class="icon">🛒</span><span>สั่งอาหาร</span></a>
    <a href="<?= URL_USER_HISTORY ?>" class="nav-link <?=($activePage??'')==='uhistory'?'active':''?>"><span class="icon">📋</span><span>ประวัติการสั่ง</span></a>
    <a href="<?= URL_USER_TOPUP ?>" class="nav-link <?=($activePage??'')==='utopup'?'active':''?>"><span class="icon">💳</span><span>เติมเงิน</span></a>
    <a href="<?= URL_USER_PROFILE ?>" class="nav-link <?=($activePage??'')==='uprofile'?'active':''?>"><span class="icon">⚙️</span><span>ตั้งค่าโปรไฟล์</span></a>
  </div>
  <div class="sidebar-footer">
    <a href="<?= URL_LOGOUT ?>" class="btn-logout"><span>🚪</span><span>ออกจากระบบ</span></a>
  </div>
</nav>