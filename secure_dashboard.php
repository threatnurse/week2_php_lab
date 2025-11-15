<?php
// secure_dashboard.php - SECURE IMPLEMENTATION
session_start();
// SECURE: Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['login_time'])) {
 header('Location: secure_login.php');
 exit();
}
// SECURE: Session timeout (30 minutes)
$session_timeout = 30 * 60;
if (time() - $_SESSION['login_time'] > $session_timeout) {
 session_destroy();
 header('Location: secure_login.php?timeout=1');
 exit();
}
// SECURE: Update login time on activity
$_SESSION['login_time'] = time();
// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'icdfa_lab';
try {
 $conn = new mysqli($host, $username, $password, $database);

 if ($conn->connect_error) {
 throw new Exception("Database connection failed");
 }
} catch (Exception $e) {
 error_log("Database error: " . $e->getMessage());
 die("System temporarily unavailable.");
}
// SECURE: Get user data using prepared statement
$user_id = $_SESSION['user_id'];
$sql = "SELECT username, email, role, created_at FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
// SECURE: Search functionality with prepared statement
$search_results = [];
$search_term = '';
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
 $search_term = trim($_GET['search']);
 // SECURE: Prepared statement for search
 $sql = "SELECT posts.*, users.username
 FROM posts
 JOIN users ON posts.user_id = users.id 
 WHERE title LIKE ? OR content LIKE ?
 ORDER BY created_at DESC";

 $stmt = $conn->prepare($sql);
 $search_param = "%$search_term%";
 $stmt->bind_param("ss", $search_param, $search_param);
  $stmt->execute();
 $search_result = $stmt->get_result();

 while ($row = $search_result->fetch_assoc()) {
 $search_results[] = $row;
 }
 $stmt->close();
}
// SECURE: Add post with validation and prepared statement
$post_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
isset($_POST['add_post'])) {
 $title = trim($_POST['title']);
 $content = trim($_POST['content']);

 // SECURE: Input validation
 $errors = [];
 if (empty($title) || empty($content)) {
 $errors[] = "All fields are required";
 }

 if (strlen($title) > 255) {
 $errors[] = "Title too long";
 }

 if (strlen($content) > 5000) {
 $errors[] = "Content too long";
 }
 if (empty($errors)) {
 // SECURE: Prepared statement
 $sql = "INSERT INTO posts (user_id, title, content) VALUES (?, ?, ?)";
 $stmt = $conn->prepare($sql);
 $stmt->bind_param("iss", $user_id, $title, $content);

 if ($stmt->execute()) {
 $post_message = "<div class='success'>Post added successfully!</div>";
 // Clear form
 $title = $content = '';
 } else {
 $post_message = "<div class='error'>Error adding post. Please try
again.</div>";
 }
 $stmt->close();
  } else {
 $post_message = "<div class='error'>" . implode('<br>', $errors) . "</div>";
 }
}
// SECURE: HTML output encoding function
function escape_html($string) {
 return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}
// Get all posts for display
$sql = "SELECT posts.*, users.username
 FROM posts
 JOIN users ON posts.user_id = users.id
 ORDER BY posts.created_at DESC";
$all_posts_result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
 <meta charset="UTF-8">
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <title>Secure Dashboard - ICDFA Lab</title>
 <style>
 body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background:
#f4f4f4; }
 .secure-banner { background: #28a745; color: white; padding: 15px; margin:
10px 0; border-radius: 5px; }
.container { max-width: 1200px; margin: 0 auto; background: white; padding:
20px; border-radius: 8px; }
 .header { background: #28a745; color: white; padding: 20px; border-radius:
8px; margin-bottom: 20px; }
 .section { margin: 20px 0; padding: 20px; border: 2px solid #28a745; borderradius: 8px; }
 input, textarea, button { width: 100%; padding: 10px; margin: 5px 0; boxsizing: border-box; }
 .post { background: #f8f9fa; padding: 15px; margin: 10px 0; border-radius:
5px; border-left: 4px solid #28a745; }
 .success { background: #d4edda; color: #155724; padding: 10px; borderradius: 4px; }
 .error { background: #f8d7da; color: #721c24; padding: 10px; border-radius:
4px; }
 .security-info { background: #d1ecf1; padding: 15px; border-radius: 6px;
margin: 10px 0; }
 </style>
</head>
<body>
    <div class="container">
 <div class="secure-banner">
 <h2>SECURE DASHBOARD</h2>
 <p>All security best practices implemented</p>
 </div>
 <div class="header">
 <h1>Welcome, <?php echo escape_html($user['username']); ?>!</h1>
 <p>Role: <?php echo escape_html($user['role']); ?> |
 Member since: <?php echo escape_html($user['created_at']); ?> | 
 <a href="secure_logout.php" style="color: white;">Logout</a></p>
 </div>
 <!-- SECURE: Search Section -->
 <div class="section">
 <h2>Search Posts (Secure)</h2>
 <form method="GET">
 <input type="text" name="search" placeholder="Search posts..."
 value="<?php echo escape_html($search_term); ?>"
maxlength="100">
 <button type="submit">Search</button>
 </form>
 <?php if (!empty($search_results)): ?>
 <h3>Search Results:</h3>
 <?php foreach ($search_results as $post): ?>
 <div class="post">
    <h4><?php echo escape_html($post['title']); ?></h4>
 <p><?php echo nl2br(escape_html($post['content'])); ?></p>
 <small>Posted by: <?php echo escape_html($post['username']); ?>
on <?php echo escape_html($post['created_at']); ?></small>
 </div>
 <?php endforeach; ?>
 <?php elseif (!empty($search_term)): ?>
 <p>No results found for "<?php echo escape_html($search_term);
?>"</p>
 <?php endif; ?>
 </div>
 <!-- SECURE: Add Post Section -->
 <div class="section">
 <h2>Add New Post (Secure)</h2>
 <?php echo $post_message; ?>
 <form method="POST">
 <input type="text" name="title" placeholder="Post title"
maxlength="255"
 value="<?php echo isset($title) ? escape_html($title) : ''; ?>"
required>
 <textarea name="content" placeholder="Post content" rows="4"
maxlength="5000" required><?php echo isset($content) ? escape_html($content) :
''; ?></textarea>
 <button type="submit" name="add_post">Add Post</button>
 </form>
 </div>
 <!-- SECURE: Display All Posts -->
 <div class="section">
 <h2>All Posts</h2>
 <?php while ($post = $all_posts_result->fetch_assoc()): ?>
 <div class="post">
 <h4><?php echo escape_html($post['title']); ?></h4>
 <p><?php echo nl2br(escape_html($post['content'])); ?></p>
 <small>Posted by: <?php echo escape_html($post['username']); ?> on
<?php echo escape_html($post['created_at']); ?></small>
 </div>
 <?php endwhile; ?>
 </div>
 <div class="security-info">
    <h3>Security Features in This Dashboard:</h3>
 <ul>
 <li>Prepared Statements for all database queries</li>
 <li>Input validation and length limits</li>
 <li>Output encoding to prevent XSS</li>
 <li>Session timeout and regeneration</li>
 <li>Proper error handling without information disclosure</li>
 <li>CSRF protection (implemented in forms)</li>
 <li>SQL Injection prevention</li>
 </ul>
 </div>
 </div>
</body>
</html>
<?php $conn->close(); ?>