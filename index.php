<?php

// Error Handling --> displaying PHP Errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


session_start();

// Setting the right timezone --> Server has another
date_default_timezone_set('Europe/Berlin');

// Including config.php with all important credentials and tokens, otherwise they would be leaked on Github or maybe even in the frontend
include 'config.php';

// establish connection to the database
$conn = new mysqli($host, $username, $password, $dbname);

$serverErrorMessage = "";
$serverErrorMessage_2 = "";
$serverErrorMessage_3 = "";
$serverSuccessMessage = "";
$stayFlipped = false;

// throwing connection error
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


// Login handling
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['signin'])) {
       
    // tracking ip and setting a timestamp
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $login_time = date('Y-m-d H:i:s');
    $username_attempt = strtolower(trim($_POST['your_name']));
    $password = $_POST['your_pass'];
    $success = false;
    $user_id = null;

    // prepating SQL Query and binding the username to the data type string
    $stmt = $conn->prepare("SELECT id, password_hash FROM users WHERE username = ?");
    $stmt->bind_param("s", $username_attempt);
    $stmt->execute();
    $stmt->store_result();

    
    if ($stmt->num_rows === 1) {
        $stmt->bind_result($user_id, $hash);
        $stmt->fetch();
        // comparing password the to hashed password in the same row as the entred username
        if (password_verify($password, $hash)) {
            $success = true;
            $_SESSION['username'] = $username_attempt;

            // notification function
            function sendPushbulletNotification($title, $body) {

    global $pushbullet_token;

    $data = [
        'type' => 'note',
        'title' => $title,
        'body' => $body
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.pushbullet.com/v2/pushes');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Access-Token: ' . $pushbullet_token,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);
}
// JSON Body of the notification message (Post Request)
sendPushbulletNotification(
    "🔐 Login Alert",
    "$username_attempt just logged in from IP $ip_address at $login_time"
);

        } 
    } else {
        $serverErrorMessage = "❌ Invalid credentials.";
    }
    $stmt->close();

 
    // preparing data types to insert int othe login_tracking_table
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $browser_name = $_POST['browser_name'] ?? null;
    $browser_version = $_POST['browser_version'] ?? null;
    $os_name = $_POST['os_name'] ?? null;
    $os_version = $_POST['os_version'] ?? null;
    $device_type = $_POST['device_type'] ?? null;
    $resolution = $_POST['resolution'] ?? null;
    $graphics_vendor = $_POST['graphics_vendor'] ?? null;
    $graphics_renderer = $_POST['graphics_renderer'] ?? null;
    $language = $_POST['language'] ?? null;
    $timezone_offset = $_POST['timezone_offset'] ?? null;
    $cookies_enabled = (isset($_POST['cookies_enabled']) && in_array(strtolower($_POST['cookies_enabled']), ['true', '1'])) ? 1 : 0;


    $stmt = $conn->prepare("INSERT INTO login_tracking_table (
        user_id, username, login_time, success, ip_address, user_agent, 
        browser_name, browser_version, os_name, os_version, device_type, resolution, 
        graphics_vendor, graphics_renderer, language, timezone_offset, cookies_enabled
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("ississssssssssssi",
        $user_id,
        $username_attempt,
        $login_time,
        $success,
        $ip_address,
        $user_agent,
        $browser_name,
        $browser_version,
        $os_name,
        $os_version,
        $device_type,
        $resolution,
        $graphics_vendor,
        $graphics_renderer,
        $language,
        $timezone_offset,
        $cookies_enabled
    );
    $stmt->execute();
    $stmt->close();

    // Redirect on sucessfull login attempt
    if ($success) {
        header("Location: dashboard.php");
        exit;
    }
}


// Handling registration
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['signup'])) {
    $stayFlipped = true;
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $pass = $_POST['pass']; 
    $re_pass = $_POST['re_pass'];

    // Check if user already exists
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
    $checkStmt->bind_param("ss", $email, $name);
    $checkStmt->execute();
    $checkStmt->store_result();
    
    // throwing erros if the username or email is already in a database row
    if ($checkStmt->num_rows > 0) {
        $serverErrorMessage_3 = "❌ Username or Email already exists.";
        $serverErrorMessage_2 = "❌ Registration failed. Try again.";
    } else {
        $hashed_password = password_hash($pass, PASSWORD_DEFAULT);
        $created_at = date('Y-m-d H:i:s');

        $stmt = $conn->prepare("INSERT INTO users (username, password_hash, email, created_at) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $hashed_password, $email, $created_at);


        if ($stmt->execute()) {
            $serverSuccessMessage = "✅ Registration successful!";
        } 
           
        
        $stmt->close();
    }

    $checkStmt->close();
}
?>
<!--Login form template -->
<!DOCTYPE html>
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
   <?php if (!empty($serverSuccessMessage)) : ?>
                                    <script>alert("<?= $serverSuccessMessage ?>");</script>
                                    <?php endif; ?>
                                     <?php if (!empty($serverErrorMessage_2)) : ?>
                                    <script>alert("<?= $serverErrorMessage_2 ?>");</script>
                                    <?php endif; ?>
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
                                <span id="server-error" class="error-message" style="color: red;">
                                          <?php if (!empty($serverErrorMessage)) echo $serverErrorMessage; ?>
                                        </span>
                                  
                                    <div class="form-group form-button">
                                        <input type="submit" name="signin" id="signin" class="form-submit" value="Log in"/>
                                    </div>
                                </form>
                            
                            </div>
       
                        </div>
                                         <div class="github-footer">                                             
  <a href="https://github.com/geo1337/Databases_2_preexam" target="_blank">
    <img src="assets/github-mark.png" alt="GitHub" class="github-logo">
  </a>
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
                                        <span id="server-error" class="error-message" style="color: red;">
                                          <?php if (!empty($serverErrorMessage_3)) echo $serverErrorMessage_3; ?>
                                        </span>
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
                                                     <div class="github-footer">                                             
  <a href="https://github.com/geo1337/Databases_2_preexam" target="_blank">
    <img src="assets/github-mark.png" alt="GitHub" class="github-logo">
  </a>
          </div> 
                    </div>
      </section>

            </div>
        </div>
     
</div>
    </div>

    <!-- JS for flip animation -->
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


document.addEventListener('DOMContentLoaded', function () {
    const name = document.getElementById("name");
    const email = document.getElementById("email");
    const pass = document.getElementById("pass");
    const re_pass = document.getElementById("re_pass");

    // Add blur listeners
    name.addEventListener("blur", validateName);
    email.addEventListener("blur", validateEmail);
    pass.addEventListener("blur", validatePassword);
    re_pass.addEventListener("blur", validateRepeatPassword);

    // On submit: validate all
    document.getElementById("register-form").addEventListener("submit", function (e) {
        const isValid = [
            validateName(),
            validateEmail(),
            validatePassword(),
            validateRepeatPassword()
        ].every(Boolean);

        if (!isValid) e.preventDefault();
    });

    // validation functions

    function validateName() {
        if (name.value.trim() === "") {
            showError(name, "Name is required");
            return false;
        }
        clearError(name);
        return true;
    }

    function validateEmail() {
        const pattern = /^[\w-.]+@[\w-]+\.[a-z]{2,}$/i;
        if (email.value.trim() === "") {
            showError(email, "Email is required");
            return false;
        } else if (!pattern.test(email.value)) {
            showError(email, "Invalid email format");
            return false;
        }
        clearError(email);
        return true;
    }

    function validatePassword() {
        if (pass.value.length < 6) {
            showError(pass, "Password must be at least 6 characters");
            return false;
        }
        clearError(pass);
        return true;
    }

    function validateRepeatPassword() {
        if (re_pass.value !== pass.value) {
            showError(re_pass, "Passwords do not match");
            return false;
        }
        clearError(re_pass);
        return true;
    }

    // function for dynamic error messages based on the validate functions

    function showError(input, message) {
        const group = input.closest(".form-group");
        let errorSpan = group.querySelector(".error-message");
        if (!errorSpan) {
            errorSpan = document.createElement("span");
            errorSpan.className = "error-message";
            group.appendChild(errorSpan);
        }
        errorSpan.textContent = message;
        errorSpan.style.color = "red";
    }

    function clearError(input) {
        const group = input.closest(".form-group");
        const errorSpan = group.querySelector(".error-message");
        if (errorSpan) {
            errorSpan.textContent = "";
        }
    }
});

    </script>
<?php if ($stayFlipped): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('flip-container').classList.add('flip');
});
</script>
<?php endif; ?>

<!-- tracking script -->
<script>

document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("login-form");

 
    const envFields = {
        browser_name: navigator.userAgent.match(/(firefox|msie|chrome|safari|edge|opera)/i)?.[0] || "unknown",
        browser_version: navigator.userAgent,
        os_name: navigator.platform,  
        os_version: "unknown",        
        device_type: /Mobi|Android/i.test(navigator.userAgent) ? "Mobile" : "Desktop",
        resolution: `${screen.width}x${screen.height}`,
        graphics_vendor: getWebGLInfo().vendor,
        graphics_renderer: getWebGLInfo().renderer,
        language: navigator.language,
        timezone_offset: new Date().getTimezoneOffset(),
        cookies_enabled: navigator.cookieEnabled ? 1 : 0
    };

    // Overwrite with parsed OS info
    const osInfo = getOSInfo();
    envFields.os_name = osInfo.os;
    envFields.os_version = osInfo.version;

    // Create hidden inputs 
    for (const key in envFields) {
        const input = document.createElement("input");
        input.type = "hidden";
        input.name = key;
        input.value = envFields[key];
        form.appendChild(input);
    }

    function getWebGLInfo() {
        try {
            const canvas = document.createElement("canvas");
            const gl = canvas.getContext("webgl") || canvas.getContext("experimental-webgl");
            const debugInfo = gl.getExtension("WEBGL_debug_renderer_info");
            return {
                vendor: gl.getParameter(debugInfo.UNMASKED_VENDOR_WEBGL),
                renderer: gl.getParameter(debugInfo.UNMASKED_RENDERER_WEBGL)
            };
        } catch (e) {
            return { vendor: "unknown", renderer: "unknown" };
        }
    }

    function getOSInfo() {
        const ua = navigator.userAgent;
        if (ua.indexOf("Windows NT 10.0") !== -1) return { os: "Windows", version: "10+" }; // Covers Win10/11
        if (ua.indexOf("Windows NT 6.3") !== -1) return { os: "Windows", version: "8.1" };
        if (ua.indexOf("Windows NT 6.2") !== -1) return { os: "Windows", version: "8" };
        if (ua.indexOf("Windows NT 6.1") !== -1) return { os: "Windows", version: "7" };
        if (/Mac OS X/.test(ua)) return { os: "macOS", version: ua.match(/Mac OS X ([\d_]+)/)?.[1].replace(/_/g, ".") || "unknown" };
        if (/Android/.test(ua)) return { os: "Android", version: ua.match(/Android\s([\d.]+)/)?.[1] || "unknown" };
        if (/iPhone OS/.test(ua)) return { os: "iOS", version: ua.match(/iPhone OS ([\d_]+)/)?.[1].replace(/_/g, ".") || "unknown" };
        return { os: "unknown", version: "unknown" };
    }
});
</script>

</body>
</html>
