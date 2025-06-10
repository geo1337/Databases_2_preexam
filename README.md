💻 Login & Registration System with PHP and MySQL

This project is part of a pre-exam assignment for the Databases module at Hochschule für Technik Stuttgart. It demonstrates the implementation of a secure login and registration system using PHP, MySQL, and InfinityFree hosting.

🚀 Features 

User registration with email, password, and username

Password hashing using PHP's password_hash() for security

Login functionality (planned or under construction)

Flip animation between login and registration forms using HTML/CSS/JS

Responsive and modern UI

Database interaction via MySQL

🛡️ Security Practices

Passwords are stored hashed, not in plain text

Database access credentials are not hardcoded in the main code, but instead stored securely using config.php

⚙️ Why use config.php?

The config.php file contains sensitive database configuration details like:

$host = "...";
$dbname = "...";
$username = "...";
$password = "...";

We use a separate config.php file to:

Avoid exposing credentials in the main application code

Improve maintainability – you only change DB credentials in one place

Make the project safer to share – the file can be added to .gitignore so credentials aren't pushed to GitHub

🔒 Tip: Always add config.php to your .gitignore to avoid leaking it via GitHub.

🛠️ Technologies Used

PHP (core scripting)

MySQL (database)
 
HTML/CSS/JS+JQuery (frontend)

InfinityFree (free web hosting & MySQL server & phpmyadmin)


💾 Database Table Structure

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL
);

