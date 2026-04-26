<?php
session_start();
require_once __DIR__ . '/../../config/paths.php';
require_once CONFIG_PATH . '/db.php';

if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'] ?? '';
    if ($role === 'admin')   header('Location: ' . URL_INDEX);
    elseif ($role === 'manager') header('Location: ' . URL_MANAGER_DASH);
    else header('Location: ' . URL_USER_DASHBOARD);
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role     = $_POST['role'] ?? 'user';

    if ($role === 'admin') {
        $e = mysqli_real_escape_string($conn, $email);
        $row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM Admin WHERE AD_email='$e' LIMIT 1"));
        if ($row && password_verify($password, $row['AD_password'])) {
            $_SESSION['user_id']   = $row['AD_id'];
            $_SESSION['user_name'] = $row['AD_name'];
            $_SESSION['role']      = 'admin';
            header('Location: ' . URL_INDEX); exit;
        }
    } elseif ($role === 'manager') {
        $e = mysqli_real_escape_string($conn, $email);
        $row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM Manager WHERE MG_email='$e' LIMIT 1"));
        if ($row && password_verify($password, $row['MG_password'])) {
            $_SESSION['user_id']   = $row['MG_id'];
            $_SESSION['user_name'] = $row['MG_name'];
            $_SESSION['role']      = 'manager';
            $_SESSION['RS_id']     = $row['RS_id'] ?? '';
            header('Location: ' . URL_MANAGER_DASH); exit;
        }
    } else {
        $e = mysqli_real_escape_string($conn, $email);
        $row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM Users WHERE US_email='$e' LIMIT 1"));
        if ($row && password_verify($password, $row['US_password'])) {
            $_SESSION['user_id']      = $row['US_id'];
            $_SESSION['user_name']    = $row['US_name'];
            $_SESSION['role']         = 'user';
            $_SESSION['US_wallet']    = $row['US_wallet'];
            $_SESSION['US_calgoal']   = $row['US_caloriegoal'];
            header('Location: ' . URL_USER_DASHBOARD); exit;
        }
    }
    $error = 'อีเมลหรือรหัสผ่านไม่ถูกต้อง';
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>เข้าสู่ระบบ – RestoSys</title>
<link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&family=Prompt:wght@600;700;800&display=swap" rel="stylesheet">
<style>
:root {
  --bg:#0f0f13;--surface:#18181f;--surface2:#222230;--border:#2e2e3f;
  --accent:#f97316;--accent2:#fb923c;--text:#f1f0f5;--muted:#8b8ba0;
  --success:#22c55e;--danger:#ef4444;--info:#38bdf8;--radius:12px;
}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Sarabun',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;display:flex;align-items:center;justify-content:center;position:relative;overflow:hidden}
body::before{content:'';position:fixed;inset:0;background:radial-gradient(ellipse 80% 60% at 50% -10%,rgba(249,115,22,0.15),transparent);pointer-events:none}
.orb{position:fixed;border-radius:50%;filter:blur(80px);pointer-events:none;animation:float 8s ease-in-out infinite}
.orb1{width:400px;height:400px;background:rgba(249,115,22,0.08);top:-100px;left:-100px;animation-delay:0s}
.orb2{width:300px;height:300px;background:rgba(56,189,248,0.06);bottom:-80px;right:-80px;animation-delay:4s}
@keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-20px)}}
.login-wrap{width:100%;max-width:420px;padding:20px;position:relative;z-index:1}
.logo-area{text-align:center;margin-bottom:32px}
.logo-area h1{font-family:'Prompt',sans-serif;font-size:2rem;font-weight:800;color:var(--accent);letter-spacing:-0.5px}
.logo-area p{color:var(--muted);font-size:0.85rem;margin-top:4px}
.card{background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:32px;box-shadow:0 24px 60px rgba(0,0,0,0.4)}
.role-tabs{display:grid;grid-template-columns:repeat(3,1fr);gap:6px;margin-bottom:28px;background:var(--surface2);border-radius:10px;padding:5px}
.role-tab{padding:8px 4px;border-radius:7px;border:none;background:transparent;color:var(--muted);font-family:'Sarabun',sans-serif;font-size:0.82rem;font-weight:600;cursor:pointer;transition:all .2s;text-align:center}
.role-tab.active{background:var(--accent);color:#fff}
.form-group{margin-bottom:16px}
label{display:block;font-size:0.78rem;color:var(--muted);font-weight:500;margin-bottom:6px}
input{width:100%;background:var(--surface2);border:1px solid var(--border);border-radius:8px;color:var(--text);padding:11px 14px;font-family:'Sarabun',sans-serif;font-size:0.9rem;outline:none;transition:border-color .15s}
input:focus{border-color:var(--accent)}
.btn-login{width:100%;background:var(--accent);color:#fff;border:none;border-radius:8px;padding:12px;font-family:'Prompt',sans-serif;font-size:0.95rem;font-weight:700;cursor:pointer;transition:all .2s;margin-top:8px;letter-spacing:0.3px}
.btn-login:hover{background:var(--accent2);transform:translateY(-1px)}
.error{background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);color:var(--danger);border-radius:8px;padding:10px 14px;font-size:0.85rem;margin-bottom:16px}
.register-link{text-align:center;margin-top:18px;font-size:0.82rem;color:var(--muted)}
.register-link a{color:var(--accent);text-decoration:none;font-weight:600}
.register-link a:hover{text-decoration:underline}
</style>
</head>
<body>
<div class="orb orb1"></div>
<div class="orb orb2"></div>
<div class="login-wrap">
  <div class="logo-area">
    <h1>🍜 RestoSys</h1>
    <p>ระบบโภชนาการและสั่งอาหาร</p>
  </div>
  <div class="card">
    <div class="role-tabs">
      <button class="role-tab <?=($_POST['role']??'user')==='user'?'active':''?>" onclick="setRole('user')">🎓 นักศึกษา</button>
      <button class="role-tab <?=($_POST['role']??'')==='manager'?'active':''?>" onclick="setRole('manager')">🧑‍💼 Manager</button>
      <button class="role-tab <?=($_POST['role']??'')==='admin'?'active':''?>" onclick="setRole('admin')">👑 Admin</button>
    </div>
    <?php if($error): ?>
    <div class="error">⚠️ <?=htmlspecialchars($error)?></div>
    <?php endif; ?>
    <form method="POST">
      <input type="hidden" name="role" id="roleInput" value="<?=htmlspecialchars($_POST['role']??'user')?>">
      <div class="form-group">
        <label>อีเมล</label>
        <input type="email" name="email" required placeholder="กรอกอีเมล" value="<?=htmlspecialchars($_POST['email']??'')?>">
      </div>
      <div class="form-group">
        <label>รหัสผ่าน</label>
        <input type="password" name="password" required placeholder="กรอกรหัสผ่าน">
      </div>
      <button type="submit" class="btn-login">เข้าสู่ระบบ →</button>
    </form>
    <div class="register-link">
      ยังไม่มีบัญชี? <a href="<?= URL_REGISTER ?>">สมัครสมาชิก</a>
    </div>
  </div>
</div>
<script>
function setRole(r) {
  document.getElementById('roleInput').value = r;
  document.querySelectorAll('.role-tab').forEach(t => t.classList.remove('active'));
  event.target.classList.add('active');
}
</script>
</body>
</html>