<?php
session_start();
require_once __DIR__ . '/../../config/paths.php';
require_once INCLUDES_PATH . '/auth.php';
requireLogin('user');
require_once CONFIG_PATH . '/db.php';

$uid = $_SESSION['user_id'];
$e   = mysqli_real_escape_string($conn, $uid);
$msg = '';

// ---- CHECKOUT ----
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='checkout') {
    $cart = json_decode($_POST['cart_data'] ?? '[]', true);
    if (empty($cart)) { $msg=['type'=>'danger','text'=>'ตะกร้าว่างเปล่า']; }
    else {
        $user = mysqli_fetch_assoc(mysqli_query($conn,"SELECT US_wallet,US_calorie_today FROM Users WHERE US_id='$e'"));
        $total_price = 0; $total_cal = 0;
        foreach($cart as $item) {
            $total_price += floatval($item['price']) * intval($item['qty']);
            $total_cal   += intval($item['calorie']) * intval($item['qty']);
        }
        if ($user['US_wallet'] < $total_price) {
            $msg = ['type'=>'danger','text'=>'❌ เงินไม่เพียงพอ กรุณาเติมเงินก่อน (มี ฿'.number_format($user['US_wallet'],2).' ต้องการ ฿'.number_format($total_price,2).')'];
        } else {
            // สร้าง Order
            $or_id = 'OR' . date('ymdHis') . rand(10,99);
            $dt    = date('Y-m-d H:i:s');
            mysqli_query($conn,"INSERT INTO Orders(OR_id,US_id,OR_datetime,OR_totalprice,OR_totalcalorie)VALUES('$or_id','$e','$dt','$total_price','$total_cal')");
            // สร้าง OrderDetail
            foreach($cart as $i => $item) {
                $ort_id = 'ORT'.date('ymdHis').str_pad($i,3,'0',STR_PAD_LEFT);
                $mn_id  = mysqli_real_escape_string($conn, $item['mn_id']);
                $qty    = (int)$item['qty'];
                $ppu    = floatval($item['price']);
                mysqli_query($conn,"INSERT INTO OrderDetail(ORT_id,OR_id,MN_id,ORT_quantity,ORT_priceperunit)VALUES('$ort_id','$or_id','$mn_id','$qty','$ppu')");
            }
            // หักเงิน + เพิ่มแคล
            $new_wallet = $user['US_wallet'] - $total_price;
            $new_cal    = $user['US_calorie_today'] + $total_cal;
            mysqli_query($conn,"UPDATE Users SET US_wallet='$new_wallet', US_calorie_today='$new_cal' WHERE US_id='$e'");
            $_SESSION['US_wallet'] = $new_wallet;
            $msg = ['type'=>'success','text'=>"✅ สั่งอาหารสำเร็จ! Order: $or_id | จ่าย ฿".number_format($total_price,2)." | แคล ".number_format($total_cal)." kcal"];
        }
    }
}

// ดึงข้อมูล
$user    = mysqli_fetch_assoc(mysqli_query($conn,"SELECT US_wallet,US_caloriegoal,US_calorie_today FROM Users WHERE US_id='$e'"));
$rests   = mysqli_query($conn,"SELECT RS_id,RS_name,RS_description FROM Restaurant ORDER BY RS_name");
$selected_rs = $_GET['rs'] ?? '';

$menus = null;
$rs_info = null;
if ($selected_rs) {
    $rs_e = mysqli_real_escape_string($conn, $selected_rs);
    $rs_info = mysqli_fetch_assoc(mysqli_query($conn,"SELECT r.*,m.MG_name FROM Restaurant r LEFT JOIN Manager m ON r.MG_id=m.MG_id WHERE r.RS_id='$rs_e'"));
    $menus = mysqli_query($conn,
        "SELECT mn.*,COALESCE(n.NT_calorie,0) AS NT_calorie,COALESCE(n.NT_protein,0) AS NT_protein,COALESCE(n.NT_carb,0) AS NT_carb,COALESCE(n.NT_fat,0) AS NT_fat
         FROM Menu mn LEFT JOIN Nutrition n ON mn.MN_id=n.MN_id
         WHERE mn.RS_id='$rs_e' AND mn.MN_status='available' ORDER BY mn.MN_name");
}

$pageTitle = "สั่งอาหาร – RestoSys";
$activePage = "ushop";
require_once INCLUDES_PATH . '/user_header.php';
?>

<div class="main">
  <div class="page-header">
    <h2>🛒 สั่งอาหาร</h2>
    <p>เลือกร้านอาหารและเมนูที่ต้องการ</p>
  </div>

  <?php if($msg): ?>
  <div class="alert alert-<?=$msg['type']?>"><?=$msg['text']?></div>
  <?php endif; ?>

  <!-- Wallet + Cal bar -->
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:20px">
    <div class="stat-card" style="display:flex;align-items:center;gap:14px;padding:14px 18px">
      <div style="font-size:1.8rem">💳</div>
      <div>
        <div style="font-size:0.7rem;color:var(--muted)">เงินคงเหลือ</div>
        <div style="font-family:'Prompt',sans-serif;font-size:1.3rem;font-weight:700;color:var(--success)">฿<?=number_format($user['US_wallet'],2)?></div>
      </div>
    </div>
    <div class="stat-card" style="display:flex;align-items:center;gap:14px;padding:14px 18px">
      <div style="font-size:1.8rem">🔥</div>
      <div style="flex:1">
        <div style="font-size:0.7rem;color:var(--muted)">แคลวันนี้</div>
        <div style="font-family:'Prompt',sans-serif;font-size:1.1rem;font-weight:700"><?=number_format($user['US_calorie_today'])?> / <?=number_format($user['US_caloriegoal'])?> kcal</div>
        <?php $p=min(100,round($user['US_calorie_today']/max(1,$user['US_caloriegoal'])*100)); ?>
        <div class="progress-bar" style="margin-top:5px"><div class="progress-fill <?=$p>=100?'over':($p>=80?'warn':'')?>" style="width:<?=$p?>%"></div></div>
      </div>
    </div>
  </div>

  <!-- Restaurant list -->
  <div class="card">
    <div class="card-title">🏪 เลือกร้านอาหาร</div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px">
      <?php mysqli_data_seek($rests,0); while($r=mysqli_fetch_assoc($rests)): ?>
      <a href="<?= URL_USER_SHOP ?>?rs=<?=urlencode($r['RS_id'])?>" style="text-decoration:none">
        <div style="background:var(--surface2);border:2px solid <?=$selected_rs===$r['RS_id']?'var(--accent)':'var(--border)'?>;border-radius:10px;padding:16px;cursor:pointer;transition:all .15s;<?=$selected_rs===$r['RS_id']?'background:rgba(249,115,22,0.08)':''?>">
          <div style="font-size:1.5rem;margin-bottom:6px">🏪</div>
          <div style="font-weight:700;font-size:0.9rem;color:var(--text)"><?=htmlspecialchars($r['RS_name'])?></div>
          <div style="font-size:0.75rem;color:var(--muted);margin-top:3px"><?=htmlspecialchars(mb_strimwidth($r['RS_description']??'',0,40,'...'))?></div>
        </div>
      </a>
      <?php endwhile; ?>
    </div>
  </div>

  <?php if($rs_info && $menus): ?>
  <!-- Menu + Cart -->
  <div style="display:grid;grid-template-columns:1fr 340px;gap:20px;align-items:start">
    <!-- Menu list -->
    <div class="card">
      <div class="card-title">🍱 เมนูจาก <?=htmlspecialchars($rs_info['RS_name'])?></div>
      <?php if(mysqli_num_rows($menus)===0): ?>
      <div style="text-align:center;color:var(--muted);padding:30px">ยังไม่มีเมนูในร้านนี้</div>
      <?php else: ?>
      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px">
        <?php while($m=mysqli_fetch_assoc($menus)): ?>
        <div class="menu-item" style="background:var(--surface2);border:1px solid var(--border);border-radius:10px;padding:14px;transition:all .15s"
             data-id="<?=htmlspecialchars($m['MN_id'])?>"
             data-name="<?=htmlspecialchars($m['MN_name'])?>"
             data-price="<?=htmlspecialchars($m['MN_price'])?>"
             data-cal="<?=htmlspecialchars($m['NT_calorie'])?>">
          <div style="font-size:1.6rem;margin-bottom:8px">🍱</div>
          <div style="font-weight:700;font-size:0.88rem"><?=htmlspecialchars($m['MN_name'])?></div>
          <div style="display:flex;gap:6px;margin:6px 0;flex-wrap:wrap">
            <span class="badge badge-success">฿<?=number_format($m['MN_price'],2)?></span>
            <span class="badge badge-info"><?=number_format($m['NT_calorie'])?> kcal</span>
          </div>
          <?php if($m['NT_protein']>0): ?>
          <div style="font-size:0.7rem;color:var(--muted)">P:<?=$m['NT_protein']?>g C:<?=$m['NT_carb']?>g F:<?=$m['NT_fat']?>g</div>
          <?php endif; ?>
          <button class="btn btn-primary btn-sm" style="width:100%;margin-top:10px;justify-content:center" onclick="addToCart(this)">+ เพิ่มลงตะกร้า</button>
        </div>
        <?php endwhile; ?>
      </div>
      <?php endif; ?>
    </div>

    <!-- Cart -->
    <div class="card" style="position:sticky;top:20px">
      <div class="card-title">🛒 ตะกร้าสินค้า</div>
      <div id="cart-items">
        <div style="text-align:center;color:var(--muted);padding:20px;font-size:0.85rem" id="cart-empty">ยังไม่มีรายการ</div>
      </div>
      <div id="cart-summary" style="display:none;border-top:1px solid var(--border);padding-top:14px;margin-top:4px">
        <div style="display:flex;justify-content:space-between;margin-bottom:6px;font-size:0.85rem">
          <span style="color:var(--muted)">ราคารวม</span>
          <strong id="cart-total-price">฿0.00</strong>
        </div>
        <div style="display:flex;justify-content:space-between;margin-bottom:14px;font-size:0.85rem">
          <span style="color:var(--muted)">แคลรวม</span>
          <span class="badge badge-info" id="cart-total-cal">0 kcal</span>
        </div>
        <form method="POST" id="checkout-form">
          <input type="hidden" name="action" value="checkout">
          <input type="hidden" name="cart_data" id="cart-data-input">
          <button type="button" class="btn btn-primary" style="width:100%;justify-content:center;font-size:0.95rem" onclick="doCheckout()">✅ ยืนยันการสั่ง</button>
        </form>
        <button onclick="clearCart()" class="btn btn-danger btn-sm" style="width:100%;justify-content:center;margin-top:8px">🗑️ ล้างตะกร้า</button>
      </div>
    </div>
  </div>
  <?php endif; ?>
</div>

<script>
let cart = {};

function addToCart(btn) {
  const item = btn.closest('.menu-item');
  const id    = item.dataset.id;
  const name  = item.dataset.name;
  const price = parseFloat(item.dataset.price);
  const cal   = parseInt(item.dataset.cal);
  if (cart[id]) cart[id].qty++;
  else cart[id] = { mn_id:id, name, price, calorie:cal, qty:1 };
  renderCart();
}

function renderCart() {
  const items = Object.values(cart);
  const el = document.getElementById('cart-items');
  const empty = document.getElementById('cart-empty');
  const summary = document.getElementById('cart-summary');
  if (items.length === 0) {
    el.innerHTML = '<div style="text-align:center;color:var(--muted);padding:20px;font-size:0.85rem">ยังไม่มีรายการ</div>';
    summary.style.display = 'none'; return;
  }
  empty && (empty.style.display='none');
  summary.style.display = 'block';
  let html = '';
  let totalP = 0, totalC = 0;
  items.forEach(it => {
    const sub = it.price * it.qty;
    totalP += sub; totalC += it.calorie * it.qty;
    html += `<div style="display:flex;align-items:center;gap:8px;padding:8px 0;border-bottom:1px solid var(--border)">
      <div style="flex:1;font-size:0.82rem"><strong>${it.name}</strong><br><span style="color:var(--muted)">฿${it.price.toFixed(2)} × ${it.qty}</span></div>
      <div style="text-align:right">
        <div style="font-size:0.85rem;font-weight:700">฿${sub.toFixed(2)}</div>
        <div style="display:flex;gap:4px;margin-top:4px">
          <button onclick="changeQty('${it.mn_id}',-1)" style="width:24px;height:24px;border-radius:5px;background:var(--surface2);border:1px solid var(--border);color:var(--text);cursor:pointer;font-size:0.9rem">−</button>
          <button onclick="changeQty('${it.mn_id}',1)" style="width:24px;height:24px;border-radius:5px;background:var(--surface2);border:1px solid var(--border);color:var(--text);cursor:pointer;font-size:0.9rem">+</button>
        </div>
      </div>
    </div>`;
  });
  el.innerHTML = html;
  document.getElementById('cart-total-price').textContent = '฿' + totalP.toFixed(2);
  document.getElementById('cart-total-cal').textContent = totalC.toLocaleString() + ' kcal';
}

function changeQty(id, d) {
  if (!cart[id]) return;
  cart[id].qty += d;
  if (cart[id].qty <= 0) delete cart[id];
  renderCart();
}

function clearCart() { cart = {}; renderCart(); }

function doCheckout() {
  const items = Object.values(cart);
  if (items.length === 0) { alert('กรุณาเลือกเมนูก่อน'); return; }
  document.getElementById('cart-data-input').value = JSON.stringify(items);
  document.getElementById('checkout-form').submit();
}
</script>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>