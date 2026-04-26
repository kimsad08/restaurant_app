<?php
session_start();
require_once __DIR__ . '/../../config/paths.php';
require_once INCLUDES_PATH . '/auth.php';
requireLogin('admin');
require_once CONFIG_PATH . '/db.php';

// Analytics data
$totalUsers    = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM Users"))['c'];
$totalRestaurants = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM Restaurant"))['c'];
$totalOrders   = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM Orders"))['c'];
$totalRevenue  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COALESCE(SUM(OR_totalprice),0) t FROM Orders"))['t'];
$totalCalories = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COALESCE(SUM(OR_totalcalorie),0) t FROM Orders"))['t'];

// เมนูขายดีที่สุด (top 10)
$topMenus = mysqli_query($conn,
  "SELECT m.MN_name, r.RS_name, SUM(od.ORT_quantity) qty, SUM(od.ORT_quantity*od.ORT_priceperunit) rev
   FROM OrderDetail od JOIN Menu m ON od.MN_id=m.MN_id JOIN Restaurant r ON m.RS_id=r.RS_id
   GROUP BY od.MN_id ORDER BY qty DESC LIMIT 10");

// ร้านที่มีคนซื้อเยอะสุด
$topRestaurants = mysqli_query($conn,
  "SELECT r.RS_name, COUNT(DISTINCT o.OR_id) orders, SUM(od.ORT_quantity) items, SUM(od.ORT_quantity*od.ORT_priceperunit) rev
   FROM Restaurant r JOIN Menu m ON r.RS_id=m.RS_id JOIN OrderDetail od ON m.MN_id=od.MN_id JOIN Orders o ON od.OR_id=o.OR_id
   GROUP BY r.RS_id ORDER BY orders DESC LIMIT 10");

// แคลลูกค้าแต่ละคน
$userCals = mysqli_query($conn,
  "SELECT u.US_name, u.US_studentID, u.US_caloriegoal, u.US_calorie_today,
          COALESCE(SUM(o.OR_totalcalorie),0) total_cal_all
   FROM Users u LEFT JOIN Orders o ON u.US_id=o.US_id
   GROUP BY u.US_id ORDER BY total_cal_all DESC LIMIT 20");

// 7 วันล่าสุด
$daily = mysqli_query($conn,
  "SELECT DATE(OR_datetime) d, COUNT(*) orders, SUM(OR_totalprice) rev, SUM(OR_totalcalorie) cal
   FROM Orders WHERE OR_datetime >= DATE_SUB(NOW(),INTERVAL 7 DAY)
   GROUP BY DATE(OR_datetime) ORDER BY d");

$pageTitle = "Analytics – RestoSys";
$activePage = "analytics";
require_once INCLUDES_PATH . '/header.php';
?>

<div class="main">
  <div class="page-header">
    <h2>📈 Analytics & รายงาน</h2>
    <p>ภาพรวมการใช้งานระบบทั้งหมด</p>
  </div>

  <div class="stats-row">
    <div class="stat-card"><div class="label">🎓 นักศึกษา</div><div class="value"><?=$totalUsers?></div><div class="sub">ผู้ใช้งาน</div></div>
    <div class="stat-card"><div class="label">🏪 ร้านอาหาร</div><div class="value"><?=$totalRestaurants?></div><div class="sub">ร้านทั้งหมด</div></div>
    <div class="stat-card"><div class="label">📋 คำสั่งซื้อ</div><div class="value"><?=number_format($totalOrders)?></div><div class="sub">ออเดอร์รวม</div></div>
    <div class="stat-card"><div class="label">💰 รายได้รวม</div><div class="value" style="font-size:1.4rem">฿<?=number_format($totalRevenue,0)?></div><div class="sub">บาท</div></div>
    <div class="stat-card"><div class="label">🔥 แคลรวมทั้งระบบ</div><div class="value" style="font-size:1.3rem"><?=number_format($totalCalories)?></div><div class="sub">kcal ทั้งหมด</div></div>
  </div>

  <!-- 7 วันล่าสุด -->
  <div class="card">
    <div class="card-title">📅 สรุป 7 วันล่าสุด</div>
    <div class="table-wrap">
      <table>
        <thead><tr><th>วันที่</th><th>ออเดอร์</th><th>รายได้</th><th>แคลรวม</th></tr></thead>
        <tbody>
        <?php if(mysqli_num_rows($daily)===0): ?>
          <tr><td colspan="4" style="text-align:center;color:var(--muted);padding:20px">ไม่มีข้อมูล</td></tr>
        <?php else: while($d=mysqli_fetch_assoc($daily)): ?>
          <tr>
            <td><?=htmlspecialchars($d['d'])?></td>
            <td><span class="badge badge-info"><?=$d['orders']?> ออเดอร์</span></td>
            <td><strong>฿<?=number_format($d['rev'],2)?></strong></td>
            <td><?=number_format($d['cal'])?> kcal</td>
          </tr>
        <?php endwhile; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
    <!-- เมนูขายดี -->
    <div class="card">
      <div class="card-title">🏆 เมนูขายดีสุด (Top 10)</div>
      <div class="table-wrap">
        <table>
          <thead><tr><th>#</th><th>ชื่อเมนู</th><th>ร้าน</th><th>จำนวน</th><th>รายได้</th></tr></thead>
          <tbody>
          <?php $i=1; while($m=mysqli_fetch_assoc($topMenus)): ?>
            <tr>
              <td><span style="color:<?=$i<=3?'var(--accent)':'var(--muted)'?>;font-weight:700"><?=$i?></span></td>
              <td><strong><?=htmlspecialchars($m['MN_name'])?></strong></td>
              <td style="font-size:0.8rem;color:var(--muted)"><?=htmlspecialchars($m['RS_name'])?></td>
              <td><span class="badge badge-success"><?=$m['qty']?></span></td>
              <td style="color:var(--success)">฿<?=number_format($m['rev'],0)?></td>
            </tr>
          <?php $i++; endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ร้านขายดี -->
    <div class="card">
      <div class="card-title">🏪 ร้านที่มีคนซื้อเยอะสุด</div>
      <div class="table-wrap">
        <table>
          <thead><tr><th>#</th><th>ร้าน</th><th>ออเดอร์</th><th>รายได้</th></tr></thead>
          <tbody>
          <?php $i=1; while($r=mysqli_fetch_assoc($topRestaurants)): ?>
            <tr>
              <td><span style="color:<?=$i<=3?'var(--accent)':'var(--muted)'?>;font-weight:700"><?=$i?></span></td>
              <td><strong><?=htmlspecialchars($r['RS_name'])?></strong></td>
              <td><span class="badge badge-info"><?=$r['orders']?></span></td>
              <td style="color:var(--success)">฿<?=number_format($r['rev'],0)?></td>
            </tr>
          <?php $i++; endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- แคลลูกค้า -->
  <div class="card">
    <div class="card-title">🔥 แคลอรี่นักศึกษา (Top 20)</div>
    <div class="table-wrap">
      <table>
        <thead><tr><th>รหัสนักศึกษา</th><th>ชื่อ</th><th>เป้าหมาย/วัน</th><th>แคลวันนี้</th><th>%วันนี้</th><th>แคลสะสมทั้งหมด</th></tr></thead>
        <tbody>
        <?php while($u=mysqli_fetch_assoc($userCals)):
          $pct = $u['US_caloriegoal']>0 ? round($u['US_calorie_today']/$u['US_caloriegoal']*100) : 0;
          $cls = $pct>=100?'badge-danger':($pct>=80?'badge-warn':'badge-success');
        ?>
          <tr>
            <td style="font-size:0.8rem;color:var(--muted)"><?=htmlspecialchars($u['US_studentID'])?></td>
            <td><?=htmlspecialchars($u['US_name'])?></td>
            <td><?=number_format($u['US_caloriegoal'])?> kcal</td>
            <td><?=number_format($u['US_calorie_today'])?> kcal</td>
            <td><span class="badge <?=$cls?>"><?=$pct?>%</span></td>
            <td><strong><?=number_format($u['total_cal_all'])?></strong> kcal</td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
function openModal(id){document.getElementById(id).classList.add('open')}
function closeModal(id){document.getElementById(id).classList.remove('open')}
let pendingDeleteForm=null;
function confirmDelete(formId){pendingDeleteForm=document.getElementById(formId);document.getElementById('confirmOverlay').classList.add('open')}
function doDelete(){if(pendingDeleteForm)pendingDeleteForm.submit()}
function cancelDelete(){document.getElementById('confirmOverlay').classList.remove('open');pendingDeleteForm=null}
</script>
<div class="confirm-overlay" id="confirmOverlay">
  <div class="confirm-box">
    <div class="confirm-icon">🗑️</div>
    <div class="confirm-title">ยืนยันการลบ</div>
    <div class="confirm-text">ข้อมูลที่ลบแล้วไม่สามารถกู้คืนได้</div>
    <div class="confirm-btns">
      <button class="btn btn-danger" onclick="doDelete()">ลบเลย</button>
      <button class="btn" style="background:var(--surface2);color:var(--text)" onclick="cancelDelete()">ยกเลิก</button>
    </div>
  </div>
</div>
<?php require_once INCLUDES_PATH . '/footer.php'; ?>