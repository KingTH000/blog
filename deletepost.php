<?php
session_start();
require 'db.php';  // DB connection
require 'casbin.php'; // Casbin client setup

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized");
}

$username = $_SESSION['username'];

$sub = $_SESSION['username'];
$obj = "post";
$act = "delete";

// Check with Casbin
if (!casbinEnforce($sub, $obj, $act)) {
    die("Unauthorized: insufficient permissions");
}


// Get post ID
if (!isset($_GET['id'])) {
    die("No post ID provided");
}
$post_id = $_GET['id'];

// Fetch post owner
$stmt = $conn->prepare("SELECT user_id FROM posts WHERE id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();

if (!$post) {
    die("Post not found");
}

// Decide the "object" (own vs any post)
$obj = ($post['user_id'] == $_SESSION['user_id']) ? "post_own" : "post";
$act = "delete";

// Ask Casbin if allowed
if (!casbinEnforce($username, $obj, $act)) {
    die("Unauthorized");
}

// If allowed → delete
$stmt = $conn->prepare("DELETE FROM posts WHERE id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();

header("Location: index.php");
exit;
