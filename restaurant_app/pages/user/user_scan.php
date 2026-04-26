<?php
session_start();
require_once __DIR__ . '/../../config/paths.php';
require_once INCLUDES_PATH . '/auth.php';
requireLogin('user');
require_once CONFIG_PATH . '/db.php';

$uid = $_SESSION['user_id'];
$e   = mysqli_real_escape_string($conn, $uid);
$msg = '';

// RS_id พิเศษสำหรับเก็บ menu ของ user log (ต้องมีในตาราง Restaurant)
$USER_LOG_RS = 'RS_USER_LOG';

// ── POST: บันทึกอาหาร ─────────────────────────────────
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='save') {
    $mealName = trim($_POST['meal_name'] ?? '');
    $mealType = trim($_POST['meal_type'] ?? 'lunch');
    $cal      = (int)($_POST['calorie'] ?? 0);
    $protein  = (float)($_POST['protein'] ?? 0);
    $carb     = (float)($_POST['carb'] ?? 0);
    $fat      = (float)($_POST['fat'] ?? 0);
    $qty      = max(1, (int)($_POST['quantity'] ?? 1));

    if ($mealName === '' || $cal <= 0) {
        $msg = ['type'=>'danger','text'=>'❌ กรุณากรอกชื่ออาหารและแคลอรี่'];
    } else {
        // 1) จัดการไฟล์รูป (optional)
        $imgFile = null;
        if (!empty($_FILES['food_image']['name']) && $_FILES['food_image']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['jpg','jpeg','png','gif','webp'];
            $ext = strtolower(pathinfo($_FILES['food_image']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed)) {
                $msg = ['type'=>'danger','text'=>'❌ รองรับเฉพาะ JPG, PNG, GIF, WEBP'];
            } elseif ($_FILES['food_image']['size'] > 5*1024*1024) {
                $msg = ['type'=>'danger','text'=>'❌ ไฟล์ใหญ่เกิน 5MB'];
            } else {
                $uploadDir = __DIR__ . '/uploads/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                $imgFile = 'food_' . $uid . '_' . time() . '_' . rand(100,999) . '.' . $ext;
                if (!move_uploaded_file($_FILES['food_image']['tmp_name'], $uploadDir . $imgFile)) {
                    $imgFile = null;
                    $msg = ['type'=>'danger','text'=>'❌ อัปโหลดรูปไม่สำเร็จ'];
                }
            }
        }

        if (!$msg) {
            // 2) สร้าง Menu ใหม่ (reference ให้ OrderDetail)
            $mnId = 'MNU' . date('ymdHis') . rand(10,99);
            $nameEsc = mysqli_real_escape_string($conn, $mealName);
            $price   = 0; // ไม่ใช่การซื้อ ราคา 0

            mysqli_query($conn, "INSERT INTO Menu(MN_id,RS_id,MN_name,MN_price,MN_status)
                                 VALUES('$mnId','$USER_LOG_RS','$nameEsc','$price','available')");

            // 3) สร้าง Nutrition (ถ้าใส่มา)
            if ($protein > 0 || $carb > 0 || $fat > 0 || $cal > 0) {
                $ntId = 'NT' . date('ymdHis') . rand(10,99);
                mysqli_query($conn, "INSERT INTO Nutrition(NT_id,MN_id,NT_calorie,NT_protein,NT_carb,NT_fat)
                                     VALUES('$ntId','$mnId','$cal','$protein','$carb','$fat')");
            }

            // 4) สร้าง Order
            $orId = 'OR' . date('ymdHis') . rand(10,99);
            $dt   = date('Y-m-d H:i:s');
            $totalCal = $cal * $qty;
            $imgEsc = $imgFile ? mysqli_real_escape_string($conn, $imgFile) : null;
            $imgSql = $imgEsc ? "'$imgEsc'" : "NULL";

            mysqli_query($conn, "INSERT INTO Orders(OR_id,US_id,OR_datetime,OR_totalprice,OR_totalcalorie,OR_image)
                                 VALUES('$orId','$e','$dt',0,'$totalCal',$imgSql)");

            // 5) สร้าง OrderDetail
            $ortId = 'ORT' . date('ymdHis') . rand(100,999);
            mysqli_query($conn, "INSERT INTO OrderDetail(ORT_id,OR_id,MN_id,ORT_quantity,ORT_priceperunit)
                                 VALUES('$ortId','$orId','$mnId','$qty','0')");

            // 6) อัปเดต calorie_today
            mysqli_query($conn, "UPDATE Users SET US_calorie_today = US_calorie_today + $totalCal WHERE US_id='$e'");

            $msg = ['type'=>'success','text'=>"✅ บันทึก \"{$mealName}\" สำเร็จ (+{$totalCal} kcal)"];
        }
    }
}

// ── POST: ลบรายการ ─────────────────────────────────
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='delete') {
    $orId = mysqli_real_escape_string($conn, $_POST['OR_id'] ?? '');
    $chk = mysqli_fetch_assoc(mysqli_query($conn, "SELECT OR_totalcalorie,OR_image FROM Orders WHERE OR_id='$orId' AND US_id='$e'"));
    if ($chk) {
        $cal = (int)$chk['OR_totalcalorie'];
        // ลบรูปไฟล์
        if (!empty($chk['OR_image'])) {
            $path = __DIR__ . '/uploads/' . $chk['OR_image'];
            if (file_exists($path)) @unlink($path);
        }
        // ลบ OrderDetail ก่อน (เพราะมี FK)
        mysqli_query($conn, "DELETE FROM OrderDetail WHERE OR_id='$orId'");
        mysqli_query($conn, "DELETE FROM Orders WHERE OR_id='$orId'");
        // ลดแคล (ไม่ติดลบ)
        mysqli_query($conn, "UPDATE Users SET US_calorie_today = GREATEST(0, US_calorie_today - $cal) WHERE US_id='$e'");
        $msg = ['type'=>'success','text'=>'✅ ลบรายการแล้ว'];
    }
}

// ── GET: ดึง user + ประวัติวันนี้ ─────────────────────
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT US_wallet,US_caloriegoal,US_calorie_today FROM Users WHERE US_id='$e'"));
$today = date('Y-m-d');
$logs = mysqli_query($conn,
    "SELECT o.OR_id, o.OR_datetime, o.OR_totalcalorie, o.OR_image,
            GROUP_CONCAT(m.MN_name SEPARATOR ', ') AS items
     FROM Orders o
     LEFT JOIN OrderDetail od ON o.OR_id=od.OR_id
     LEFT JOIN Menu m ON od.MN_id=m.MN_id
     WHERE o.US_id='$e' AND DATE(o.OR_datetime)='$today'
     GROUP BY o.OR_id
     ORDER BY o.OR_datetime DESC");

$pageTitle = "บันทึกอาหาร – RestoSys";
$activePage = "uscan";
require_once INCLUDES_PATH . '/user_header.php';
?>

<div class="main">
  <div class="page-header">
    <h2>📷 บันทึกอาหารที่ทาน</h2>
    <p>กรอกข้อมูลอาหารที่ทานพร้อมแนบรูป (ถ้าต้องการ)</p>
  </div>

  <?php if($msg): ?>
  <div class="alert alert-<?=$msg['type']?>"><?=$msg['text']?></div>
  <?php endif; ?>

  <!-- สรุปแคลวันนี้ -->
  <?php
  $calGoal = (int)$user['US_caloriegoal'];
  $calToday = (int)$user['US_calorie_today'];
  $pct = $calGoal>0 ? min(100, round($calToday/$calGoal*100)) : 0;
  $pctCls = $pct>=100?'over':($pct>=80?'warn':'');
  ?>
  <div class="card" style="margin-bottom:20px">
    <div style="display:flex;align-items:center;gap:20px;flex-wrap:wrap">
      <div style="flex:1;min-width:220px">
        <div style="font-size:0.75rem;color:var(--muted);margin-bottom:4px">🔥 แคลวันนี้</div>
        <div style="font-family:'Prompt',sans-serif;font-size:1.6rem;font-weight:800">
          <?=number_format($calToday)?> / <?=number_format($calGoal)?> kcal
        </div>
        <div class="progress-bar" style="margin-top:8px">
          <div class="progress-fill <?=$pctCls?>" style="width:<?=$pct?>%"></div>
        </div>
      </div>
      <div style="text-align:center;padding:0 20px">
        <div style="font-family:'Prompt',sans-serif;font-size:2.2rem;font-weight:800;color:<?=$pct>=100?'var(--danger)':($pct>=80?'var(--warn)':'var(--accent)')?>">
          <?=$pct?>%
        </div>
        <div style="font-size:0.7rem;color:var(--muted)">ของเป้าหมาย</div>
      </div>
    </div>
  </div>

  <!-- Form + ประวัติ -->
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:start">

    <!-- Form บันทึกอาหาร -->
    <div class="card">
      <div class="card-title">✏️ กรอกข้อมูลอาหาร</div>
      <form method="POST" enctype="multipart/form-data" id="scanForm">
        <input type="hidden" name="action" value="save">

        <!-- Preview รูป -->
        <div style="margin-bottom:14px">
          <label>รูปอาหาร (ไม่บังคับ)</label>
          <div id="preview-box" style="width:100%;height:180px;background:var(--surface2);border:2px dashed var(--border);border-radius:10px;display:flex;align-items:center;justify-content:center;cursor:pointer;overflow:hidden;margin-top:6px;transition:border-color .15s" onclick="document.getElementById('imgInput').click()">
            <div id="preview-placeholder" style="text-align:center;color:var(--muted)">
              <div style="font-size:2.2rem;margin-bottom:4px">📷</div>
              <div style="font-size:0.82rem">คลิกเพื่อเลือกรูป หรือถ่ายรูป</div>
              <div style="font-size:0.7rem;margin-top:2px">JPG, PNG, WEBP (สูงสุด 5MB)</div>
            </div>
            <img id="preview-img" style="display:none;width:100%;height:100%;object-fit:cover" alt="preview">
          </div>
          <input type="file" id="imgInput" name="food_image" accept="image/*" capture="environment" style="display:none" onchange="previewImage(this)">
          <button type="button" id="clearImgBtn" onclick="clearImage()" style="display:none;margin-top:6px;background:var(--surface2);border:1px solid var(--border);color:var(--muted);padding:5px 12px;border-radius:6px;font-size:0.78rem;cursor:pointer">🗑️ เอารูปออก</button>
        </div>

        <div class="form-group">
          <label>ชื่ออาหาร *</label>
          <input name="meal_name" required placeholder="เช่น ข้าวมันไก่, ผัดกะเพราไก่ไข่ดาว" maxlength="100">
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
          <div class="form-group">
            <label>ประเภทมื้อ</label>
            <select name="meal_type">
              <option value="breakfast">🌅 อาหารเช้า</option>
              <option value="lunch" selected>☀️ อาหารกลางวัน</option>
              <option value="dinner">🌙 อาหารเย็น</option>
              <option value="snack">🍪 ของว่าง</option>
            </select>
          </div>
          <div class="form-group">
            <label>จำนวน (จาน/แก้ว)</label>
            <input name="quantity" type="number" min="1" max="10" value="1" required>
          </div>
        </div>

        <div class="form-group">
          <label>แคลอรี่ต่อหน่วย (kcal) *</label>
          <input name="calorie" type="number" min="1" max="5000" required placeholder="เช่น 450">
        </div>

        <div style="font-size:0.72rem;color:var(--muted);margin-bottom:8px;margin-top:6px">สารอาหาร (ไม่บังคับ):</div>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;margin-bottom:16px">
          <div class="form-group" style="margin-bottom:0">
            <label style="font-size:0.72rem">โปรตีน (g)</label>
            <input name="protein" type="number" step="0.1" min="0" value="0" placeholder="0">
          </div>
          <div class="form-group" style="margin-bottom:0">
            <label style="font-size:0.72rem">คาร์บ (g)</label>
            <input name="carb" type="number" step="0.1" min="0" value="0" placeholder="0">
          </div>
          <div class="form-group" style="margin-bottom:0">
            <label style="font-size:0.72rem">ไขมัน (g)</label>
            <input name="fat" type="number" step="0.1" min="0" value="0" placeholder="0">
          </div>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:12px">
          💾 บันทึกอาหาร
        </button>
      </form>

      <!-- Quick presets -->
      <div style="margin-top:14px;padding-top:14px;border-top:1px solid var(--border)">
        <div style="font-size:0.72rem;color:var(--muted);margin-bottom:8px">⚡ กดเพื่อกรอกเร็ว:</div>
        <div style="display:flex;gap:6px;flex-wrap:wrap">
          <button type="button" onclick="applyPreset('ข้าวมันไก่',596,28,75,20)" class="preset-btn">ข้าวมันไก่ 596</button>
          <button type="button" onclick="applyPreset('ผัดกะเพราไก่ไข่ดาว',720,35,85,25)" class="preset-btn">กะเพราไก่ 720</button>
          <button type="button" onclick="applyPreset('ส้มตำไทย',120,3,25,1)" class="preset-btn">ส้มตำ 120</button>
          <button type="button" onclick="applyPreset('ก๋วยเตี๋ยวต้มยำ',450,20,60,15)" class="preset-btn">ต้มยำ 450</button>
          <button type="button" onclick="applyPreset('กาแฟดำ',5,0,1,0)" class="preset-btn">กาแฟดำ 5</button>
          <button type="button" onclick="applyPreset('ชาไทยเย็น',280,3,45,10)" class="preset-btn">ชาไทย 280</button>
        </div>
      </div>
    </div>

    <!-- ประวัติวันนี้ -->
    <div class="card">
      <div class="card-title">📋 อาหารวันนี้</div>
      <?php if(mysqli_num_rows($logs)===0): ?>
        <div style="text-align:center;color:var(--muted);padding:40px 20px">
          <div style="font-size:2.5rem;margin-bottom:10px">🍽️</div>
          <div style="font-size:0.9rem">ยังไม่มีรายการวันนี้</div>
          <div style="font-size:0.75rem;margin-top:4px">กรอกข้อมูลด้านซ้ายเพื่อบันทึก</div>
        </div>
      <?php else: ?>
        <div style="display:flex;flex-direction:column;gap:10px;max-height:560px;overflow-y:auto">
        <?php while($r = mysqli_fetch_assoc($logs)):
          $time = date('H:i', strtotime($r['OR_datetime']));
          $imgSrc = !empty($r['OR_image']) ? 'uploads/' . htmlspecialchars($r['OR_image']) : null;
        ?>
        <div style="display:flex;gap:10px;padding:10px;background:var(--surface2);border-radius:10px;border:1px solid var(--border)">
          <?php if($imgSrc): ?>
          <img src="<?=$imgSrc?>" style="width:64px;height:64px;border-radius:8px;object-fit:cover;flex-shrink:0;cursor:pointer" onclick="openImage('<?=$imgSrc?>')">
          <?php else: ?>
          <div style="width:64px;height:64px;border-radius:8px;background:var(--surface);display:flex;align-items:center;justify-content:center;font-size:1.8rem;flex-shrink:0">🍱</div>
          <?php endif; ?>
          <div style="flex:1;min-width:0">
            <div style="font-weight:700;font-size:0.88rem;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?=htmlspecialchars($r['items'] ?? '-')?></div>
            <div style="font-size:0.72rem;color:var(--muted);margin-top:2px"><?=$time?> น.</div>
            <div style="margin-top:4px"><span class="badge badge-info"><?=number_format($r['OR_totalcalorie'])?> kcal</span></div>
          </div>
          <form method="POST" onsubmit="return confirm('ลบรายการนี้?')" style="align-self:flex-start">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="OR_id" value="<?=htmlspecialchars($r['OR_id'])?>">
            <button type="submit" style="background:transparent;border:none;color:var(--muted);cursor:pointer;padding:4px;font-size:1rem" title="ลบ">🗑️</button>
          </form>
        </div>
        <?php endwhile; ?>
        </div>
      <?php endif; ?>
    </div>

  </div>
</div>

<!-- Modal ดูรูป -->
<div id="imgModal" onclick="closeImage()" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.9);z-index:9999;align-items:center;justify-content:center;padding:20px;cursor:pointer">
  <img id="modalImg" style="max-width:90vw;max-height:90vh;border-radius:12px">
</div>

<style>
.preset-btn {
  background: var(--surface2);
  border: 1px solid var(--border);
  color: var(--text);
  padding: 5px 10px;
  border-radius: 6px;
  font-family: 'Sarabun', sans-serif;
  font-size: 0.75rem;
  cursor: pointer;
  transition: all .15s;
}
.preset-btn:hover { background: rgba(249,115,22,0.15); border-color: var(--accent); color: var(--accent); }
#preview-box:hover { border-color: var(--accent) !important; }
</style>

<script>
function previewImage(input) {
  if (input.files && input.files[0]) {
    const file = input.files[0];
    if (file.size > 5 * 1024 * 1024) {
      alert('ไฟล์ใหญ่เกิน 5MB');
      input.value = '';
      return;
    }
    const reader = new FileReader();
    reader.onload = e => {
      document.getElementById('preview-img').src = e.target.result;
      document.getElementById('preview-img').style.display = 'block';
      document.getElementById('preview-placeholder').style.display = 'none';
      document.getElementById('clearImgBtn').style.display = 'inline-block';
    };
    reader.readAsDataURL(file);
  }
}
function clearImage() {
  document.getElementById('imgInput').value = '';
  document.getElementById('preview-img').style.display = 'none';
  document.getElementById('preview-img').src = '';
  document.getElementById('preview-placeholder').style.display = 'block';
  document.getElementById('clearImgBtn').style.display = 'none';
}
function applyPreset(name, cal, p, c, f) {
  document.querySelector('[name="meal_name"]').value = name;
  document.querySelector('[name="calorie"]').value = cal;
  document.querySelector('[name="protein"]').value = p;
  document.querySelector('[name="carb"]').value = c;
  document.querySelector('[name="fat"]').value = f;
}
function openImage(src) {
  document.getElementById('modalImg').src = src;
  document.getElementById('imgModal').style.display = 'flex';
}
function closeImage() {
  document.getElementById('imgModal').style.display = 'none';
}
</script>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>