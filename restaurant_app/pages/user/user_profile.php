<?php
session_start();
require_once __DIR__ . '/../../config/paths.php';
require_once INCLUDES_PATH . '/auth.php';
requireLogin('user');
require_once CONFIG_PATH . '/db.php';
$uid = $_SESSION['user_id'];
$e   = mysqli_real_escape_string($conn, $uid);
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = mysqli_real_escape_string($conn, trim($_POST['US_name']));
    $cal     = (int)$_POST['US_caloriegoal'];
    $gender  = mysqli_real_escape_string($conn, trim($_POST['US_gender'] ?? ''));
    $height  = floatval($_POST['US_height'] ?? 0);
    $weight  = floatval($_POST['US_weight'] ?? 0);
    $age     = (int)($_POST['US_age'] ?? 0);
    $disease = mysqli_real_escape_string($conn, trim($_POST['US_disease'] ?? ''));
    $allergy = mysqli_real_escape_string($conn, trim($_POST['US_allergy'] ?? ''));
    $pass    = trim($_POST['new_password'] ?? '');

    $sql = "UPDATE Users SET US_name='$name', US_caloriegoal='$cal',
            US_gender='$gender', US_height='$height', US_weight='$weight',
            US_age='$age', US_disease='$disease', US_allergy='$allergy'";
    if ($pass !== '') {
        if (strlen($pass) < 6) { $msg=['type'=>'danger','text'=>'รหัสผ่านต้องมีอย่างน้อย 6 ตัว']; goto done; }
        $hash = mysqli_real_escape_string($conn, password_hash($pass, PASSWORD_DEFAULT));
        $sql .= ", US_password='$hash'";
    }
    $sql .= " WHERE US_id='$e'";
    if (mysqli_query($conn, $sql)) {
        $_SESSION['user_name'] = $name;
        $msg = ['type'=>'success','text'=>'✅ บันทึกข้อมูลสำเร็จ'];
    } else { $msg = ['type'=>'danger','text'=>'❌ '.mysqli_error($conn)]; }
}
done:
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM Users WHERE US_id='$e'"));

// คำนวณ BMI
$bmi = 0; $bmi_status = ''; $bmi_color = '';
if ($user['US_height'] > 0 && $user['US_weight'] > 0) {
    $bmi = $user['US_weight'] / (($user['US_height']/100) ** 2);
    if ($bmi < 18.5)      { $bmi_status = 'น้ำหนักน้อย'; $bmi_color = 'var(--info)'; }
    elseif ($bmi < 23)    { $bmi_status = 'ปกติ ✅';      $bmi_color = 'var(--success)'; }
    elseif ($bmi < 25)    { $bmi_status = 'น้ำหนักเกิน'; $bmi_color = 'var(--warn)'; }
    else                  { $bmi_status = 'อ้วน ⚠️';      $bmi_color = 'var(--danger)'; }
}

$pageTitle = "โปรไฟล์ – RestoSys";
$activePage = "uprofile";
require_once INCLUDES_PATH . '/user_header.php';
?>

<div class="main">
  <div class="page-header">
    <h2>⚙️ โปรไฟล์และสุขภาพ</h2>
    <p>ข้อมูลส่วนตัวและสุขภาพของคุณ</p>
  </div>

  <?php if($msg): ?><div class="alert alert-<?=$msg['type']?>"><?=$msg['text']?></div><?php endif; ?>

  <!-- Health Summary Cards -->
  <?php if($bmi > 0): ?>
  <div class="stats-row" style="margin-bottom:20px">
    <div class="stat-card">
      <div class="label">📏 ส่วนสูง</div>
      <div class="value" style="font-size:1.5rem"><?=number_format($user['US_height'],1)?></div>
      <div class="sub">เซนติเมตร</div>
    </div>
    <div class="stat-card">
      <div class="label">⚖️ น้ำหนัก</div>
      <div class="value" style="font-size:1.5rem"><?=number_format($user['US_weight'],1)?></div>
      <div class="sub">กิโลกรัม</div>
    </div>
    <div class="stat-card">
      <div class="label">📊 BMI</div>
      <div class="value" style="font-size:1.5rem;color:<?=$bmi_color?>"><?=number_format($bmi,1)?></div>
      <div class="sub"><?=$bmi_status?></div>
    </div>
    <div class="stat-card">
      <div class="label">🎂 อายุ</div>
      <div class="value" style="font-size:1.5rem"><?=$user['US_age']?></div>
      <div class="sub">ปี</div>
    </div>
    <div class="stat-card">
      <div class="label">🔥 เป้าหมายแคล</div>
      <div class="value" style="font-size:1.3rem"><?=number_format($user['US_caloriegoal'])?></div>
      <div class="sub">kcal/วัน</div>
    </div>
  </div>
  <?php endif; ?>

  <!-- โรคและแพ้อาหาร Summary -->
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px">
    <div class="card" style="margin-bottom:0">
      <div class="card-title">🏥 โรคประจำตัว</div>
      <div style="font-size:0.88rem;color:<?=($user['US_disease']&&$user['US_disease']!='ไม่มี')?'var(--warn)':'var(--success)'?>">
        <?=nl2br(htmlspecialchars($user['US_disease'] ?: 'ไม่มี'))?>
      </div>
    </div>
    <div class="card" style="margin-bottom:0">
      <div class="card-title">⚠️ อาหารที่แพ้</div>
      <div style="font-size:0.88rem;color:<?=($user['US_allergy']&&$user['US_allergy']!='ไม่มี')?'var(--danger)':'var(--success)'?>">
        <?=nl2br(htmlspecialchars($user['US_allergy'] ?: 'ไม่มี'))?>
      </div>
    </div>
  </div>

  <!-- Edit Form -->
  <div class="card">
    <div class="card-title">✏️ แก้ไขข้อมูล</div>
    <form method="POST">

      <div style="font-size:0.72rem;font-weight:700;color:var(--accent);text-transform:uppercase;letter-spacing:1px;margin-bottom:12px;padding-bottom:6px;border-bottom:1px solid var(--border)">👤 ข้อมูลบัญชี</div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px">
        <div class="form-group"><label>รหัสนักศึกษา</label><input value="<?=htmlspecialchars($user['US_studentID'])?>" disabled style="opacity:.5"></div>
        <div class="form-group"><label>อีเมล</label><input value="<?=htmlspecialchars($user['US_email'])?>" disabled style="opacity:.5"></div>
        <div class="form-group" style="grid-column:1/-1"><label>ชื่อ-นามสกุล</label><input name="US_name" required value="<?=htmlspecialchars($user['US_name'])?>"></div>
        <div class="form-group" style="grid-column:1/-1"><label>รหัสผ่านใหม่ (เว้นว่างถ้าไม่เปลี่ยน)</label><input type="password" name="new_password" placeholder="อย่างน้อย 6 ตัว"></div>
      </div>

      <div style="font-size:0.72rem;font-weight:700;color:var(--accent);text-transform:uppercase;letter-spacing:1px;margin-bottom:12px;padding-bottom:6px;border-bottom:1px solid var(--border)">🏥 ข้อมูลสุขภาพ</div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px">
        <div class="form-group">
          <label>เพศ</label>
          <select name="US_gender" id="gender_sel" onchange="calcBMI()">
            <option value="male" <?=$user['US_gender']==='male'?'selected':''?>>ชาย</option>
            <option value="female" <?=$user['US_gender']==='female'?'selected':''?>>หญิง</option>
            <option value="other" <?=$user['US_gender']==='other'?'selected':''?>>อื่นๆ</option>
          </select>
        </div>
        <div class="form-group">
          <label>อายุ (ปี)</label>
          <input type="number" name="US_age" id="age_in" min="1" max="100" value="<?=htmlspecialchars($user['US_age'])?>" oninput="calcBMI()">
        </div>
        <div class="form-group">
          <label>ส่วนสูง (cm)</label>
          <input type="number" name="US_height" id="ht_in" min="50" max="250" step="0.1" value="<?=htmlspecialchars($user['US_height'])?>" oninput="calcBMI()">
        </div>
        <div class="form-group">
          <label>น้ำหนัก (kg)</label>
          <input type="number" name="US_weight" id="wt_in" min="20" max="300" step="0.1" value="<?=htmlspecialchars($user['US_weight'])?>" oninput="calcBMI()">
        </div>

        <!-- BMI live -->
        <div style="grid-column:1/-1" id="bmi_box" <?=($bmi>0?'':'style="display:none"')?>>
          <div style="background:var(--surface2);border:1px solid var(--border);border-radius:10px;padding:12px 16px;display:flex;gap:20px;align-items:center">
            <div><div style="font-size:0.68rem;color:var(--muted)">BMI</div><div style="font-family:'Prompt',sans-serif;font-size:1.3rem;font-weight:800;color:var(--accent)" id="bmi_v"><?=$bmi>0?number_format($bmi,1):'-'?></div></div>
            <div><div style="font-size:0.68rem;color:var(--muted)">สถานะ</div><div id="bmi_s" style="font-weight:700;font-size:0.9rem;color:<?=$bmi_color?>"><?=$bmi_status?></div></div>
            <div><div style="font-size:0.68rem;color:var(--muted)">แคลแนะนำ</div><div id="bmi_c" style="font-weight:700;font-size:0.9rem;color:var(--success)">-</div></div>
          </div>
        </div>

        <div class="form-group" style="grid-column:1/-1">
          <label>เป้าหมายแคลอรี่ต่อวัน (kcal)</label>
          <input type="number" name="US_caloriegoal" id="cal_in" required min="500" max="5000" value="<?=htmlspecialchars($user['US_caloriegoal'])?>">
        </div>
      </div>

      <div style="font-size:0.72rem;font-weight:700;color:var(--accent);text-transform:uppercase;letter-spacing:1px;margin-bottom:12px;padding-bottom:6px;border-bottom:1px solid var(--border)">⚕️ โรคประจำตัวและอาหารที่แพ้</div>
      <div class="form-group" style="margin-bottom:12px">
        <label>โรคประจำตัว</label>
        <textarea name="US_disease" style="min-height:80px" placeholder="เช่น เบาหวาน ความดัน (ถ้าไม่มีพิมพ์ ไม่มี)"><?=htmlspecialchars($user['US_disease'] ?? 'ไม่มี')?></textarea>
      </div>
      <div class="form-group" style="margin-bottom:16px">
        <label>อาหารที่แพ้ / ไม่รับประทาน</label>
        <textarea name="US_allergy" style="min-height:80px" placeholder="เช่น กุ้ง ถั่วลิสง นม (ถ้าไม่มีพิมพ์ ไม่มี)"><?=htmlspecialchars($user['US_allergy'] ?? 'ไม่มี')?></textarea>
      </div>

      <button type="submit" class="btn btn-primary">💾 บันทึกข้อมูล</button>
    </form>

    <!-- Reset calorie -->
    <div style="margin-top:16px;padding-top:16px;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
      <div>
        <div style="font-size:0.8rem;color:var(--muted)">แคลวันนี้ (รีเซ็ตทุกวัน)</div>
        <div style="font-family:'Prompt',sans-serif;font-size:1.1rem;font-weight:700;margin-top:2px"><?=number_format($user['US_calorie_today'])?> kcal</div>
      </div>
      <form method="POST" action="<?= URL_USER_RESET_CAL ?>">
        <button type="submit" class="btn btn-sm" style="background:var(--surface2);border:1px solid var(--border);color:var(--muted)">🔄 รีเซ็ตแคลวันนี้</button>
      </form>
    </div>
  </div>
</div>

<script>
function calcBMI() {
  const h = parseFloat(document.getElementById('ht_in').value);
  const w = parseFloat(document.getElementById('wt_in').value);
  const a = parseInt(document.getElementById('age_in').value) || 20;
  const g = document.getElementById('gender_sel').value;
  if (!h || !w || h < 50) { document.getElementById('bmi_box').style.display='none'; return; }
  const bmi = w / ((h/100)**2);
  let status='', color='';
  if (bmi < 18.5)    { status='น้ำหนักน้อย'; color='var(--info)'; }
  else if (bmi < 23) { status='ปกติ ✅';      color='var(--success)'; }
  else if (bmi < 25) { status='น้ำหนักเกิน'; color='var(--warn)'; }
  else               { status='อ้วน ⚠️';      color='var(--danger)'; }
  let bmr = g==='female'
    ? 447.593+(9.247*w)+(3.098*h)-(4.330*a)
    : 88.362+(13.397*w)+(4.799*h)-(5.677*a);
  const tdee = Math.round(bmr*1.55);
  document.getElementById('bmi_v').textContent = bmi.toFixed(1);
  document.getElementById('bmi_s').textContent = status;
  document.getElementById('bmi_s').style.color = color;
  document.getElementById('bmi_c').textContent = tdee+' kcal';
  document.getElementById('cal_in').value = tdee;
  document.getElementById('bmi_box').style.display = 'block';
}
</script>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>