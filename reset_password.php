<?php
require_once 'db.php';

try {
    $username = 'admin';
    $password = 'nagaraj123'; // Updated password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Check if admin exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);

    if ($stmt->fetch()) {
        // Update existing admin
        $update = $pdo->prepare("UPDATE users SET password = ? WHERE username = ?");
        $update->execute([$hashed_password, $username]);
        echo "Admin password updated to: $password";
    } else {
        // Create admin if not exists
        $insert = $pdo->prepare("INSERT INTO users (username, password, full_name) VALUES (?, ?, ?)");
        $insert->execute([$username, $hashed_password, 'System Admin']);
        echo "Admin account created with password: $password";
    }

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>