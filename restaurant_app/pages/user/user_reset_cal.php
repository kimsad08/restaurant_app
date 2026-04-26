<?php
// user_reset_cal.php
session_start();
require_once __DIR__ . '/../../config/paths.php';
require_once INCLUDES_PATH . '/auth.php';
requireLogin('user');
require_once CONFIG_PATH . '/db.php';
$e = mysqli_real_escape_string($conn, $_SESSION['user_id']);
mysqli_query($conn,"UPDATE Users SET US_calorie_today=0 WHERE US_id='$e'");
header('Location: ' . URL_USER_PROFILE);
exit;