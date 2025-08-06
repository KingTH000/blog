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
$user_stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();

// Fetch user posts
$post_stmt = $conn->prepare("SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC");
$post_stmt->bind_param("i", $user_id);
$post_stmt->execute();
$posts = $post_stmt->get_result();


// Fetch posts from other users
$stmt = $conn->prepare("SELECT posts.*, users.username FROM posts JOIN users ON posts.user_id = users.id WHERE posts.user_id != ? ORDER BY created_at DESC");
$stmt->execute([$user_id ?? 0]); // If not logged in, show all posts
$otherPosts = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Dashboard</title>
</head>
<body>
    <h1>Welcome, <?= htmlspecialchars($user['username']) ?>!</h1>
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
                    <a href="editpost.php?id=<?= $post['id'] ?>">Edit</a> |
                    <a href="deletepost.php?id=<?= $post['id'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
                    <hr>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php else: ?>
        <p>No posts yet. <a href="addpost.php">Create one now!</a></p>
    <?php endif; ?>

    <h2>All Posts</h2>
    <?php if ($otherPosts): ?>
        <ul>
            <?php foreach ($otherPosts as $post): ?>
                <li>
                    <strong><?= htmlspecialchars($post['title']) ?></strong><br>
                    <?= nl2br(htmlspecialchars($post['content'])) ?><br>
                    <small>Posted by: <?= htmlspecialchars($post['username']) ?></small><br>
                    <small>Posted on: <?= $post['created_at'] ?></small><br>
                    <hr>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No other posts available.</p>
    <?php endif; ?>
</body>
</html>
