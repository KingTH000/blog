<?php
session_start();
require 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Check if post ID is provided
if (!isset($_GET['id'])) {
    echo "No post ID provided.";
    exit;
}

$post_id = $_GET['id'];

// Fetch the post
$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ? AND user_id = ?");
$stmt->execute([$post_id, $user_id]);
$post = $stmt->fetch();

if (!$post) {
    echo "Post not found or you don't have permission.";
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';

    if ($title && $content) {
        $update = $pdo->prepare("UPDATE posts SET title = ?, content = ? WHERE id = ? AND user_id = ?");
        $update->execute([$title, $content, $post_id, $user_id]);
        header('Location: index.php');
        exit;
    } else {
        echo "Title and content are required.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Post</title>
</head>
<body>
    <h2>Edit Post</h2>
    <form method="POST">
        <label>Title:</label><br>
        <input type="text" name="title" value="<?= htmlspecialchars($post['title']) ?>"><br><br>

        <label>Context:</label><br>
        <textarea name="context" rows="10" cols="50"><?= htmlspecialchars($post['context']) ?></textarea><br><br>

        <button type="submit">Update Post</button>
    </form>
    <p><a href="index.php">Back to Dashboard</a></p>
</body>
</html>
