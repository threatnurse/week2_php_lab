<?php
// setup_database.php
// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'icdfa_lab';
// Create connection
$conn = new mysqli($host, $username, $password);
// Check connection
if ($conn->connect_error) {
 die("Connection failed: " . $conn->connect_error);
}
// Create database
$sql = "CREATE DATABASE IF NOT EXISTS $database";
if ($conn->query($sql) === TRUE) {
 echo "Database created successfully<br>";
} else {
 echo "Error creating database: " . $conn->error . "<br>";
}
// Select database
$conn->select_db($database);
// Create users table
$sql = "CREATE TABLE IF NOT EXISTS users (
 id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 username VARCHAR(50) NOT NULL UNIQUE,
 email VARCHAR(100) NOT NULL,
 password VARCHAR(255) NOT NULL,
 role ENUM('user', 'admin') DEFAULT 'user',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 last_login TIMESTAMP NULL
)";
if ($conn->query($sql) === TRUE) {
 echo "Users table created successfully<br>";
} else {
 echo "Error creating table: " . $conn->error . "<br>";
}
// Create posts table
$sql = "CREATE TABLE IF NOT EXISTS posts (
 id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id INT(6) UNSIGNED,
 title VARCHAR(255) NOT NULL,
 content TEXT NOT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";
if ($conn->query($sql) === TRUE) {
 echo "Posts table created successfully<br>";
} else {
    echo "Error creating table: " . $conn->error . "<br>";
}
// Insert sample data
$hashed_password = password_hash('securepassword123',
PASSWORD_DEFAULT);
$sql = "INSERT IGNORE INTO users (username, email, password, role)
VALUES
 ('admin', 'admin@icdfa.com', '$hashed_password', 'admin'),
 ('john_doe', 'john@example.com', '$hashed_password', 'user'),
 ('jane_smith', 'jane@example.com', '$hashed_password', 'user')";
if ($conn->query($sql) === TRUE) {
 echo "Sample users inserted successfully<br>";
} else {
 echo "Error inserting users: " . $conn->error . "<br>";
}
// Insert sample posts
$sql = "INSERT IGNORE INTO posts (user_id, title, content) VALUES
 (1, 'Welcome to ICDFA', 'This is the first post in our secure application.'),
 (2, 'Security Tips', 'Always hash your passwords and use prepared statements.'),
 (3, 'XSS Prevention', 'Remember to escape output to prevent cross-site
scripting.')";
if ($conn->query($sql) === TRUE) {
 echo "Sample posts inserted successfully<br>";
} else {
 echo "Error inserting posts: " . $conn->error . "<br>";
 }
echo "<h3>Database setup completed!</h3>";
echo "<a href='vulnerable_login.php'>Proceed to Vulnerable Login</a><br>";
echo "<a href='secure_login.php'>Proceed to Secure Login</a>";
$conn->close();
?>