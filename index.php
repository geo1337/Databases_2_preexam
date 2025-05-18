<?php
date_default_timezone_set('Europe/Berlin');
include 'config.php';
$host = "sql106.infinityfree.com"; // Replace with your actual host (e.g. sql103.epizy.com)
$dbname = "if0_39012981_my_db"; // Your full DB name
$username = "if0_39012981"; // Your InfinityFree username
$password = "lW1txYk0rXD"; // Use the correct password


$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['signup'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $pass = $_POST['pass'];
    $re_pass = $_POST['re_pass'];

    if ($pass !== $re_pass) {
        echo "❌ Passwords do not match!";
    } else {
        $hashed_password = password_hash($pass, PASSWORD_DEFAULT);
       
        $created_at = date('Y-m-d H:i:s');
        // Use prepared statement
        $stmt = $conn->prepare("INSERT INTO users (username, password_hash, email, created_at) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $hashed_password, $email, $created_at);

        if ($stmt->execute()) {
           echo "<script>alert('✅ Registration successful!');</script>";
        } 

        $stmt->close();
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login/Register Flip</title>

    <!-- Font Icon -->
    <link rel="stylesheet" href="fonts/material-icon/css/material-design-iconic-font.min.css">

    <!-- Main css -->
    <link rel="stylesheet" href="css/style.css">

    
</head>
<body>

    <div class="main">
        <div class="flip-container" id="flip-container">
            <div class="flipper">

                <!-- Sign in (Front) -->
                <section class="sign-in front">
                    <div class="container">
                        <div class="signin-content">
                            <div class="signin-image">
                                <figure><img src="images/signin-image.jpg" alt="sign in image"></figure>
                                <a href="#" class="signup-image-link" id="toRegister">Create an account</a>
                            </div>
                            <div class="signin-form">
                                <h2 class="form-title">Log in</h2>
                                <form method="POST" class="register-form" id="login-form">
                                    <div class="form-group">
                                        <label for="your_name"><i class="zmdi zmdi-account material-icons-name"></i></label>
                                        <input type="text" name="your_name" id="your_name" placeholder="Your Name"/>
                                    </div>
                                    <div class="form-group">
                                        <label for="your_pass"><i class="zmdi zmdi-lock"></i></label>
                                        <input type="password" name="your_pass" id="your_pass" placeholder="Password"/>
                                    </div>
                                    <div class="form-group">
                                        <input type="checkbox" name="remember-me" id="remember-me" class="agree-term" />
                                        <label for="remember-me" class="label-agree-term"><span><span></span></span>Remember me</label>
                                    </div>
                                    <div class="form-group form-button">
                                        <input type="submit" name="signin" id="signin" class="form-submit" value="Log in"/>
                                    </div>
                                </form>
                            
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Sign up (Back) -->
                <section class="signup back">
                    <div class="container">
                        <div class="signup-content">
                            <div class="signup-form">
                                <h2 class="form-title">Sign up</h2>
                                <form method="POST" class="register-form" id="register-form">
                                    <div class="form-group">
                                        <label for="name"><i class="zmdi zmdi-account material-icons-name"></i></label>
                                        <input type="text" name="name" autocomplete="off" id="name" placeholder="Your Name"/>
                                    </div>
                                    <div class="form-group">
                                        <label for="email"><i class="zmdi zmdi-email"></i></label>
                                        <input type="email" name="email" autocomplete="off" id="email" placeholder="Your Email"/>
                                    </div>
                                    <div class="form-group">
                                        <label for="pass"><i class="zmdi zmdi-lock"></i></label>
                                        <input type="password" autocomplete="off" name="pass" id="pass" placeholder="Password"/>
                                    </div>
                                    <div class="form-group">
                                        <label for="re_pass"><i class="zmdi zmdi-lock-outline"></i></label>
                                        <input type="password" autocomplete="off" name="re_pass" id="re_pass" placeholder="Repeat your password"/>
                                    </div>
                                    <div class="form-group">
                                        <input type="checkbox" name="agree-term" id="agree-term" class="agree-term" />
                                        <label for="agree-term" class="label-agree-term"><span><span></span></span>I agree to all statements in <a href="#" class="term-service">Terms of service</a></label>
                                    </div>
                                    <div class="form-group form-button">
                                        <input type="submit" name="signup" id="signup" class="form-submit" value="Register"/>
                                    </div>
                                </form>
                            </div>
                            <div class="signup-image">
                                <figure><img src="images/signup-image.jpg" alt="sign up image"></figure>
                                <a href="#" class="signup-image-link" id="toLogin">I am already member</a>
                            </div>
                        </div>
                    </div>
                </section>

            </div>
        </div>
    </div>

    <!-- JS -->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const flipContainer = document.getElementById('flip-container');
            const toRegister = document.getElementById('toRegister');
            const toLogin = document.getElementById('toLogin');

            toRegister.addEventListener('click', function(e) {
                e.preventDefault();
                flipContainer.classList.add('flip');
            });

            toLogin.addEventListener('click', function(e) {
                e.preventDefault();
                flipContainer.classList.remove('flip');
            });
        });
    </script>
</body>
</html>
