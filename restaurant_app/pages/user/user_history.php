<?php
// user_history.php
session_start();
require_once __DIR__ . '/../../config/paths.php';
require_once INCLUDES_PATH . '/auth.php';
requireLogin('user');
require_once CONFIG_PATH . '/db.php';
$uid = $_SESSION['user_id'];
$e   = mysqli_real_escape_string($conn, $uid);

$orders = mysqli_query($conn,
  "SELECT o.*,GROUP_CONCAT(CONCAT(m.MN_name,'×',od.ORT_quantity) SEPARATOR ', ') AS items
   FROM Orders o
   LEFT JOIN OrderDetail od ON o.OR_id=od.OR_id
   LEFT JOIN Menu m ON od.MN_id=m.MN_id
   WHERE o.US_id='$e'
   GROUP BY o.OR_id ORDER BY o.OR_datetime DESC");

$pageTitle="ประวัติการสั่ง – RestoSys"; $activePage="uhistory";
require_once INCLUDES_PATH . '/user_header.php';
?>
<div class="main">
  <div class="page-header"><h2>📋 ประวัติการสั่งอาหาร</h2><p>รายการทั้งหมดที่คุณเคยสั่ง</p></div>
  <div class="card">
    <div class="table-wrap">
      <table>
        <thead><tr><th>Order ID</th><th>รายการ</th><th>วันที่/เวลา</th><th>ราคารวม</th><th>แคลอรี่</th></tr></thead>
        <tbody>
        <?php if(mysqli_num_rows($orders)===0): ?>
          <tr><td colspan="5" style="text-align:center;color:var(--muted);padding:30px">ยังไม่มีประวัติ <a href="<?= URL_USER_SHOP ?>" style="color:var(--accent)">เริ่มสั่งอาหาร →</a></td></tr>
        <?php else: while($r=mysqli_fetch_assoc($orders)): ?>
          <tr>
            <td><code style="color:var(--accent);font-size:0.78rem"><?=htmlspecialchars($r['OR_id'])?></code></td>
            <td style="font-size:0.82rem;color:var(--muted);max-width:240px"><?=htmlspecialchars($r['items']??'-')?></td>
            <td style="font-size:0.8rem"><?=htmlspecialchars($r['OR_datetime'])?></td>
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