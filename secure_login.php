<?php
// secure_login.php - SECURE IMPLEMENTATION
session_start();
// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'icdfa_lab';
// Create connection with error handling
try {
 $conn = new mysqli($host, $username, $password, $database);

 if ($conn->connect_error) {
 throw new Exception("Database connection failed: " . $conn->connect_error);
 }
} catch (Exception $e) {
 error_log("Database error: " . $e->getMessage());
 die("System temporarily unavailable. Please try again later.");
}
$message = '';
// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
 $user_username = trim($_POST['username']);
 $user_password = $_POST['password'];

 // SECURE: Input validation
 if (empty($user_username) || empty($user_password)) {
 $message = "<div class='error'>Please fill in all fields</div>";
 } elseif (strlen($user_username) > 50) {
 $message = "<div class='error'>Username too long</div>";
 } else {
 // SECURE: Prepared statement to prevent SQL Injection
 $sql = "SELECT id, username, password, role FROM users WHERE
username = ?";
 $stmt = $conn->prepare($sql);

 if ($stmt) {
 $stmt->bind_param("s", $user_username);
 $stmt->execute();
 $result = $stmt->get_result();

 if ($result->num_rows === 1) {
 $user = $result->fetch_assoc();

 // SECURE: Verify password hash
 if (password_verify($user_password, $user['password'])) {
 // SECURE: Regenerate session ID to prevent fixation
 session_regenerate_id(true);

 // SECURE: Store minimal user info in session
 $_SESSION['user_id'] = $user['id'];
 $_SESSION['username'] = $user['username'];
 $_SESSION['role'] = $user['role'];
 $_SESSION['login_time'] = time();

 // SECURE: Update last login
 $update_sql = "UPDATE users SET last_login = NOW() WHERE id
= ?";
 $update_stmt = $conn->prepare($update_sql);
 $update_stmt->bind_param("i", $user['id']);
 $update_stmt->execute();
 $update_stmt->close();

 // Redirect to secure dashboard
 header('Location: secure_dashboard.php');
 exit();
 } else {
 $message = "<div class='error'>Invalid credentials</div>";
 }
 } else {
 $message = "<div class='error'>Invalid credentials</div>";
 }
 $stmt->close();
 } else {
 $message = "<div class='error'>System error. Please try again.</div>";
 }
 }
}
// SECURE: HTML output encoding function
function escape_html($string) {
 return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
 <meta charset="UTF-8">
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <title>Secure Login - ICDFA Lab</title>
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
 .secure-banner {
 background: #28a745;
 color: white;
 padding: 15px;
 margin: 20px 0;
 border-radius: 8px;
 border: 3px solid #218838;
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
 background: #28a745;
 color: white;
 border: none;
 border-radius: 6px;
 font-size: 16px;
 cursor: pointer;
 }
 .error {
 background: #f8d7da;
 color: #721c24;
 padding: 10px;
 border-radius: 4px;
 margin: 10px 0;
 }
 .security-features {
 background: #d1ecf1;
 padding: 15px;
 border-radius: 6px;
 margin: 20px 0;
 }
 </style>
</head>
<body>
 <div class="login-container">
 <div class="secure-banner">
 <h2>SECURE LOGIN SYSTEM</h2>
 <p>This implementation follows security best practices</p>
 </div>
 <h1>Secure Login System</h1>

 <?php echo $message; ?>

 <form method="POST" action="">
 <div class="form-group">
    <label for="username">Username:</label>
 <input type="text" id="username" name="username" maxlength="50"
required
 value="<?php echo isset($_POST['username']) ?
escape_html($_POST['username']) : ''; ?>">
 </div>

 <div class="form-group">
 <label for="password">Password:</label>
 <input type="password" id="password" name="password" required
minlength="8">
 </div>

 <button type="submit" name="login">Login (Secure)</button>
 </form>
 <div class="security-features">
 <h3>Security Features Implemented:</h3>
 <ul>
    <li>Prepared Statements (SQL Injection Prevention)</li>
 <li>Password Hashing Verification</li>
 <li>Input Validation and Length Limits</li>
 <li>Output Encoding (XSS Prevention)</li>
 <li>Session Regeneration</li>
 <li>Error Handling without Information Disclosure</li>
 </ul>
 </div>
 <div style="margin-top: 20px;">
 <h3>Test Credentials:</h3>
 <p><strong>Username:</strong> admin</p>
 <p><strong>Password:</strong> securepassword123</p>
 </div>
 </div>
</body>
</html>
<?php $conn->close(); ?>