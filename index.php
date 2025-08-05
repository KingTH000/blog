<?php
session_start();
require 'db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user info
$user_stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();

// Fetch user posts
$post_stmt = $conn->prepare("SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC");
$post_stmt->bind_param("i", $user_id);
$post_stmt->execute();
$posts = $post_stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Dashboard</title>
</head>
<body>
    <h1>Welcome, <?= htmlspecialchars($user['name']) ?>!</h1>
    <a href="addpost.php">Add New Post</a> |
    <a href="logout.php">Logout</a>
    <hr>

    <h2>Your Posts</h2>
    <?php if ($posts->num_rows > 0): ?>
        <ul>
            <?php while ($post = $posts->fetch_assoc()): ?>
                <li>
                    <strong><?= htmlspecialchars($post['title']) ?></strong><br>
                    <?= nl2br(htmlspecialchars($post['content'])) ?><br>
                    <small>Posted on: <?= $post['created_at'] ?></small><br>
                    <a href="edit_post.php?id=<?= $post['id'] ?>">✏️ Edit</a> |
                    <a href="delete_post.php?id=<?= $post['id'] ?>" onclick="return confirm('Are you sure?')">🗑️ Delete</a>
                    <hr>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php else: ?>
        <p>No posts yet. <a href="addpost.php">Create one now!</a></p>
    <?php endif; ?>
</body>
</html>
