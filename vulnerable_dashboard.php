<?php
// vulnerable_dashboard.php - INSECURE CODE
session_start();
if (!isset($_SESSION['user_id'])) {
 header('Location: vulnerable_login.php');
 exit();
}
// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'icdfa_lab';
$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
 die("Connection failed: " . $conn->connect_error);
}
// VULNERABLE: Display user data without sanitization
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE id = $user_id";
$result = $conn->query($sql);
$user = $result->fetch_assoc();
// VULNERABLE: Search functionality with SQL Injection
$search_results = [];
if (isset($_GET['search'])) {
 $search_term = $_GET['search'];
 // VULNERABLE: Direct concatenation in SQL
 $sql = "SELECT * FROM posts WHERE title LIKE '%$search_term%' OR
content LIKE '%$search_term%'";
 $search_result = $conn->query($sql);

 if ($search_result) {
 while ($row = $search_result->fetch_assoc()) {
 $search_results[] = $row;
 }
 }
}
// VULNERABLE: Add post without proper validation
if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
isset($_POST['add_post'])) {
 $title = $_POST['title'];
 $content = $_POST['content'];

 // VULNERABLE: Direct concatenation
 $sql = "INSERT INTO posts (user_id, title, content) VALUES ($user_id, '$title',
'$content')";

 if ($conn->query($sql) === TRUE) {
 $post_message = "<div style='color: green;'>Post added successfully!</div>";
 } else {
 $post_message = "<div style='color: red;'>Error: " . $conn->error . "</div>";
 }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
 <meta charset="UTF-8">
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <title>Vulnerable Dashboard - ICDFA Lab</title>
 <style>
 body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background:
#f4f4f4; }
 .warning { background: #ff6666; color: white; padding: 15px; margin: 10px
0; border-radius: 5px; }
 .container { max-width: 1200px; margin: 0 auto; background: white; padding:
20px; border-radius: 8px; }
 .header { background: #dc3545; color: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
 .section { margin: 20px 0; padding: 20px; border: 2px solid #dc3545; borderradius: 8px; }
 input, textarea, button { width: 100%; padding: 10px; margin: 5px 0; boxsizing: border-box; }
 .post { background: #f8f9fa; padding: 15px; margin: 10px 0; border-radius:
5px; border-left: 4px solid #dc3545; }
 </style>
</head>
<body>
 <div class="container">
 <div class="warning">
 <h2> VULNERABLE DASHBOARD </h2>
 <p>This dashboard contains multiple security vulnerabilities!</p>
 </div>
 <div class="header">
 <h1>Welcome, <?php echo $user['username']; ?>!</h1>
 <p>Role: <?php echo $user['role']; ?> | <a
href="vulnerable_login.php?logout=1" style="color: white;">Logout</a></p>
 </div>
 <!-- VULNERABLE: Search Section -->
 <div class="section">
 <h2>Search Posts (Vulnerable to SQL Injection)</h2>
 <form method="GET">
 <input type="text" name="search" placeholder="Search posts..."
value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>">
 <button type="submit">Search</button>
 </form>
 <?php if (!empty($search_results)): ?>
 <h3>Search Results:</h3>
 <?php foreach ($search_results as $post): ?>
 <div class="post">
 <h4><?php echo $post['title']; ?></h4>
 <p><?php echo $post['content']; ?></p>
 <small>Posted by user ID: <?php echo $post['user_id']; ?></small>
 </div>
 <?php endforeach; ?>
 <?php endif; ?>
 </div>
 <!-- VULNERABLE: Add Post Section -->
 <div class="section">
 <h2>Add New Post (Vulnerable)</h2>
 <?php if (isset($post_message)) echo $post_message; ?>
 <form method="POST">
 <input type="text" name="title" placeholder="Post title" required>
 <textarea name="content" placeholder="Post content" rows="4"
required></textarea>
 <button type="submit" name="add_post">Add Post</button>
 </form>
 </div>
 <!-- VULNERABLE: Display All Posts -->
 <div class="section">
 <h2>All Posts</h2>
 <?php
 // VULNERABLE: No input validation or output encoding
 $sql = "SELECT posts.*, users.username FROM posts JOIN users ON
posts.user_id = users.id ORDER BY created_at DESC";
 $result = $conn->query($sql);
 while ($post = $result->fetch_assoc()):
 ?>
 <div class="post">
 <h4><?php echo $post['title']; ?></h4>
 <p><?php echo $post['content']; ?></p>
 <small>Posted by: <?php echo $post['username']; ?> on <?php echo
$post['created_at']; ?></small>
 </div>
 <?php endwhile; ?>
 </div>
 <div class="section">
 <h3>Security Testing:</h3>
 <p>Try these SQL Injection payloads in search:</p>
 <ul>
 <li><code>' UNION SELECT 1,2,3,4,5,6 -- </code></li>
 <li><code>' UNION SELECT
id,username,password,email,role,created_at FROM users -- </code></li>
 <li><code>test'; DROP TABLE posts; -- </code></li>
 </ul>
 </div>
 </div>
</body>
</html>
<?php $conn->close(); ?>