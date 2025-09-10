<?php
require 'db.php';

$errors = [];
$success = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    // Basic validation
    if (empty($name) || empty($email) || empty($password)) {
        $errors[] = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    } else {
        // Check if email exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $errors[] = "Email is already registered.";
        } else {
            // Hash and insert into users
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $email, $hashedPassword);

            if ($stmt->execute()) {
                // ✅ Assign "user" role via Casbin API
                $casbinUrl = "http://localhost:8080/role";
                $payload = json_encode(["user" => $name, "role" => "user"]);

                $ch = curl_init($casbinUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
                $apiResponse = curl_exec($ch);
                curl_close($ch);

                // Optional: check API response
                $res = json_decode($apiResponse, true);
                if (isset($res['message'])) {
                    $success = "Registration successful (role assigned). You can now <a href='login.php'>login</a>.";
                } else {
                    $errors[] = "User registered, but failed to assign role in Casbin.";
                }
            } else {
                $errors[] = "Registration failed. Please try again.";
            }
        }
    }
}
?>
