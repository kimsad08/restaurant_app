RestoSys — Restaurant App

A web-based restaurant management system built with PHP and MySQL, containerized with Docker. Supports multiple user roles (Admin, Manager, User) with features for menu management, food ordering, calorie tracking, and a wallet top-up system.

---

Table of Contents

1. [System Overview](#1-system-overview)
2. [Tech Stack & Tools](#2-tech-stack--tools)
3. [User Guide](#3-user-guide)
4. [Installation & Setup](#4-installation--setup)
5. [Project Structure](#5-project-structure)

---

## 1. System Overview

**RestoSys** is a restaurant management system designed to support three main user roles: System Administrators (Admin), Restaurant Managers (Manager), and End Users (User). Each role has its own dashboard and access permissions.

### Key Features

- **Authentication System** — User registration, login, and profile management
- **Role-based Access Control** — Separate permissions for Admin / Manager / User
- **Menu Management** — Add, edit, and delete menu items with nutritional information
- **Ordering System** — Place orders, view order details, and track order history
- **Calorie & Nutrition Tracking** — Display nutritional info per menu item with daily calorie reset
- **Wallet Top-up System** — Users can add funds to their wallet to pay for orders
- **Manual Food Logging (Scan)** — Log food consumed outside the restaurant for calorie tracking
- **Analytics Dashboard** — Admins can view system-wide statistics and metrics
- **API Endpoint** — External system integration via `api/api.php`

### User Roles

| Role | Permissions |
| --- | --- |
| **Admin** | Manage all users, view analytics, configure system, manage restaurants |
| **Manager** | Manage menus for assigned restaurant, view orders, manage menu items |
| **User** | Browse menus, place orders, top up wallet, view history, manage profile |

---

## 2. Tech Stack & Tools

### Backend
- **PHP 8.1** — Primary server-side language (running on Apache)
- **MySQL 8.0** — Relational database
- **mysqli** — PHP extension for MySQL connectivity

### Frontend
- **HTML / CSS / JavaScript** — UI implementation
- **Google Fonts** — Sarabun, Prompt (Thai language support)

### Infrastructure & DevOps
- **Docker** — Container runtime
- **Docker Compose** — Multi-service orchestration
- **phpMyAdmin** — Web-based database management

### Development Tools
- **Visual Studio Code** — IDE
- **Git / GitHub** — Version control

### Docker Services

The system runs on three coordinated containers:

| Service | Image | Port | Purpose |
| --- | --- | --- | --- |
| **web** | `restaurant_app-web` (PHP 8.1 + Apache) | `8081:80` | Web application server |
| **db** | `mysql:8.0` | `3306:3306` | MySQL database |
| **phpmyadmin** | `phpmyadmin:latest` | `8082:80` | Database management UI |

---

## 3. User Guide

### Logging In

1. Open your browser and navigate to `http://localhost:8081`
2. If you don't have an account, click **Register** to create a User account
3. On the Login page, select your role (Student / Manager / Admin) and enter your email and password
4. The system will automatically redirect you to the appropriate dashboard based on your role

### For Users (Students)

| Feature | Description |
| --- | --- |
| **Dashboard** | Overview: wallet balance, today's calories vs. goal, recent activity |
| **Food Logging (Scan)** | Log food consumed outside the restaurant with calorie counts |
| **Order Food** | Choose restaurant → select menu items → confirm order (deducted from wallet) |
| **Order History** | View past orders with details and total calories |
| **Top-up** | Add funds to your wallet to pay for orders |
| **Profile Settings** | Edit personal info, adjust calorie goals, reset today's calories |

### For Managers

1. Log in with a Manager account
2. Access the Manager Dashboard to view your assigned restaurant
3. Manage menu items: add / edit / delete
4. View incoming orders and manage order status

### For Admins

1. Log in with an Admin account
2. Access the Admin Dashboard for system-wide overview (Admin / Manager / User counts, Menu items, Orders, total revenue)
3. View Analytics for usage statistics
4. Manage every table: Admin, Manager, Restaurants, Menu, Nutrition, Users, Orders, Order Details
5. Full CRUD operations across all tables

### Database Management

Access phpMyAdmin at `http://localhost:8082` to:
- View and edit database records directly
- Import / Export the database
- Run SQL queries

phpMyAdmin login credentials:
- **Server:** `db`
- **Username:** `admin`
- **Password:** `password123`

---

## 4. Installation & Setup

### 4.1 Prerequisites

Make sure the following are installed on your machine:

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) — for running containers
- [Git](https://git-scm.com/) — for cloning the project
- A modern browser (Chrome, Firefox, Edge, etc.)

### 4.2 Clone the Project

Open a terminal or command prompt and run:

```bash
git clone https://github.com/<your-username>/restaurant-app.git
cd restaurant-app
```

### 4.3 Prepare the Database File (Important)

Place your schema file at `database/init.sql`

The system will **import it automatically** the first time you start Docker. MySQL reads all `.sql` files in the `database/` folder and imports them into the database on initial startup.

> **Note:** Auto-import only runs when the volume is created for the first time. If you've run it before, see the [Troubleshooting](#48-troubleshooting) section to reset.

### 4.4 Verify Configuration (If Needed)

Default values in `docker-compose.yml` work out of the box. If you want to override them via `.env`:

```bash
cp .env.example .env
```

Then edit `.env` as needed.

### 4.5 Start Docker Containers

From the project folder, run:

```bash
docker-compose up -d
```

This command will:
- Pull required images (may take a moment on first run)
- Build and start three containers (web, db, phpmyadmin)
- Run in detached mode (`-d`) — services run in the background
- Auto-import the schema from `database/init.sql`

Check container status:

```bash
docker-compose ps
```

Or view it in Docker Desktop under the **Containers** tab.

### 4.6 Wait for MySQL to Be Ready

On first startup, MySQL takes about 20-30 seconds to initialize the database.

Watch the logs with:

```bash
docker-compose logs -f db
```

When you see the `ready for connections` message, MySQL is ready to use.

### 4.7 Access the System

Once everything is running, open your browser and navigate to:

| Service | URL |
| --- | --- |
| Web Application | `http://localhost:8081` |
| phpMyAdmin | `http://localhost:8082` |

### 4.8 Troubleshooting

#### Issue: Port already in use

Check whether another program is using port `8081`, `8082`, or `3306`. Either close that program or change the port in `docker-compose.yml`:

```yaml
ports:
  - "9081:80"   # change from 8081 to 9081
```

#### Issue: Cannot connect to database

- Verify the `db` container is running: `docker-compose ps`
- Verify that `$host` in `config/db.php` is `"db"` and not `localhost`
- Wait ~30 seconds after running `docker-compose up` to allow MySQL to initialize

#### Issue: Database not auto-imported

MySQL only imports `.sql` files from `database/` on the **first** volume creation. If you've run it before, you must reset the volume:

```bash
docker-compose down -v       # ⚠️ deletes the volume (data will be lost)
docker-compose up -d
```

#### Issue: Uploaded files disappear after restart

Verify that the `uploads/` folder is mounted as a volume in `docker-compose.yml` (this is configured by default).

### 4.9 Common Docker Commands

```bash
# Stop containers (data is preserved)
docker-compose down

# Tail all logs
docker-compose logs -f

# Tail logs for a specific service
docker-compose logs -f web

# Restart containers
docker-compose restart

# Remove containers and volumes (⚠️ data will be lost)
docker-compose down -v

# Rebuild image after changing Dockerfile
docker-compose up -d --build

# Open a bash shell in the web container
docker exec -it restaurant_app_web bash

# Open the MySQL CLI
docker exec -it restaurant_app_db mysql -u admin -ppassword123 my_project
```

---

## 5. Project Structure

```
restaurant_app/
├── config/
│   ├── db.php                  # Database connection
│   └── paths.php               # Path & URL constants
├── includes/
│   ├── auth.php                # Authentication helpers (requireLogin)
│   ├── header.php              # Sidebar for Admin/Manager
│   ├── footer.php              # Shared JS + modal markup
│   └── user_header.php         # Sidebar for Users (with wallet bar)
├── pages/
│   ├── auth/
│   │   ├── login.php           # Login page
│   │   ├── logout.php          # Logout handler
│   │   └── register.php        # User registration
│   ├── admin/
│   │   ├── admin.php           # CRUD: Admin table
│   │   ├── admin_analytics.php # Analytics dashboard
│   │   └── users.php           # CRUD: Users table
│   ├── manager/
│   │   ├── manager.php         # CRUD: Manager table
│   │   └── manager_dashboard.php # Manager dashboard
│   ├── restaurant/
│   │   ├── restaurant.php      # CRUD: Restaurant table
│   │   ├── menu.php            # CRUD: Menu table
│   │   ├── nutrition.php       # CRUD: Nutrition table
│   │   ├── orders.php          # CRUD: Orders table
│   │   └── orderdetail.php     # CRUD: OrderDetail table
│   └── user/
│       ├── user_dashboard.php  # User dashboard
│       ├── user_profile.php    # Profile settings
│       ├── user_history.php    # Order history
│       ├── user_shop.php       # Order food
│       ├── user_topup.php      # Wallet top-up
│       ├── user_scan.php       # Manual food logging
│       └── user_reset_cal.php  # Reset today's calories
├── api/
│   └── api.php                 # API endpoint
├── html/                       # Static assets (CSS, JS, images)
├── uploads/                    # User-uploaded files (gitignored)
├── docker/
│   └── Dockerfile              # PHP + Apache image
├── database/
│   └── init.sql                # Database schema (auto-imported)
├── docker-compose.yml          # Docker services configuration
├── index.php                   # Admin dashboard (entry after admin login)
├── .env.example                # Environment file template
├── .gitignore
└── README.md
```

### Developer Notes

- **Path constants** are all defined in `config/paths.php` — every page requires this file first
- **Authentication** uses `requireLogin($role)` from `includes/auth.php` at the top of any protected page
- **Sessions** across all roles use `$_SESSION['user_id']`, `$_SESSION['user_name']`, `$_SESSION['role']`
- **CSS theme** uses a dark palette with CSS variables (e.g., `--accent: #f97316`)

---

License

This project was developed for educational purposes.

Contact

For issues or suggestions, please open an Issue in the GitHub Repository.
