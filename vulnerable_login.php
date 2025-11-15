<?php

// vulnerable_login.php - INSECURE CODE FOR DEMONSTRATION ONLY
session_start();
// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'icdfa_lab';
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
 die("Connection failed: " . $conn->connect_error);
}
$message = '';

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
 $user_username = $_POST['username'];
 $user_password = $_POST['password'];

// VULNERABLE: SQL Injection - direct concatenation
 $sql = "SELECT * FROM users WHERE username = '$user_username' AND 
password = '$user_password'";
 $result = $conn->query($sql);
 
 
 if ($result->num_rows > 0) {
 $user = $result->fetch_assoc();
 
 // VULNERABLE: Storing plain text in session
 $_SESSION['user_id'] = $user['id'];
 $_SESSION['username'] = $user['username'];
 $_SESSION['role'] = $user['role'];
 $message = "<div style='color: green;'>Login successful! Welcome " . 
$user['username'] . "</div>";
 // VULNERABLE: Direct user input in response
 echo "<script>alert('Welcome $user_username!');</script>";
 } else {
    $message = "<div style='color: red;'>Invalid credentials!</div>";
 }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
 <meta charset="UTF-8">
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <title>Vulnerable Login - ICDFA Lab</title>
 <style>
 body {
 font-family: Arial, sans-serif;
 background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
 margin: 0;
 padding: 20px;
 min-height: 100vh;
 display: flex;
 justify-content: center;
 align-items: center;
 }
 .warning {
 background: #ff6666;
 color: white;
 padding: 20px;
 margin: 20px 0;
 border-radius: 8px;
 border: 3px solid red;
 }
 .login-container {
 background: white;
 padding: 40px;
 border-radius: 15px;
 box-shadow: 0 15px 35px rgba(0,0,0,0.1);
 width: 100%;
 max-width: 500px;
 }
 .form-group {
 margin-bottom: 20px;
 }
 label {
 display: block;
 margin-bottom: 5px;
 font-weight: bold;
 }
 input[type="text"],
 input[type="password"] {
 width: 100%;
 padding: 12px;
 border: 2px solid #ddd;
 border-radius: 6px;
 font-size: 16px;
 box-sizing: border-box;
 }
 button {
 width: 100%;
 padding: 12px;
 background: #dc3545;
 color: white;
 border: none;
 border-radius: 6px;
 font-size: 16px;
 cursor: pointer;
 }
 .demo-info {
 background: #fff3cd;
 padding: 15px;
 border-radius: 6px;
 margin: 20px 0;
 }
 </style>
</head>
<body>
 <div class="login-container">
 <div class="warning">
 <h2>SECURITY WARNING </h2>
 <p>This page contains deliberate security vulnerabilities for educational 
purposes!</p>
 <p><strong>DO NOT USE THIS CODE IN 
PRODUCTION!</strong></p>
 </div>
 <h1>Vulnerable Login System</h1>
 
 <?php echo $message; ?>
 
 <form method="POST" action="">
 <div class="form-group">
 <label for="username">Username:</label>
 <input type="text" id="username" name="username" required>
 </div>
 
 <div class="form-group">
 <label for="password">Password:</label>
 <input type="password" id="password" name="password" required>
 </div>
 
 <button type="submit" name="login">Login (Insecure)</button>
 </form>
 <div class="demo-info">
 <h3>SQL Injection Demo:</h3>
 <p>Try these payloads in the username field:</p>
 <ul>
 <li><code>admin' OR '1'='1' -- </code></li>
 <li><code>' OR 1=1 -- </code></li>
 <li><code>admin' #</code></li>
 </ul>
 <p>Leave password field empty or put anything</p>
 </div>
 <div style="margin-top: 20px;">
 <h3>Sample Credentials (for testing without SQLi):</h3>
 <p><strong>Username:</strong> admin</p>
 <p><strong>Password:</strong> securepassword123</p>
 </div>
 </div>
</body>
</html>
<?php $conn->close(); ?>