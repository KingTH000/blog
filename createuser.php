<?php include 'db.php'; ?>
<!DOCTYPE html>
<html>
<head><title>Add User</title></head>
<link rel="stylesheet" href="style.css">
<body>
<h2>Add New User</h2>
<form method="POST" action="">
    Name: <input type="text" name="name" required><br><br>
    Email: <input type="email" name="email" required><br><br>
    <input type="submit" value="Add User">
</form>
<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $conn->query("INSERT INTO users (username, email) VALUES ('$name', '$email')");
    header("Location: index.php");
    exit();
}
?>
</body>
</html>
