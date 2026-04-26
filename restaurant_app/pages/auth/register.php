<?php
session_start();
require_once __DIR__ . '/../../config/paths.php';
require_once CONFIG_PATH . '/db.php';

if (isset($_SESSION['user_id'])) { header('Location: ' . URL_USER_DASHBOARD); exit; }
$error = ''; $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sid     = mysqli_real_escape_string($conn, trim($_POST['US_studentID']));
    $name    = mysqli_real_escape_string($conn, trim($_POST['US_name']));
    $email   = mysqli_real_escape_string($conn, trim($_POST['US_email']));
    $pass    = trim($_POST['US_password']);
    $pass2   = trim($_POST['US_password2']);
    $cal     = (int)($_POST['US_caloriegoal'] ?? 2000);
    $gender  = mysqli_real_escape_string($conn, trim($_POST['US_gender'] ?? ''));
    $height  = floatval($_POST['US_height'] ?? 0);
    $weight  = floatval($_POST['US_weight'] ?? 0);
    $age     = (int)($_POST['US_age'] ?? 0);
    $disease = mysqli_real_escape_string($conn, trim($_POST['US_disease'] ?? 'ไม่มี'));
    $allergy = mysqli_real_escape_string($conn, trim($_POST['US_allergy'] ?? 'ไม่มี'));

    if ($pass !== $pass2) { $error = 'รหัสผ่านไม่ตรงกัน'; }
    elseif (strlen($pass) < 6) { $error = 'รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร'; }
    else {
        $chk = mysqli_fetch_assoc(mysqli_query($conn, "SELECT US_id FROM Users WHERE US_email='$email' OR US_studentID='$sid' LIMIT 1"));
        if ($chk) { $error = 'อีเมลหรือรหัสนักศึกษานี้ถูกใช้แล้ว'; }
        else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $uid  = 'US' . str_pad(rand(1,99999), 7, '0', STR_PAD_LEFT);
            $hh   = mysqli_real_escape_string($conn, $hash);
            $sql  = "INSERT INTO Users(US_id,US_studentID,US_name,US_email,US_password,
                     US_wallet,US_caloriegoal,US_calorie_today,US_gender,US_height,US_weight,
                     US_age,US_disease,US_allergy)
                     VALUES('$uid','$sid','$name','$email','$hh',
                     0,'$cal',0,'$gender','$height','$weight','$age','$disease','$allergy')";
            if (mysqli_query($conn, $sql)) { $success = 'สมัครสมาชิกสำเร็จ!'; }
            else { $error = '❌ ' . mysqli_error($conn); }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>สมัครสมาชิก – RestoSys</title>
<link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&family=Prompt:wght@600;700;800&display=swap" rel="stylesheet">
<style>
:root{--bg:#0f0f13;--surface:#18181f;--surface2:#222230;--border:#2e2e3f;--accent:#f97316;--accent2:#fb923c;--text:#f1f0f5;--muted:#8b8ba0;--success:#22c55e;--danger:#ef4444;--info:#38bdf8;--warn:#eab308}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Sarabun',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px}
body::before{content:'';position:fixed;inset:0;background:radial-gradient(ellipse 80% 60% at 50% -10%,rgba(249,115,22,0.12),transparent);pointer-events:none}
.wrap{width:100%;max-width:620px;position:relative;z-index:1}
.logo-area{text-align:center;margin-bottom:24px}
.logo-area h1{font-family:'Prompt',sans-serif;font-size:1.8rem;font-weight:800;color:var(--accent)}
.logo-area p{color:var(--muted);font-size:0.85rem;margin-top:4px}
.card{background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:32px;box-shadow:0 24px 60px rgba(0,0,0,0.4)}
.card h2{font-family:'Prompt',sans-serif;font-size:1.1rem;font-weight:700;margin-bottom:6px}
.section-title{font-size:0.72rem;font-weight:700;color:var(--accent);text-transform:uppercase;letter-spacing:1.2px;margin:20px 0 12px;padding-bottom:6px;border-bottom:1px solid var(--border)}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.full{grid-column:1/-1}
.form-group{display:flex;flex-direction:column;gap:5px}
label{font-size:0.77rem;color:var(--muted);font-weight:500}
input,select,textarea{background:var(--surface2);border:1px solid var(--border);border-radius:8px;color:var(--text);padding:10px 13px;font-family:'Sarabun',sans-serif;font-size:0.875rem;outline:none;transition:border-color .15s;width:100%}
input:focus,select:focus,textarea:focus{border-color:var(--accent)}
select option{background:var(--surface2)}
textarea{resize:vertical;min-height:72px}
.hint{font-size:0.7rem;color:var(--muted);margin-top:3px}
.bmi-box{background:var(--surface2);border:1px solid var(--border);border-radius:10px;padding:14px 18px;display:flex;align-items:center;gap:16px;flex-wrap:wrap}
.bmi-item{flex:1;min-width:80px}
.bmi-item .bl{font-size:0.68rem;color:var(--muted);margin-bottom:3px}
.bmi-item .bv{font-family:'Prompt',sans-serif;font-size:1.3rem;font-weight:800}
.btn{width:100%;background:var(--accent);color:#fff;border:none;border-radius:8px;padding:12px;font-family:'Prompt',sans-serif;font-size:0.95rem;font-weight:700;cursor:pointer;transition:all .2s;margin-top:10px}
.btn:hover{background:var(--accent2);transform:translateY(-1px)}
.alert{padding:10px 14px;border-radius:8px;font-size:0.85rem;margin-bottom:14px}
.alert-danger{background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);color:var(--danger)}
.alert-success{background:rgba(34,197,94,0.1);border:1px solid rgba(34,197,94,0.3);color:var(--success)}
.back-link{text-align:center;margin-top:16px;font-size:0.82rem;color:var(--muted)}
.back-link a{color:var(--accent);text-decoration:none;font-weight:600}
</style>
</head>
<body>
<div class="wrap">
  <div class="logo-area">
    <h1>🍜 RestoSys</h1>
    <p>สมัครสมาชิกเพื่อเริ่มต้นใช้งาน</p>
  </div>
  <div class="card">
    <h2>🎓 สร้างบัญชีนักศึกษา</h2>
    <?php if($error): ?><div class="alert alert-danger">⚠️ <?=htmlspecialchars($error)?></div><?php endif; ?>
    <?php if($success): ?>
      <div class="alert alert-success">✅ <?=htmlspecialchars($success)?> <a href="<?= URL_LOGIN ?>" style="color:var(--success);font-weight:700">เข้าสู่ระบบเลย →</a></div>
    <?php endif; ?>
    <?php if(!$success): ?>
    <form method="POST">

      <div class="section-title">👤 ข้อมูลบัญชี</div>
      <div class="form-grid">
        <div class="form-group full"><label>รหัสนักศึกษา</label><input name="US_studentID" required placeholder="6400000001" maxlength="10"></div>
        <div class="form-group full"><label>ชื่อ-นามสกุล</label><input name="US_name" required placeholder="ชื่อ นามสกุล"></div>
        <div class="form-group full"><label>อีเมล</label><input type="email" name="US_email" required placeholder="student@university.ac.th"></div>
        <div class="form-group"><label>รหัสผ่าน</label><input type="password" name="US_password" required placeholder="อย่างน้อย 6 ตัว"></div>
        <div class="form-group"><label>ยืนยันรหัสผ่าน</label><input type="password" name="US_password2" required placeholder="กรอกซ้ำ"></div>
      </div>

      <div class="section-title">🏥 ข้อมูลสุขภาพ</div>
      <div class="form-grid">
        <div class="form-group">
          <label>เพศ</label>
          <select name="US_gender" id="gender_sel" required onchange="calcBMI()">
            <option value="">-- เลือกเพศ --</option>
            <option value="male">ชาย</option>
            <option value="female">หญิง</option>
            <option value="other">อื่นๆ</option>
          </select>
        </div>
        <div class="form-group">
          <label>อายุ (ปี)</label>
          <input type="number" name="US_age" id="age_in" required min="1" max="100" placeholder="20" oninput="calcBMI()">
        </div>
        <div class="form-group">
          <label>ส่วนสูง (cm)</label>
          <input type="number" name="US_height" id="ht_in" required min="50" max="250" placeholder="170" step="0.1" oninput="calcBMI()">
        </div>
        <div class="form-group">
          <label>น้ำหนัก (kg)</label>
          <input type="number" name="US_weight" id="wt_in" required min="20" max="300" placeholder="60" step="0.1" oninput="calcBMI()">
        </div>

        <!-- BMI Result -->
        <div class="form-group full" id="bmi_box" style="display:none">
          <div class="bmi-box">
            <div class="bmi-item">
              <div class="bl">ค่า BMI</div>
              <div class="bv" id="bmi_v" style="color:var(--accent)">-</div>
            </div>
            <div class="bmi-item">
              <div class="bl">สถานะ</div>
              <div class="bv" id="bmi_s" style="font-size:0.95rem">-</div>
            </div>
            <div class="bmi-item">
              <div class="bl">แคลแนะนำ/วัน</div>
              <div class="bv" id="bmi_c" style="color:var(--success);font-size:1rem">-</div>
            </div>
          </div>
        </div>

        <div class="form-group full">
          <label>เป้าหมายแคลอรี่ต่อวัน (kcal)</label>
          <input type="number" name="US_caloriegoal" id="cal_in" required min="500" max="5000" value="2000">
          <span class="hint">💡 กรอกส่วนสูง น้ำหนัก อายุ เพื่อให้ระบบคำนวณแคลแนะนำให้อัตโนมัติ</span>
        </div>
      </div>

      <div class="section-title">⚕️ โรคประจำตัวและอาหารที่แพ้</div>
      <div class="form-grid">
        <div class="form-group full">
          <label>โรคประจำตัว</label>
          <textarea name="US_disease" placeholder="เช่น เบาหวาน ความดันโลหิตสูง ไขมันในเลือดสูง&#10;(ถ้าไม่มีให้พิมพ์ว่า ไม่มี)"></textarea>
        </div>
        <div class="form-group full">
          <label>อาหารที่แพ้ / ไม่รับประทาน</label>
          <textarea name="US_allergy" placeholder="เช่น กุ้ง ถั่วลิสง นม แป้งสาลี อาหารทะเล&#10;(ถ้าไม่มีให้พิมพ์ว่า ไม่มี)"></textarea>
        </div>
      </div>

      <button type="submit" class="btn">✅ สมัครสมาชิก</button>
    </form>
    <?php endif; ?>
    <div class="back-link">มีบัญชีแล้ว? <a href="<?= URL_LOGIN ?>">เข้าสู่ระบบ</a></div>
  </div>
</div>
<script>
function calcBMI() {
  const h = parseFloat(document.getElementById('ht_in').value);
  const w = parseFloat(document.getElementById('wt_in').value);
  const a = parseInt(document.getElementById('age_in').value) || 20;
  const g = document.getElementById('gender_sel').value;
  if (!h || !w || h < 50 || w < 10) { document.getElementById('bmi_box').style.display='none'; return; }

  const bmi = w / ((h/100)**2);
  let status = '', color = '';
  if (bmi < 18.5)      { status = '⚠️ น้ำหนักน้อย'; color = 'var(--info)'; }
  else if (bmi < 23)   { status = '✅ ปกติ';         color = 'var(--success)'; }
  else if (bmi < 25)   { status = '⚠️ น้ำหนักเกิน'; color = 'var(--warn)'; }
  else                 { status = '❌ อ้วน';          color = 'var(--danger)'; }

  // Harris-Benedict + activity 1.55
  let bmr = g === 'female'
    ? 447.593 + (9.247*w) + (3.098*h) - (4.330*a)
    : 88.362  + (13.397*w) + (4.799*h) - (5.677*a);
  const tdee = Math.round(bmr * 1.55);

  document.getElementById('bmi_v').textContent = bmi.toFixed(1);
  document.getElementById('bmi_s').textContent = status;
  document.getElementById('bmi_s').style.color = color;
  document.getElementById('bmi_c').textContent = tdee + ' kcal';
  document.getElementById('cal_in').value = tdee;
  document.getElementById('bmi_box').style.display = 'block';
}
</script>
</body>
</html>