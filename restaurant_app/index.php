<?php
$pageTitle = "Dashboard – RestoSys";
$activePage = "dashboard";
require_once __DIR__ . '/config/paths.php';
require_once CONFIG_PATH . '/db.php';
require_once INCLUDES_PATH . '/auth.php';
requireLogin('admin');
require_once INCLUDES_PATH . '/header.php';

$counts = [];
foreach (['Admin','Manager','Restaurant','Menu','Users','Orders'] as $t) {
    $r = mysqli_query($conn, "SELECT COUNT(*) as c FROM `$t`");
    $counts[$t] = mysqli_fetch_assoc($r)['c'];
}
$revenue = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COALESCE(SUM(OR_totalprice),0) as total FROM Orders"))['total'];
$recentOrders = mysqli_query($conn,
  "SELECT o.OR_id, u.US_name, o.OR_datetime, o.OR_totalprice, o.OR_totalcalorie
   FROM Orders o JOIN Users u ON o.US_id=u.US_id
   ORDER BY o.OR_datetime DESC LIMIT 8");
?>

<div class="main">
  <div class="page-header">
    <h2>📊 Dashboard</h2>
    <p>ภาพรวมระบบจัดการร้านอาหาร</p>
  </div>

  <div class="stats-row">
    <?php
    $stats = [
      ['👑','Admin',$counts['Admin'],'ผู้ดูแลระบบ'],
      ['🧑‍💼','Manager',$counts['Manager'],'ผู้จัดการ'],
      ['🏪','ร้านอาหาร',$counts['Restaurant'],'ร้านทั้งหมด'],
      ['🍱','เมนู',$counts['Menu'],'รายการอาหาร'],
      ['🎓','นักศึกษา',$counts['Users'],'ผู้ใช้งาน'],
      ['📋','Orders',$counts['Orders'],'คำสั่งซื้อ'],
      ['💰','รายได้','฿'.number_format($counts['Orders']?$revenue:0,2),'บาท รวมทั้งหมด'],
    ];
    foreach($stats as $s): ?>
    <div class="stat-card">
      <div class="label"><?=$s[0]?> <?=$s[1]?></div>
      <div class="value"><?=$s[2]?></div>
      <div class="sub"><?=$s[3]?></div>
    </div>
    <?php endforeach; ?>
  </div>

  <div class="card">
    <div class="card-title">📋 คำสั่งซื้อล่าสุด</div>
    <div class="table-wrap">
      <table>
        <thead><tr>
          <th>Order ID</th><th>ชื่อนักศึกษา</th><th>วันที่/เวลา</th>
          <th>ราคารวม</th><th>แคลอรี่รวม</th>
        </tr></thead>
        <tbody>
        <?php if(mysqli_num_rows($recentOrders)===0): ?>
          <tr><td colspan="5" style="text-align:center;color:var(--muted);padding:30px">ยังไม่มีคำสั่งซื้อ</td></tr>
        <?php else: while($row=mysqli_fetch_assoc($recentOrders)): ?>
          <tr>
            <td><span class="badge badge-info"><?=htmlspecialchars($row['OR_id'])?></span></td>
            <td><?=htmlspecialchars($row['US_name'])?></td>
            <td><?=htmlspecialchars($row['OR_datetime'])?></td>
            <td>฿<?=number_format($row['OR_totalprice'],2)?></td>
            <td><?=number_format($row['OR_totalcalorie'])?> kcal</td>
          </tr>
        <?php endwhile; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>