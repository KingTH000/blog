<?php include 'db.php'; ?>
<!DOCTYPE html>
<html>
<head><title>Edit User</title></head>
<link rel="stylesheet" href="style.css">
<body>
<h2>Edit User</h2>
<?php
$id = $_GET['id'];
$result = $conn->query("SELECT * FROM users WHERE id = $id");
$user = $result->fetch_assoc();
?>
<form method="POST" action="">
    Name: <input type="text" name="name" value="<?= $user['username'] ?>" required><br><br>
    Email: <input type="email" name="email" value="<?= $user['email'] ?>" required><br><br>
    <input type="submit" value="Update User">
</form>
<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $conn->query("UPDATE users SET username='$name', email='$email' WHERE id=$id");
    header("Location: index.php");
    exit();
}
?>
</body>
</html>
