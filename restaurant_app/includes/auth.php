<?php
// includes/auth.php — ใส่ไว้ต้นทุกหน้าที่ต้อง login
require_once __DIR__ . '/../config/paths.php';

function requireLogin($role = null) {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . URL_LOGIN); exit;
    }
    if ($role !== null && $_SESSION['role'] !== $role) {
        // redirect ตาม role จริง
        $r = $_SESSION['role'];
        if ($r === 'admin')   { header('Location: ' . URL_INDEX); exit; }
        if ($r === 'manager') { header('Location: ' . URL_MANAGER_DASH); exit; }
        header('Location: ' . URL_USER_DASHBOARD); exit;
    }
}

function requireAnyLogin() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . URL_LOGIN); exit;
    }
}
