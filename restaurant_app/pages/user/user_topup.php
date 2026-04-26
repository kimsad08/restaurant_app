<?php
// user_topup.php
session_start();
require_once __DIR__ . '/../../config/paths.php';
require_once INCLUDES_PATH . '/auth.php';
requireLogin('user');
require_once CONFIG_PATH . '/db.php';
$uid = $_SESSION['user_id'];
$e   = mysqli_real_escape_string($conn, $uid);
$msg = '';

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $amount = floatval($_POST['amount'] ?? 0);
    if ($amount <= 0) { $msg=['type'=>'danger','text'=>'กรุณากรอกจำนวนเงินที่ถูกต้อง']; }
    else {
        mysqli_query($conn,"UPDATE Users SET US_wallet=US_wallet+$amount WHERE US_id='$e'");
        $msg=['type'=>'success','text'=>"✅ เติมเงิน ฿".number_format($amount,2)." สำเร็จ"];
    }
}

$user = mysqli_fetch_assoc(mysqli_query($conn,"SELECT US_wallet FROM Users WHERE US_id='$e'"));
$pageTitle="เติมเงิน – RestoSys"; $activePage="utopup";
require_once INCLUDES_PATH . '/user_header.php';
?>
<div class="main">
  <div class="page-header"><h2>💳 เติมเงินกระเป๋า</h2><p>เพิ่มยอดเงินเพื่อสั่งอาหาร</p></div>
  <?php if($msg): ?><div class="alert alert-<?=$msg['type']?>"><?=$msg['text']?></div><?php endif; ?>
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;max-width:600px">
    <div class="card" style="grid-column:1/-1">
      <div style="text-align:center;padding:10px 0">
        <div style="font-size:0.8rem;color:var(--muted)">ยอดเงินปัจจุบัน</div>
        <div style="font-family:'Prompt',sans-serif;font-size:2.5rem;font-weight:800;color:var(--success)">฿<?=number_format($user['US_wallet'],2)?></div>
      </div>
    </div>
    <div class="card" style="grid-column:1/-1">
      <div class="card-title">เลือกจำนวนเงิน</div>
      <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:16px">
        <?php foreach([50,100,200,300,500,1000] as $a): ?>
        <button onclick="document.getElementById('amt').value=<?=$a?>" class="btn" style="background:var(--surface2);border:1px solid var(--border);color:var(--text);justify-content:center">฿<?=number_format($a)?></button>
        <?php endforeach; ?>
      </div>
      <form method="POST">
        <div class="form-group">
          <label>จำนวนเงิน (บาท)</label>
          <input type="number" name="amount" id="amt" min="1" step="0.01" required placeholder="กรอกจำนวน">
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">✅ เติมเงิน</button>
      </form>
    </div>
  </div>
</div>
<?php require_once INCLUDES_PATH . '/footer.php'; ?>