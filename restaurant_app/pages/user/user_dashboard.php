<?php
session_start();
require_once __DIR__ . '/../../config/paths.php';
require_once INCLUDES_PATH . '/auth.php';
requireLogin('user');
require_once CONFIG_PATH . '/db.php';

$uid = $_SESSION['user_id'];
$e   = mysqli_real_escape_string($conn, $uid);

$user = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM Users WHERE US_id='$e'"));
$_SESSION['US_wallet'] = $user['US_wallet'];

$calGoal  = (int)$user['US_caloriegoal'];
$calToday = (int)$user['US_calorie_today'];
$calPct   = $calGoal > 0 ? min(100, round($calToday/$calGoal*100)) : 0;
$calClass = $calPct >= 100 ? 'over' : ($calPct >= 80 ? 'warn' : '');

// สถิติ
$totalOrders = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c, COALESCE(SUM(OR_totalprice),0) s, COALESCE(SUM(OR_totalcalorie),0) cal FROM Orders WHERE US_id='$e'"));

// history ล่าสุด 5 รายการ
$recent = mysqli_query($conn,"SELECT o.OR_id, o.OR_datetime, o.OR_totalprice, o.OR_totalcalorie FROM Orders o WHERE o.US_id='$e' ORDER BY o.OR_datetime DESC LIMIT 5");

// top เมนูที่สั่งบ่อย
$topMenu = mysqli_query($conn,"SELECT m.MN_name, SUM(od.ORT_quantity) qty FROM OrderDetail od JOIN Orders o ON od.OR_id=o.OR_id JOIN Menu m ON od.MN_id=m.MN_id WHERE o.US_id='$e' GROUP BY od.MN_id ORDER BY qty DESC LIMIT 3");

$pageTitle = "Dashboard – RestoSys";
$activePage = "udash";
require_once INCLUDES_PATH . '/user_header.php';
?>

<div class="main">
  <div class="page-header">
    <h2>👋 สวัสดี, <?=htmlspecialchars($user['US_name'])?>!</h2>
    <p>ภาพรวมการรับประทานอาหารและการใช้จ่ายของคุณ</p>
  </div>

  <!-- Stats -->
  <div class="stats-row">
    <div class="stat-card">
      <div class="label">💳 เงินคงเหลือ</div>
      <div class="value" style="color:var(--success);font-size:1.5rem">฿<?=number_format($user['US_wallet'],2)?></div>
      <div class="sub">กระเป๋าเงิน</div>
    </div>
    <div class="stat-card">
      <div class="label">🔥 แคลวันนี้</div>
      <div class="value" style="font-size:1.5rem;color:<?=$calPct>=100?'var(--danger)':($calPct>=80?'var(--warn)':'var(--text)')?>">
        <?=number_format($calToday)?>
      </div>
      <div class="sub">/ <?=number_format($calGoal)?> kcal เป้าหมาย</div>
      <div class="progress-bar"><div class="progress-fill <?=$calClass?>" style="width:<?=$calPct?>%"></div></div>
    </div>
    <div class="stat-card">
      <div class="label">📋 คำสั่งซื้อทั้งหมด</div>
      <div class="value"><?=$totalOrders['c']?></div>
      <div class="sub">รายการที่สั่ง</div>
    </div>
    <div class="stat-card">
      <div class="label">💰 ยอดใช้จ่ายรวม</div>
      <div class="value" style="font-size:1.4rem">฿<?=number_format($totalOrders['s'],0)?></div>
      <div class="sub">บาท ตลอดการใช้งาน</div>
    </div>
    <div class="stat-card">
      <div class="label">🔥 แคลรวมทั้งหมด</div>
      <div class="value" style="font-size:1.4rem"><?=number_format($totalOrders['cal'])?></div>
      <div class="sub">kcal ตลอดการใช้งาน</div>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
    <!-- แคลวันนี้ gauge -->
    <div class="card">
      <div class="card-title">🎯 เป้าหมายแคลวันนี้</div>
      <?php
      $remaining = max(0, $calGoal - $calToday);
      $over      = max(0, $calToday - $calGoal);
      ?>
      <div style="text-align:center;padding:10px 0">
        <div style="font-size:3rem;font-family:'Prompt',sans-serif;font-weight:800;color:<?=$calPct>=100?'var(--danger)':($calPct>=80?'var(--warn)':'var(--accent)')?>">
          <?=$calPct?>%
        </div>
        <div style="color:var(--muted);font-size:0.85rem;margin-top:4px">ของเป้าหมายวันนี้</div>
        <div class="progress-bar" style="margin:14px 0;height:12px">
          <div class="progress-fill <?=$calClass?>" style="width:<?=$calPct?>%"></div>
        </div>
        <?php if($over > 0): ?>
        <div style="color:var(--danger);font-size:0.85rem">⚠️ เกินเป้าหมาย <?=number_format($over)?> kcal</div>
        <?php else: ?>
        <div style="color:var(--success);font-size:0.85rem">✅ เหลืออีก <?=number_format($remaining)?> kcal</div>
        <?php endif; ?>
      </div>
      <div style="display:flex;justify-content:space-around;margin-top:12px;padding-top:12px;border-top:1px solid var(--border)">
        <div style="text-align:center">
          <div style="font-size:0.7rem;color:var(--muted)">รับแล้ว</div>
          <div style="font-family:'Prompt',sans-serif;font-weight:700;color:var(--accent)"><?=number_format($calToday)?></div>
          <div style="font-size:0.68rem;color:var(--muted)">kcal</div>
        </div>
        <div style="text-align:center">
          <div style="font-size:0.7rem;color:var(--muted)">เป้าหมาย</div>
          <div style="font-family:'Prompt',sans-serif;font-weight:700;color:var(--text)"><?=number_format($calGoal)?></div>
          <div style="font-size:0.68rem;color:var(--muted)">kcal</div>
        </div>
      </div>
    </div>

    <!-- เมนูที่สั่งบ่อย -->
    <div class="card">
      <div class="card-title">⭐ เมนูที่สั่งบ่อย</div>
      <?php if(mysqli_num_rows($topMenu)===0): ?>
      <div style="text-align:center;color:var(--muted);padding:30px 0;font-size:0.85rem">ยังไม่มีประวัติการสั่ง<br><a href="<?= URL_USER_SHOP ?>" style="color:var(--accent);font-weight:600">เริ่มสั่งอาหาร →</a></div>
      <?php else: $i=1; while($m=mysqli_fetch_assoc($topMenu)): ?>
      <div style="display:flex;align-items:center;gap:12px;padding:10px 0;<?=$i<3?'border-bottom:1px solid var(--border)':''?>">
        <div style="width:28px;height:28px;border-radius:50%;background:var(--surface2);display:flex;align-items:center;justify-content:center;font-size:0.75rem;font-weight:700;color:var(--accent);flex-shrink:0"><?=$i?></div>
        <div style="flex:1">
          <div style="font-weight:600;font-size:0.88rem"><?=htmlspecialchars($m['MN_name'])?></div>
        </div>
        <span class="badge badge-info"><?=$m['qty']?> ครั้ง</span>
      </div>
      <?php $i++; endwhile; endif; ?>
      <div style="margin-top:14px">
        <a href="<?= URL_USER_SHOP ?>" class="btn btn-primary" style="width:100%;justify-content:center">🛒 สั่งอาหาร</a>
      </div>
    </div>
  </div>

  <!-- คำสั่งซื้อล่าสุด -->
  <div class="card">
    <div class="card-title" style="justify-content:space-between">
      <span>📋 คำสั่งซื้อล่าสุด</span>
      <a href="<?= URL_USER_HISTORY ?>" style="font-size:0.78rem;color:var(--accent);text-decoration:none">ดูทั้งหมด →</a>
    </div>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Order ID</th><th>วันที่/เวลา</th><th>ราคารวม</th><th>แคลอรี่</th></tr></thead>
        <tbody>
        <?php if(mysqli_num_rows($recent)===0): ?>
          <tr><td colspan="4" style="text-align:center;color:var(--muted);padding:24px">ยังไม่มีคำสั่งซื้อ</td></tr>
        <?php else: while($r=mysqli_fetch_assoc($recent)): ?>
          <tr>
            <td><code style="color:var(--accent);font-size:0.78rem"><?=htmlspecialchars($r['OR_id'])?></code></td>
            <td style="font-size:0.82rem;color:var(--muted)"><?=htmlspecialchars($r['OR_datetime'])?></td>
            <td><strong>฿<?=number_format($r['OR_totalprice'],2)?></strong></td>
            <td><span class="badge badge-info"><?=number_format($r['OR_totalcalorie'])?> kcal</span></td>
          </tr>
        <?php endwhile; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>