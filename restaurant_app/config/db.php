<?php
// config/db.php — Database connection
require_once __DIR__ . '/paths.php';

// อ่านค่าจาก environment variables ก่อน ถ้าไม่มีใช้ default (สำหรับ Docker)
$host   = getenv('DB_HOST')     ?: 'db';
$user   = getenv('DB_USER')     ?: 'admin';
$pass   = getenv('DB_PASSWORD') ?: 'password123';
$dbname = getenv('DB_NAME')     ?: 'my_project';
$port   = (int)(getenv('DB_PORT') ?: 3306);

$conn = mysqli_connect($host, $user, $pass, $dbname, $port);

if (!$conn) {
    die("เชื่อมต่อล้มเหลว: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");
