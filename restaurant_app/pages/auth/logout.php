<?php
// logout.php
require_once __DIR__ . '/../../config/paths.php';
session_start();
session_destroy();
header('Location: ' . URL_LOGIN);
exit;