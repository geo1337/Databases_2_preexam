# 💻 Full-Stack Login & User Management System with PHP and MySQL

This project was developed as a pre-exam assignment for the **Databases II module (Summer Semester 2025)** at **Hochschule für Technik Stuttgart**. It demonstrates the implementation of a secure, role-based login and registration system with a dynamic dashboard, login environment tracking, and real-time admin features.

---

## 🚀 Features

- 🔐 User registration and login with validation
- 🧠 Password hashing using PHP’s `password_hash()`
- 📱 Push notifications on successful logins via [Pushbullet](https://www.pushbullet.com/)
- 🧑‍💼 Admin dashboard to manage users (edit/delete accounts)
- 🔍 Live search in user table
- 📁 Export user data to Excel with [SheetJS](https://sheetjs.com/)
- 🧠 Login tracking: logs browser, OS, resolution, and more
- 🔁 Flip animation for login/register form switch (HTML/CSS/JS)
- ✅ Client-side validation for all form fields
- 🔒 Server-side protection with `.htaccess` authentication
- 🌐 Responsive and modern UI (Bootstrap)

---

## 🛠️ Technologies Used

| Component    | Technology              |
|--------------|--------------------------|
| Frontend     | HTML, CSS, JavaScript, Bootstrap, jQuery |
| Backend      | PHP    |
| Database     | MySQL        & phpMyAdmin            |
| Hosting      | [InfinityFree](https://www.infinityfree.net) |
| APIs         | [Pushbullet API](https://docs.pushbullet.com/) |
| Libraries    | [SheetJS](https://docs.sheetjs.com/) for Excel export

---

## 🧩 Database Design

###  `users` Table

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL,
    role_id INT,
    FOREIGN KEY (role_id) REFERENCES roles(id)
);

 `roles` Table

CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL
);
`login_tracking_table` Table
CREATE TABLE login_tracking_table (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    username VARCHAR(100),
    login_time DATETIME,
    success TINYINT(1),
    ip_address VARCHAR(45),
    user_agent TEXT,
    browser_name VARCHAR(100),
    browser_version TEXT,
    os_name VARCHAR(100),
    os_version VARCHAR(100),
    device_type VARCHAR(50),
    resolution VARCHAR(20),
    graphics_vendor VARCHAR(100),
    graphics_renderer VARCHAR(100),
    language VARCHAR(20),
    timezone_offset INT,
    cookies_enabled TINYINT(1),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

🛡️ Security Practices
Passwords stored using password_hash() and verified with password_verify()

SQL injection prevention via prepared statements (bind_param)

30-minute session timeout for inactive users

.htaccess and .htpasswd for server-level protection

Role-based access: only admins can edit or delete users

Login attempts logged, even failed ones, with full environment info


The config.php file securely stores your configuration:

php
Kopieren
Bearbeiten
$host = "localhost";
$username = "db_user";
$password = "db_pass";
$dbname = "your_db";
$pushbullet_token = "your_pushbullet_token";

🧪 Testing Scenarios
Register a new user and confirm it's saved

Login and confirm the Pushbullet notification

Try logging in with incorrect credentials (check that it's tracked)

Use an admin account to edit or delete other users

Export table data and open in Excel

Wait 30+ minutes of inactivity to verify session timeout

Check tracking data in the login_tracking_table