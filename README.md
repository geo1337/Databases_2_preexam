# 💻 Full-Stack User Authentication / Tracking and Dashboard System

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

```
###  `roles` Table
```sql
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL
);
```
###  `login_tracking` Table
```sql

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
```
## 🛡️ Security Practices

- 🔒 Passwords are securely stored using PHP’s `password_hash()` and verified via `password_verify()`
- 🛡️ SQL injection is prevented using **prepared statements** (`bind_param`)
- ⏳ Inactive sessions are automatically destroyed after **30 minutes**
- 🧱 Server access is protected with `.htaccess` and `.htpasswd`
- 👮 Role-based access control ensures that **only admins** can edit or delete users
- 📊 All login attempts — even failed ones — are **fully logged** with browser, OS, IP, and environment data

---
> ⚠️ **Important:**  

> The `config.php` file is **not included** in this repository for security reasons.  
> It has been added to the `.gitignore` file to prevent sensitive credentials from being exposed.

## ⚙️ Example: `config.php` File

The `config.php` file securely stores your sensitive credentials and tokens. Here's a sample:

```php
<?php
$host = "localhost";
$username = "db_user";
$password = "db_pass";
$dbname = "your_db";
$pushbullet_token = "your_pushbullet_token";
?>
```
## 🧪 Testing Scenarios

Run through the following scenarios to verify that all key features are working correctly:

- 📝 **Register a new user**  
  Fill out the registration form and ensure the user is saved in the database and visible in the dashboard table.

- 🔐 **Log in with valid credentials**  
  Confirm that login works and that a **Pushbullet notification** is sent to your connected device.

- ❌ **Try logging in with incorrect credentials**  
  Ensure the login fails, and the attempt is still logged in the `login_tracking_table`.

- 👨‍💼 **Log in as an admin**  
  Edit or delete another user from the dashboard. Confirm that changes are reflected in real time.

- 📁 **Export user table to Excel**  
  Click the **export** button and verify that the downloaded `.xlsx` file contains accurate user data.

- ⏱️ **Test session timeout**  
  Leave the session inactive for 30+ minutes, then try to navigate the dashboard. You should be logged out and redirected to the login page.

- 📊 **Verify login tracking**  
  Use phpMyAdmin to open the `login_tracking_table` and confirm that details such as browser, OS, IP, and login success status are stored correctly.
