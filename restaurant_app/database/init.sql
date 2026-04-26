-- ============================================================
-- init.sql — Database schema สำหรับ Restaurant App
-- ============================================================
-- ไฟล์นี้จะถูก import อัตโนมัติเมื่อรัน docker-compose up ครั้งแรก
-- (MySQL จะรันไฟล์ทั้งหมดในโฟลเดอร์ /docker-entrypoint-initdb.d/)
--
-- หมายเหตุ:
-- 1. แทนที่ไฟล์นี้ด้วย schema จริงของคุณ (export จาก phpMyAdmin)
-- 2. ถ้าเคยรัน docker-compose up แล้ว ต้องลบ volume ก่อน:
--      docker-compose down -v
--      docker-compose up -d
-- ============================================================

USE my_project;

-- ตัวอย่าง schema (ปรับให้ตรงกับโปรเจกต์จริง)
-- หากมี database dump อยู่แล้ว ให้นำ SQL มาวางแทนที่ส่วนด้านล่างนี้

-- Admin table
-- CREATE TABLE IF NOT EXISTS Admin (
--     AD_id VARCHAR(10) PRIMARY KEY,
--     AD_name VARCHAR(100),
--     AD_email VARCHAR(100) UNIQUE,
--     AD_password VARCHAR(255),
--     AD_address TEXT,
--     AD_phone VARCHAR(10)
-- );

-- Manager table
-- CREATE TABLE IF NOT EXISTS Manager (...);

-- Restaurant table
-- CREATE TABLE IF NOT EXISTS Restaurant (...);

-- Menu table
-- CREATE TABLE IF NOT EXISTS Menu (...);

-- Users table
-- CREATE TABLE IF NOT EXISTS Users (...);

-- Orders table
-- CREATE TABLE IF NOT EXISTS Orders (...);

-- OrderDetail table
-- CREATE TABLE IF NOT EXISTS OrderDetail (...);

-- Nutrition table
-- CREATE TABLE IF NOT EXISTS Nutrition (...);
