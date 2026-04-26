<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $pageTitle ?? 'Restaurant System' ?></title>
<link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&family=Prompt:wght@400;600;700&display=swap" rel="stylesheet">
<style>
  :root {
    --bg: #0f0f13; --surface: #18181f; --surface2: #222230; --border: #2e2e3f;
    --accent: #f97316; --accent2: #fb923c; --text: #f1f0f5; --muted: #8b8ba0;
    --success: #22c55e; --danger: #ef4444; --info: #38bdf8; --radius: 10px;
  }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Sarabun', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; display: flex; }
  .sidebar { width: 240px; background: var(--surface); border-right: 1px solid var(--border); display: flex; flex-direction: column; position: fixed; height: 100vh; z-index: 100; }
  .sidebar-logo { padding: 24px 20px; border-bottom: 1px solid var(--border); }
  .sidebar-logo h1 { font-family: 'Prompt', sans-serif; font-size: 1.1rem; font-weight: 700; color: var(--accent); letter-spacing: 0.5px; }
  .sidebar-logo p { font-size: 0.72rem; color: var(--muted); margin-top: 2px; }
  .admin-info { padding: 12px 16px; border-bottom: 1px solid var(--border); background: rgba(249,115,22,0.05); }
  .admin-info .aname { font-size: 0.84rem; font-weight: 700; }
  .admin-info .arole { font-size: 0.68rem; color: var(--accent); }
  .sidebar-nav { flex: 1; padding: 16px 12px; overflow-y: auto; }
  .nav-group { margin-bottom: 8px; }
  .nav-label { font-size: 0.65rem; text-transform: uppercase; letter-spacing: 1.5px; color: var(--muted); padding: 8px 8px 4px; }
  .nav-link { display: flex; align-items: center; gap: 10px; padding: 9px 12px; border-radius: 8px; color: var(--muted); text-decoration: none; font-size: 0.88rem; font-weight: 500; transition: all 0.15s; margin-bottom: 2px; }
  .nav-link:hover { background: var(--surface2); color: var(--text); }
  .nav-link.active { background: rgba(249,115,22,0.15); color: var(--accent); }
  .nav-link .icon { font-size: 1rem; width: 20px; text-align: center; }
  .sidebar-footer { padding: 12px; border-top: 1px solid var(--border); }
  .btn-logout { display: flex; align-items: center; gap: 8px; width: 100%; padding: 9px 12px; border-radius: 8px; background: rgba(239,68,68,0.08); border: 1px solid rgba(239,68,68,0.2); color: var(--danger); font-family: 'Sarabun', sans-serif; font-size: 0.83rem; font-weight: 600; cursor: pointer; transition: all 0.15s; text-decoration: none; }
  .btn-logout:hover { background: rgba(239,68,68,0.16); }
  .main { margin-left: 240px; flex: 1; padding: 28px 32px; min-height: 100vh; }
  .page-header { margin-bottom: 28px; }
  .page-header h2 { font-family: 'Prompt', sans-serif; font-size: 1.5rem; font-weight: 700; color: var(--text); }
  .page-header p { color: var(--muted); font-size: 0.88rem; margin-top: 4px; }
  .card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 24px; margin-bottom: 24px; }
  .card-title { font-family: 'Prompt', sans-serif; font-size: 1rem; font-weight: 600; margin-bottom: 18px; color: var(--text); display: flex; align-items: center; gap: 8px; }
  .table-wrap { overflow-x: auto; }
  table { width: 100%; border-collapse: collapse; font-size: 0.875rem; }
  thead th { background: var(--surface2); color: var(--muted); font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.8px; padding: 10px 14px; text-align: left; border-bottom: 1px solid var(--border); font-weight: 600; }
  tbody tr { border-bottom: 1px solid var(--border); transition: background 0.1s; }
  tbody tr:hover { background: var(--surface2); }
  tbody td { padding: 11px 14px; color: var(--text); vertical-align: middle; }
  tbody tr:last-child { border-bottom: none; }
  .btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 7px; border: none; cursor: pointer; font-family: 'Sarabun', sans-serif; font-size: 0.85rem; font-weight: 600; text-decoration: none; transition: all 0.15s; }
  .btn-primary { background: var(--accent); color: white; }.btn-primary:hover { background: var(--accent2); }
  .btn-danger { background: rgba(239,68,68,0.15); color: var(--danger); border: 1px solid rgba(239,68,68,0.3); }
  .btn-danger:hover { background: rgba(239,68,68,0.25); }
  .btn-edit { background: rgba(56,189,248,0.12); color: var(--info); border: 1px solid rgba(56,189,248,0.25); }
  .btn-edit:hover { background: rgba(56,189,248,0.22); }
  .btn-sm { padding: 5px 10px; font-size: 0.78rem; }
  .form-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 16px; }
  .form-group { display: flex; flex-direction: column; gap: 6px; }
  label { font-size: 0.8rem; color: var(--muted); font-weight: 500; }
  input, select, textarea { background: var(--surface2); border: 1px solid var(--border); border-radius: 7px; color: var(--text); padding: 9px 12px; font-family: 'Sarabun', sans-serif; font-size: 0.875rem; transition: border-color 0.15s; outline: none; }
  input:focus, select:focus, textarea:focus { border-color: var(--accent); }
  select option { background: var(--surface2); }
  .badge { display: inline-block; padding: 2px 8px; border-radius: 20px; font-size: 0.72rem; font-weight: 600; }
  .badge-success { background: rgba(34,197,94,0.15); color: var(--success); }
  .badge-danger { background: rgba(239,68,68,0.15); color: var(--danger); }
  .badge-info { background: rgba(56,189,248,0.12); color: var(--info); }
  .alert { padding: 12px 16px; border-radius: 8px; font-size: 0.875rem; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
  .alert-success { background: rgba(34,197,94,0.1); border: 1px solid rgba(34,197,94,0.25); color: var(--success); }
  .alert-danger { background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.25); color: var(--danger); }
  .stats-row { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 16px; margin-bottom: 28px; }
  .stat-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 18px 20px; }
  .stat-card .label { font-size: 0.75rem; color: var(--muted); margin-bottom: 8px; }
  .stat-card .value { font-family: 'Prompt', sans-serif; font-size: 1.8rem; font-weight: 700; color: var(--text); }
  .stat-card .sub { font-size: 0.72rem; color: var(--muted); margin-top: 4px; }
  .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.7); z-index: 999; align-items: center; justify-content: center; }
  .modal-overlay.open { display: flex; }
  .modal { background: var(--surface); border: 1px solid var(--border); border-radius: 14px; padding: 28px; width: 95%; max-width: 560px; max-height: 90vh; overflow-y: auto; }
  .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 22px; }
  .modal-title { font-family: 'Prompt', sans-serif; font-size: 1.05rem; font-weight: 700; }
  .modal-close { background: none; border: none; color: var(--muted); cursor: pointer; font-size: 1.2rem; padding: 4px; }
  .modal-close:hover { color: var(--text); }
  .modal-footer { display: flex; gap: 10px; justify-content: flex-end; margin-top: 22px; }
  .confirm-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.7); z-index: 1000; align-items: center; justify-content: center; }
  .confirm-overlay.open { display: flex; }
  .confirm-box { background: var(--surface); border: 1px solid var(--border); border-radius: 14px; padding: 28px; width: 380px; text-align: center; }
  .confirm-icon { font-size: 2.5rem; margin-bottom: 12px; }
  .confirm-title { font-family: 'Prompt', sans-serif; font-size: 1.1rem; font-weight: 700; margin-bottom: 8px; }
  .confirm-text { color: var(--muted); font-size: 0.88rem; margin-bottom: 22px; }
  .confirm-btns { display: flex; gap: 10px; justify-content: center; }
  @media (max-width: 768px) { .sidebar { width: 60px; } .sidebar-logo h1, .sidebar-logo p, .nav-label, .nav-link span, .admin-info { display: none; } .main { margin-left: 60px; padding: 20px 16px; } }
</style>
</head>
<body>

<nav class="sidebar">
  <div class="sidebar-logo">
    <h1>🍜 RestoSys</h1>
    <p>Restaurant Management</p>
  </div>
  <?php if(isset($_SESSION['user_id'])): ?>
  <div class="admin-info">
    <div class="aname">👑 <?=htmlspecialchars($_SESSION['user_name']??'Admin')?></div>
    <div class="arole">ผู้ดูแลระบบ</div>
  </div>
  <?php endif; ?>
  <div class="sidebar-nav">
    <div class="nav-group">
      <div class="nav-label">ภาพรวม</div>
      <a href="<?= URL_INDEX ?>" class="nav-link <?= ($activePage??'')==='dashboard'?'active':'' ?>">
        <span class="icon">📊</span><span>Dashboard</span>
      </a>
    </div>
    <div class="nav-group">
      <div class="nav-label">ผู้ใช้งานระบบ</div>
      <a href="<?= URL_ADMIN ?>" class="nav-link <?= ($activePage??'')==='admin'?'active':'' ?>">
        <span class="icon">👑</span><span>Admin</span>
      </a>
      <a href="<?= URL_MANAGER ?>" class="nav-link <?= ($activePage??'')==='manager'?'active':'' ?>">
        <span class="icon">🧑‍💼</span><span>Manager</span>
      </a>
      <a href="<?= URL_USERS ?>" class="nav-link <?= ($activePage??'')==='users'?'active':'' ?>">
        <span class="icon">🎓</span><span>นักศึกษา</span>
      </a>
    </div>
    <div class="nav-group">
      <div class="nav-label">ร้านอาหาร</div>
      <a href="<?= URL_RESTAURANT ?>" class="nav-link <?= ($activePage??'')==='restaurant'?'active':'' ?>">
        <span class="icon">🏪</span><span>ร้านอาหาร</span>
      </a>
      <a href="<?= URL_MENU ?>" class="nav-link <?= ($activePage??'')==='menu'?'active':'' ?>">
        <span class="icon">🍱</span><span>เมนู</span>
      </a>
      <a href="<?= URL_NUTRITION ?>" class="nav-link <?= ($activePage??'')==='nutrition'?'active':'' ?>">
        <span class="icon">🥗</span><span>โภชนาการ</span>
      </a>
    </div>
    <div class="nav-group">
      <div class="nav-label">คำสั่งซื้อ</div>
      <a href="<?= URL_ORDERS ?>" class="nav-link <?= ($activePage??'')==='orders'?'active':'' ?>">
        <span class="icon">📋</span><span>Orders</span>
      </a>
      <a href="<?= URL_ORDER_DETAIL ?>" class="nav-link <?= ($activePage??'')==='orderdetail'?'active':'' ?>">
        <span class="icon">📝</span><span>Order Detail</span>
      </a>
    </div>
    <div class="nav-group">
      <div class="nav-label">Analytics</div>
      <a href="<?= URL_ADMIN_ANALYTICS ?>" class="nav-link <?= ($activePage??'')==='analytics'?'active':'' ?>">
        <span class="icon">📈</span><span>รายงาน</span>
      </a>
    </div>
  </div>
  <div class="sidebar-footer">
    <a href="<?= URL_LOGOUT ?>" class="btn-logout"><span>🚪</span><span>ออกจากระบบ</span></a>
  </div>
</nav>