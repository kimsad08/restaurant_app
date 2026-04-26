<?php
/**
 * paths.php — กำหนด path กลางของโปรเจกต์
 * include ไฟล์นี้เป็นอันดับแรกในทุกหน้า เพื่อให้ใช้ค่าคงที่ได้ทั่วระบบ
 */

// ===== Absolute paths (สำหรับ require/include) =====
define('BASE_PATH',     dirname(__DIR__));
define('CONFIG_PATH',   BASE_PATH . '/config');
define('INCLUDES_PATH', BASE_PATH . '/includes');
define('PAGES_PATH',    BASE_PATH . '/pages');
define('API_PATH',      BASE_PATH . '/api');
define('UPLOADS_PATH',  BASE_PATH . '/uploads');

// ===== Base URL =====
define('BASE_URL', '');

// ===== URL paths (สำหรับ HTML link, form action, redirect) =====
// Index
define('URL_INDEX',           '/index.php');

// Auth
define('URL_LOGIN',           '/pages/auth/login.php');
define('URL_LOGOUT',          '/pages/auth/logout.php');
define('URL_REGISTER',        '/pages/auth/register.php');

// User
define('URL_USER_DASHBOARD',  '/pages/user/user_dashboard.php');
define('URL_USER_PROFILE',    '/pages/user/user_profile.php');
define('URL_USER_HISTORY',    '/pages/user/user_history.php');
define('URL_USER_SHOP',       '/pages/user/user_shop.php');
define('URL_USER_TOPUP',      '/pages/user/user_topup.php');
define('URL_USER_SCAN',       '/pages/user/user_scan.php');
define('URL_USER_RESET_CAL',  '/pages/user/user_reset_cal.php');

// Admin
define('URL_ADMIN',           '/pages/admin/admin.php');
define('URL_ADMIN_ANALYTICS', '/pages/admin/admin_analytics.php');
define('URL_USERS',           '/pages/admin/users.php');

// Manager
define('URL_MANAGER',         '/pages/manager/manager.php');
define('URL_MANAGER_DASH',    '/pages/manager/manager_dashboard.php');

// Restaurant
define('URL_RESTAURANT',      '/pages/restaurant/restaurant.php');
define('URL_MENU',            '/pages/restaurant/menu.php');
define('URL_NUTRITION',       '/pages/restaurant/nutrition.php');
define('URL_ORDERS',          '/pages/restaurant/orders.php');
define('URL_ORDER_DETAIL',    '/pages/restaurant/orderdetail.php');

// API
define('URL_API',             '/api/api.php');
