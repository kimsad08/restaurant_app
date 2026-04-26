# 🍜 RestoSys — Restaurant App

ระบบจัดการร้านอาหารแบบ Web Application พัฒนาด้วย PHP และ MySQL ทำงานบน Docker รองรับการใช้งานหลายบทบาท (Admin, Manager, User) พร้อมฟังก์ชันจัดการเมนู สั่งอาหาร ติดตามแคลอรี่ และระบบเติมเงิน

---

## 📑 สารบัญ

1. [ภาพรวมระบบ](#1-ภาพรวมระบบ)
2. [Tech Stack & Tools](#2-tech-stack--tools)
3. [คู่มือการใช้งานระบบ](#3-คู่มือการใช้งานระบบ)
4. [ขั้นตอนการติดตั้งและตั้งค่าระบบ](#4-ขั้นตอนการติดตั้งและตั้งค่าระบบ)
5. [โครงสร้างโปรเจกต์](#5-โครงสร้างโปรเจกต์)

---

## 1. ภาพรวมระบบ

**RestoSys** เป็นระบบจัดการร้านอาหารที่ออกแบบมาเพื่อรองรับการทำงาน 3 บทบาทหลัก ได้แก่ ผู้ดูแลระบบ (Admin), ผู้จัดการร้าน (Manager) และผู้ใช้งานทั่วไป (User) โดยแต่ละบทบาทจะมีหน้า Dashboard และสิทธิ์การเข้าถึงที่แตกต่างกัน

### ฟีเจอร์หลัก

- **ระบบสมาชิกและการยืนยันตัวตน** — สมัครสมาชิก เข้าสู่ระบบ และจัดการโปรไฟล์ผู้ใช้
- **ระบบ Role-based Access** — แบ่งสิทธิ์การใช้งานตามบทบาท (Admin / Manager / User)
- **จัดการเมนูอาหาร** — เพิ่ม แก้ไข ลบรายการอาหาร พร้อมข้อมูลโภชนาการ
- **ระบบสั่งอาหาร** — สั่งอาหาร ดูรายละเอียดออเดอร์ และประวัติการสั่งซื้อ
- **ติดตามแคลอรี่และโภชนาการ** — แสดงข้อมูลโภชนาการของแต่ละเมนู และ reset แคลอรี่ได้
- **ระบบเติมเงิน (Top-up)** — ผู้ใช้สามารถเติมเงินเข้าระบบเพื่อใช้สั่งอาหารได้
- **ระบบบันทึกอาหาร (Scan)** — บันทึกอาหารที่ทานเองนอกร้าน เพื่อนับแคลอรี่
- **Analytics Dashboard** — Admin สามารถดูสถิติและภาพรวมของระบบได้
- **API Endpoint** — รองรับการเชื่อมต่อกับระบบอื่นผ่าน `api/api.php`

### โครงสร้างผู้ใช้งาน

| บทบาท | สิทธิ์การใช้งาน |
| --- | --- |
| **Admin** | จัดการผู้ใช้ทั้งหมด ดู Analytics ตั้งค่าระบบ จัดการร้านอาหาร |
| **Manager** | จัดการเมนูในร้านที่รับผิดชอบ ดูออเดอร์ และจัดการรายการอาหาร |
| **User** | ดูเมนู สั่งอาหาร เติมเงิน ดูประวัติ และจัดการโปรไฟล์ส่วนตัว |

---

## 2. Tech Stack & Tools

### Backend
- **PHP 8.1** — ภาษาหลักที่ใช้พัฒนาฝั่ง server (รันบน Apache)
- **MySQL 8.0** — ฐานข้อมูลเชิงสัมพันธ์
- **mysqli** — extension สำหรับเชื่อมต่อ MySQL จาก PHP

### Frontend
- **HTML / CSS / JavaScript** — สำหรับ UI ของระบบ
- **Google Fonts** — Sarabun, Prompt (รองรับภาษาไทย)

### Infrastructure & DevOps
- **Docker** — Container สำหรับรันระบบ
- **Docker Compose** — จัดการหลาย service พร้อมกัน
- **phpMyAdmin** — เครื่องมือจัดการฐานข้อมูลผ่านเว็บ

### Development Tools
- **Visual Studio Code** — IDE สำหรับเขียนโค้ด
- **Git / GitHub** — Version control

### Docker Services

ระบบประกอบด้วย 3 containers ที่ทำงานร่วมกัน:

| Service | Image | Port | หน้าที่ |
| --- | --- | --- | --- |
| **web** | `restaurant_app-web` (PHP 8.1 + Apache) | `8081:80` | รันเว็บแอปพลิเคชัน |
| **db** | `mysql:8.0` | `3306:3306` | ฐานข้อมูล MySQL |
| **phpmyadmin** | `phpmyadmin:latest` | `8082:80` | จัดการฐานข้อมูลผ่าน UI |

---

## 3. คู่มือการใช้งานระบบ

### การเข้าสู่ระบบ

1. เปิดเบราว์เซอร์ไปที่ `http://localhost:8081`
2. หากยังไม่มีบัญชี ให้คลิก **สมัครสมาชิก** เพื่อสร้างบัญชี User
3. ที่หน้า Login เลือกบทบาท (นักศึกษา / Manager / Admin) และกรอกอีเมล + รหัสผ่าน
4. ระบบจะนำทางไปยัง Dashboard ตามบทบาทของผู้ใช้โดยอัตโนมัติ

### การใช้งานสำหรับ User (นักศึกษา)

| ฟีเจอร์ | คำอธิบาย |
| --- | --- |
| **Dashboard** | ภาพรวม: เงินในกระเป๋า แคลอรี่วันนี้ vs เป้าหมาย ประวัติล่าสุด |
| **บันทึกอาหาร (Scan)** | บันทึกอาหารที่ทานนอกร้าน พร้อมจำนวนแคลอรี่ |
| **สั่งอาหาร** | เลือกร้าน → เลือกเมนู → ยืนยันออเดอร์ (หักจากกระเป๋าเงิน) |
| **ประวัติการสั่ง** | ดูออเดอร์ย้อนหลัง พร้อมรายละเอียดและแคลอรี่รวม |
| **เติมเงิน** | เพิ่มเงินเข้ากระเป๋าเงินเพื่อใช้สั่งอาหาร |
| **ตั้งค่าโปรไฟล์** | แก้ไขข้อมูลส่วนตัว ปรับเป้าหมายแคลอรี่ และรีเซ็ตแคลอรี่วันนี้ |

### การใช้งานสำหรับ Manager

1. เข้าสู่ระบบด้วยบัญชี Manager
2. เข้าสู่ Manager Dashboard เพื่อดูข้อมูลร้านที่รับผิดชอบ
3. จัดการเมนูอาหาร: เพิ่ม / แก้ไข / ลบรายการได้
4. ดูออเดอร์ที่เข้ามาและจัดการสถานะการสั่งซื้อ

### การใช้งานสำหรับ Admin

1. เข้าสู่ระบบด้วยบัญชี Admin
2. ใช้งาน Admin Dashboard เพื่อดูภาพรวมของระบบ (จำนวน Admin / Manager / Users / Menu / Orders / รายได้รวม)
3. ดู Analytics เพื่อดูสถิติการใช้งาน
4. จัดการข้อมูลทุกตาราง: Admin, Manager, ร้านอาหาร, เมนู, โภชนาการ, นักศึกษา, Orders, Order Detail
5. CRUD ได้ครบทุกตาราง

### การจัดการฐานข้อมูล

เข้าใช้งาน phpMyAdmin ผ่าน `http://localhost:8082` เพื่อ:
- ดูและแก้ไขข้อมูลในฐานข้อมูลโดยตรง
- Import / Export ฐานข้อมูล
- รัน SQL queries

ข้อมูลเข้าใช้งาน phpMyAdmin:
- **Server:** `db`
- **Username:** `admin`
- **Password:** `password123`

---

## 4. ขั้นตอนการติดตั้งและตั้งค่าระบบ

### 4.1 สิ่งที่ต้องเตรียม

ก่อนเริ่มติดตั้ง ตรวจสอบว่ามีโปรแกรมเหล่านี้ติดตั้งในเครื่องแล้ว:

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) — สำหรับรัน Container
- [Git](https://git-scm.com/) — สำหรับ clone โปรเจกต์
- เบราว์เซอร์ (Chrome, Firefox, Edge เป็นต้น)

### 4.2 Clone โปรเจกต์

เปิด Terminal หรือ Command Prompt แล้วรันคำสั่ง:

```bash
git clone https://github.com/<your-username>/restaurant-app.git
cd restaurant-app
```

### 4.3 เตรียมไฟล์ Database (สำคัญ)

วางไฟล์ schema ของคุณที่ `database/init.sql`

ระบบจะ **import อัตโนมัติ** เมื่อรัน Docker ครั้งแรก โดย MySQL จะอ่านไฟล์ `.sql` ทั้งหมดในโฟลเดอร์ `database/` แล้วนำเข้าฐานข้อมูล

> **หมายเหตุ:** Auto-import ทำงานเฉพาะตอนสร้าง volume ใหม่ ถ้าเคยรันมาก่อนแล้ว ดูวิธี reset ใน [Troubleshooting](#48-การแก้ไขปัญหาเบื้องต้น-troubleshooting)

### 4.4 ตรวจสอบไฟล์ตั้งค่า (ถ้าจำเป็น)

ค่า default ใช้ได้เลยถ้าไม่ได้แก้ `docker-compose.yml` แต่ถ้าต้องการกำหนดค่าผ่าน `.env`:

```bash
cp .env.example .env
```

แล้วแก้ไข `.env` ตามต้องการ

### 4.5 เริ่มต้น Docker Containers

ใน Terminal ภายในโฟลเดอร์โปรเจกต์ รันคำสั่ง:

```bash
docker-compose up -d
```

คำสั่งนี้จะ:
- ดาวน์โหลด images ที่จำเป็น (ครั้งแรกอาจใช้เวลาสักครู่)
- สร้างและเริ่มต้น 3 containers (web, db, phpmyadmin)
- รันในโหมด detached (`-d`) คือทำงานอยู่เบื้องหลัง
- Import schema จาก `database/init.sql` โดยอัตโนมัติ

ตรวจสอบสถานะ container ด้วยคำสั่ง:

```bash
docker-compose ps
```

หรือดูผ่าน Docker Desktop ใต้เมนู **Containers**

### 4.6 รอให้ MySQL พร้อมใช้งาน

ครั้งแรกที่รัน MySQL จะใช้เวลาประมาณ 20-30 วินาทีในการ initialize ฐานข้อมูล

ตรวจสอบ log ได้ด้วย:

```bash
docker-compose logs -f db
```

เมื่อเห็นข้อความ `ready for connections` แล้ว แสดงว่า MySQL พร้อมใช้งาน

### 4.7 เข้าใช้งานระบบ

หลังจากตั้งค่าเสร็จเรียบร้อย เปิดเบราว์เซอร์ไปที่:

| บริการ | URL |
| --- | --- |
| Web Application | `http://localhost:8081` |
| phpMyAdmin | `http://localhost:8082` |

### 4.8 การแก้ไขปัญหาเบื้องต้น (Troubleshooting)

#### ปัญหา: Port ถูกใช้งานอยู่ (Port already in use)

ตรวจสอบว่ามีโปรแกรมอื่นใช้ port `8081`, `8082`, หรือ `3306` อยู่หรือไม่ หากมี ให้ปิดโปรแกรมนั้น หรือเปลี่ยน port ใน `docker-compose.yml`:

```yaml
ports:
  - "9081:80"   # เปลี่ยนจาก 8081 เป็น 9081
```

#### ปัญหา: เชื่อมต่อฐานข้อมูลไม่ได้

- ตรวจสอบว่า container `db` ทำงานปกติด้วย `docker-compose ps`
- ตรวจสอบว่า `$host` ใน `config/db.php` เป็น `"db"` ไม่ใช่ `localhost`
- รอประมาณ 30 วินาที หลังจากรัน `docker-compose up` เพื่อให้ MySQL พร้อมใช้งาน

#### ปัญหา: ฐานข้อมูลไม่ถูก import อัตโนมัติ

ปกติ MySQL จะ import ไฟล์ `.sql` ในโฟลเดอร์ `database/` แค่ครั้งแรกเท่านั้น ถ้าเคยรันมาก่อน ต้อง reset volume:

```bash
docker-compose down -v       # ⚠️ ลบ volume (ข้อมูลจะหาย)
docker-compose up -d
```

#### ปัญหา: ไฟล์ที่อัปโหลดหายหลังจาก restart

ตรวจสอบว่าได้ mount โฟลเดอร์ `uploads/` เป็น volume ใน `docker-compose.yml` แล้ว (ค่า default มีให้แล้ว)

### 4.9 คำสั่ง Docker ที่ใช้บ่อย

```bash
# หยุดการทำงานของ containers (ข้อมูลคงอยู่)
docker-compose down

# ดู log ของระบบทั้งหมด
docker-compose logs -f

# ดู log เฉพาะ service เช่น web
docker-compose logs -f web

# Restart containers
docker-compose restart

# ลบ container พร้อม volume (⚠️ ข้อมูลจะหาย)
docker-compose down -v

# Build image ใหม่หลังแก้ไข Dockerfile
docker-compose up -d --build

# เข้า bash ใน container web
docker exec -it restaurant_app_web bash

# เข้า MySQL CLI
docker exec -it restaurant_app_db mysql -u admin -ppassword123 my_project
```

---

## 5. โครงสร้างโปรเจกต์

```
restaurant_app/
├── config/
│   ├── db.php                  # การเชื่อมต่อฐานข้อมูล
│   └── paths.php               # ค่าคงที่สำหรับ path และ URL
├── includes/
│   ├── auth.php                # ระบบยืนยันตัวตน (requireLogin)
│   ├── header.php              # Sidebar สำหรับ Admin/Manager
│   ├── footer.php              # JS + Modal ที่ใช้ร่วมกัน
│   └── user_header.php         # Sidebar สำหรับ User (มี wallet bar)
├── pages/
│   ├── auth/
│   │   ├── login.php           # หน้า Login
│   │   ├── logout.php          # ออกจากระบบ
│   │   └── register.php        # สมัครสมาชิก (User เท่านั้น)
│   ├── admin/
│   │   ├── admin.php           # CRUD ตาราง Admin
│   │   ├── admin_analytics.php # Analytics dashboard
│   │   └── users.php           # CRUD ตาราง Users
│   ├── manager/
│   │   ├── manager.php         # CRUD ตาราง Manager
│   │   └── manager_dashboard.php # Dashboard ของ Manager
│   ├── restaurant/
│   │   ├── restaurant.php      # CRUD ตาราง Restaurant
│   │   ├── menu.php            # CRUD ตาราง Menu
│   │   ├── nutrition.php       # CRUD ตาราง Nutrition
│   │   ├── orders.php          # CRUD ตาราง Orders
│   │   └── orderdetail.php     # CRUD ตาราง OrderDetail
│   └── user/
│       ├── user_dashboard.php  # Dashboard ของ User
│       ├── user_profile.php    # ตั้งค่าโปรไฟล์
│       ├── user_history.php    # ประวัติการสั่ง
│       ├── user_shop.php       # หน้าสั่งอาหาร
│       ├── user_topup.php      # เติมเงิน
│       ├── user_scan.php       # บันทึกอาหารด้วยตนเอง
│       └── user_reset_cal.php  # รีเซ็ตแคลอรี่วันนี้
├── api/
│   └── api.php                 # API endpoint
├── html/                       # Static assets (CSS, JS, images)
├── uploads/                    # ไฟล์ที่ user อัปโหลด (gitignored)
├── docker/
│   └── Dockerfile              # PHP + Apache image
├── database/
│   └── init.sql                # Database schema (auto-import)
├── docker-compose.yml          # Docker services configuration
├── index.php                   # Admin dashboard (entry หลังลอกอินเป็น admin)
├── .env.example                # ตัวอย่างไฟล์ environment
├── .gitignore
└── README.md
```

### หมายเหตุสำหรับนักพัฒนา

- **Path constants** ทั้งหมดถูกกำหนดใน `config/paths.php` — ทุกหน้า require ไฟล์นี้ก่อน
- **Authentication** ใช้ `requireLogin($role)` จาก `includes/auth.php` ที่ต้นไฟล์ของหน้าที่ต้อง login
- **Sessions** ทุก role ใช้ `$_SESSION['user_id']`, `$_SESSION['user_name']`, `$_SESSION['role']`
- **CSS theme** ใช้ดาร์กโทนพร้อม CSS variables (เช่น `--accent: #f97316`)

---

## 📄 License

โปรเจกต์นี้พัฒนาเพื่อการศึกษา

## 📞 Contact

หากพบปัญหาหรือมีข้อเสนอแนะ สามารถเปิด Issue ใน GitHub Repository ได้
