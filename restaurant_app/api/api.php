<?php
// ============================================================
// api.php — Calorie Tracker API สำหรับ Flutter
// รัน: http://localhost:8081/api.php?action=...
// ============================================================
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);

// ── CONFIG ──────────────────────────────────────────────────
define('DB_HOST', 'db');
define('DB_USER', 'admin');
define('DB_PASS', 'password123');
define('DB_NAME', 'my_project');
define('DB_PORT', 3306);

// ⚠️ ใส่ Claude API key ของคุณตรงนี้ (หรือดึงจาก env)
define('CLAUDE_API_KEY', getenv('CLAUDE_API_KEY') ?: 'sk-ant-xxxxxxxxxxxxxxxxxxxx');

define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// ── DATABASE ─────────────────────────────────────────────────
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host='.DB_HOST.';port='.DB_PORT.';dbname='.DB_NAME.';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
    return $pdo;
}

// ── ROUTER ───────────────────────────────────────────────────
$action = $_GET['action'] ?? $_POST['action'] ?? '';
try {
    switch ($action) {
        case 'login':          handleLogin();         break;
        case 'get_user':       handleGetUser();       break;
        case 'scan_food':      handleScanFood();      break;
        case 'search_food':    handleSearchFood();    break;
        case 'save_meal':      handleSaveMeal();      break;
        case 'get_logs':       handleGetLogs();       break;
        case 'delete_log':     handleDeleteLog();     break;
        case 'daily_summary':  handleDailySummary();  break;
        case 'weekly_chart':   handleWeeklyChart();   break;
        case 'ping':           respond(200, 'pong');  break;
        default: respond(400, 'Unknown action: ' . $action);
    }
} catch (Exception $e) {
    respond(500, 'Server error: ' . $e->getMessage());
}

// ============================================================
// 1. LOGIN
// ============================================================
function handleLogin(): void {
    $data = jsonBody(['email','password']);
    $db   = getDB();
    $stmt = $db->prepare('SELECT US_id,US_name,US_email,US_password,US_wallet,US_caloriegoal,US_calorie_today FROM Users WHERE US_email = ?');
    $stmt->execute([$data['email']]);
    $row = $stmt->fetch();
    if (!$row) respond(401, 'ไม่พบบัญชีผู้ใช้');
    if (!password_verify($data['password'], $row['US_password'])) respond(401, 'รหัสผ่านไม่ถูกต้อง');

    unset($row['US_password']);
    respond(200, 'Login successful', ['user' => $row]);
}

// ============================================================
// 1b. GET USER — สำหรับ hardcode user_id
// ============================================================
function handleGetUser(): void {
    $userId = $_GET['user_id'] ?? null;
    if (!$userId) respond(400, 'user_id required');
    $db   = getDB();
    $stmt = $db->prepare('SELECT US_id,US_name,US_email,US_wallet,US_caloriegoal,US_calorie_today FROM Users WHERE US_id = ?');
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    if (!$user) respond(404, 'ไม่พบผู้ใช้');
    respond(200, 'OK', ['user' => $user]);
}

// ============================================================
// 2. SCAN FOOD — ส่งรูป → Claude AI วิเคราะห์
// ============================================================
function handleScanFood(): void {
    $imageBase64 = null;
    $mediaType   = 'image/jpeg';
    $filename    = '';

    if (!empty($_FILES['image'])) {
        $file = $_FILES['image'];
        if ($file['size'] > MAX_FILE_SIZE) respond(413, 'ไฟล์ใหญ่เกินไป (max 5MB)');
        $allowed = ['image/jpeg','image/png','image/webp','image/gif'];
        if (!in_array($file['type'], $allowed)) respond(415, 'รองรับเฉพาะ JPG, PNG, WEBP, GIF');
        if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
        $ext      = pathinfo($file['name'], PATHINFO_EXTENSION) ?: 'jpg';
        $filename = uniqid('food_', true) . '.' . $ext;
        move_uploaded_file($file['tmp_name'], UPLOAD_DIR . $filename);
        $imageBase64 = base64_encode(file_get_contents(UPLOAD_DIR . $filename));
        $mediaType   = $file['type'];
    } else {
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['image_base64'])) respond(400, 'ไม่พบรูปภาพ');
        $imageBase64 = $data['image_base64'];
        $mediaType   = $data['media_type'] ?? 'image/jpeg';
        if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
        $filename = uniqid('food_', true) . '.jpg';
        file_put_contents(UPLOAD_DIR . $filename, base64_decode($imageBase64));
    }

    $prompt = <<<PROMPT
วิเคราะห์อาหารในภาพแล้วประมาณแคลอรี่ ตอบเป็น JSON เท่านั้น ไม่มี markdown ไม่มีข้อความอื่น:
{
  "meal_name": "ชื่อมื้ออาหาร (ภาษาไทย)",
  "total_calories": <kcal รวม>,
  "confidence": "low|medium|high",
  "items": [
    {
      "name": "ชื่ออาหาร (ไทย)",
      "quantity": "ปริมาณโดยประมาณ เช่น 1 จาน, 200g",
      "calories": <kcal>,
      "protein_g": <g>,
      "carbs_g": <g>,
      "fat_g": <g>
    }
  ],
  "macros": { "protein": <g รวม>, "carbs": <g รวม>, "fat": <g รวม> },
  "note": "คำแนะนำสั้นๆ เกี่ยวกับมื้อนี้"
}
ถ้าไม่มีอาหาร: {"error":"ไม่พบอาหารในภาพ"}
PROMPT;

    $payload = [
        'model'      => 'claude-sonnet-4-20250514',
        'max_tokens' => 1024,
        'messages'   => [[
            'role'    => 'user',
            'content' => [
                ['type' => 'image', 'source' => ['type' => 'base64', 'media_type' => $mediaType, 'data' => $imageBase64]],
                ['type' => 'text',  'text'   => $prompt],
            ],
        ]],
    ];

    $ch = curl_init('https://api.anthropic.com/v1/messages');
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'x-api-key: ' . CLAUDE_API_KEY,
            'anthropic-version: 2023-06-01',
        ],
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_TIMEOUT        => 60,
    ]);
    $raw  = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($code !== 200) respond(502, 'AI API error', ['raw' => $raw]);
    $aiResp = json_decode($raw, true);
    $text   = $aiResp['content'][0]['text'] ?? '';
    $clean  = preg_replace('/```json|```/', '', $text);
    $result = json_decode(trim($clean), true);
    if (!$result)            respond(502, 'แปลงผลลัพธ์ AI ไม่ได้', ['raw' => $text]);
    if (isset($result['error'])) respond(422, $result['error']);

    $result['image_filename'] = $filename;
    respond(200, 'วิเคราะห์เสร็จแล้ว', $result);
}

// ============================================================
// 3. SEARCH FOOD
// ============================================================
function handleSearchFood(): void {
    $q = trim($_GET['q'] ?? '');
    if (strlen($q) < 1) respond(400, 'กรุณาใส่คำค้นหา');
    $db   = getDB();
    $stmt = $db->prepare(
        'SELECT id, name_th, name_en, category, calories, protein_g, carbs_g, fat_g, serving_size
         FROM food_items
         WHERE name_th LIKE ? OR name_en LIKE ? OR category LIKE ?
         ORDER BY name_th LIMIT 20'
    );
    $term = "%$q%";
    $stmt->execute([$term, $term, $term]);
    respond(200, 'OK', ['items' => $stmt->fetchAll(), 'count' => $stmt->rowCount()]);
}

// ============================================================
// 4. SAVE MEAL
// ============================================================
function handleSaveMeal(): void {
    $data = jsonBody(['user_id','total_calories']);
    $db   = getDB();

    $chk = $db->prepare('SELECT US_id FROM Users WHERE US_id = ?');
    $chk->execute([$data['user_id']]);
    if (!$chk->fetch()) respond(404, 'ไม่พบผู้ใช้');

    $stmt = $db->prepare(
        'INSERT INTO meal_logs
         (user_id, meal_name, meal_type, image_path, ai_analysis,
          total_calories, total_protein, total_carbs, total_fat, source)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $data['user_id'],
        $data['meal_name']      ?? 'มื้ออาหาร',
        $data['meal_type']      ?? 'lunch',
        $data['image_filename'] ?? null,
        isset($data['ai_analysis']) ? json_encode($data['ai_analysis'], JSON_UNESCAPED_UNICODE) : null,
        (int)$data['total_calories'],
        (float)($data['total_protein']  ?? 0),
        (float)($data['total_carbs']    ?? 0),
        (float)($data['total_fat']      ?? 0),
        $data['source']         ?? 'ai',
    ]);
    $logId = $db->lastInsertId();

    if (!empty($data['items']) && is_array($data['items'])) {
        $ins = $db->prepare(
            'INSERT INTO meal_items (meal_log_id, food_item_id, name_th, quantity_g, calories, protein_g, carbs_g, fat_g)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        foreach ($data['items'] as $item) {
            $ins->execute([
                $logId,
                $item['food_item_id'] ?? null,
                $item['name']         ?? $item['name_th'] ?? '',
                (float)($item['quantity_g']   ?? 100),
                (int)($item['calories']     ?? 0),
                (float)($item['protein_g']    ?? 0),
                (float)($item['carbs_g']      ?? 0),
                (float)($item['fat_g']        ?? 0),
            ]);
        }
    }

    // อัปเดต calorie_today ใน Users
    $db->prepare('CALL update_daily_summary(?, CURDATE())')->execute([$data['user_id']]);

    respond(201, 'บันทึกมื้ออาหารแล้ว', ['meal_log_id' => $logId]);
}

// ============================================================
// 5. GET LOGS
// ============================================================
function handleGetLogs(): void {
    $userId = $_GET['user_id'] ?? null;
    $date   = $_GET['date']    ?? date('Y-m-d');
    if (!$userId) respond(400, 'user_id required');

    $db   = getDB();
    $stmt = $db->prepare(
        'SELECT id, meal_name, meal_type, image_path, total_calories,
                total_protein, total_carbs, total_fat, source, eaten_at
         FROM meal_logs
         WHERE user_id = ? AND DATE(eaten_at) = ?
         ORDER BY eaten_at DESC'
    );
    $stmt->execute([$userId, $date]);
    $logs = $stmt->fetchAll();

    foreach ($logs as &$log) {
        $s = $db->prepare('SELECT * FROM meal_items WHERE meal_log_id = ?');
        $s->execute([$log['id']]);
        $log['items'] = $s->fetchAll();
    }
    unset($log);
    respond(200, 'OK', ['logs' => $logs, 'date' => $date]);
}

// ============================================================
// 5b. DELETE LOG
// ============================================================
function handleDeleteLog(): void {
    $data = jsonBody(['user_id','log_id']);
    $db   = getDB();
    $stmt = $db->prepare('DELETE FROM meal_logs WHERE id = ? AND user_id = ?');
    $stmt->execute([(int)$data['log_id'], $data['user_id']]);
    $db->prepare('CALL update_daily_summary(?, CURDATE())')->execute([$data['user_id']]);
    respond(200, 'ลบแล้ว');
}

// ============================================================
// 6. DAILY SUMMARY
// ============================================================
function handleDailySummary(): void {
    $userId = $_GET['user_id'] ?? null;
    if (!$userId) respond(400, 'user_id required');

    $db   = getDB();
    $stmt = $db->prepare(
        'SELECT v.*, u.US_caloriegoal AS goal_kcal, u.US_name
         FROM v_today_summary v
         JOIN Users u ON u.US_id = v.user_id
         WHERE v.user_id = ?'
    );
    $stmt->execute([$userId]);
    $summary = $stmt->fetch();
    if (!$summary) {
        $s2 = $db->prepare('SELECT US_caloriegoal AS goal_kcal, US_name FROM Users WHERE US_id = ?');
        $s2->execute([$userId]);
        $u = $s2->fetch();
        if (!$u) respond(404, 'ไม่พบผู้ใช้');
        $summary = [
            'user_id'       => $userId,
            'total_kcal'    => 0,
            'total_protein' => 0,
            'total_carbs'   => 0,
            'total_fat'     => 0,
            'meal_count'    => 0,
            'goal_kcal'     => $u['goal_kcal'] ?? 2000,
            'US_name'       => $u['US_name'] ?? '',
        ];
    }
    respond(200, 'OK', ['summary' => $summary]);
}

// ============================================================
// 7. WEEKLY CHART
// ============================================================
function handleWeeklyChart(): void {
    $userId = $_GET['user_id'] ?? null;
    if (!$userId) respond(400, 'user_id required');
    $db   = getDB();
    $stmt = $db->prepare('SELECT log_date, total_kcal FROM v_weekly_calories WHERE user_id = ? ORDER BY log_date');
    $stmt->execute([$userId]);
    respond(200, 'OK', ['chart' => $stmt->fetchAll()]);
}

// ── HELPERS ──────────────────────────────────────────────────
function respond(int $code, string $message, array $data = []): never {
    http_response_code($code);
    echo json_encode(['status' => $code, 'message' => $message, ...$data], JSON_UNESCAPED_UNICODE);
    exit;
}

function jsonBody(array $required = []): array {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) respond(400, 'Invalid JSON body');
    foreach ($required as $k)
        if (!isset($data[$k]) || $data[$k] === '') respond(400, "Missing required field: $k");
    return $data;
}