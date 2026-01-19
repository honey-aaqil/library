<?php
// Include db.php to use the connection $pdo
require_once 'db.php';

try {
    echo "Connected to Database.\n";

    // Read the SQL file
    $sql = file_get_contents('setup.sql');

    // Split SQL by semicolon using a basic regex that handles comments safely enough for this simple file
    // Note: A simple explode(';') can be brittle if comments contain semicolons, but setup.sql is simple.
    $statements = array_filter(array_map('trim', explode(';', $sql)));

    foreach ($statements as $stmt) {
        if (!empty($stmt)) {
            try {
                $pdo->exec($stmt);
                echo "Executed SQL statement successfully.\n"; // Optional: Print progress
            } catch (PDOException $e) {
                echo "SQL Error: " . $e->getMessage() . "\n";
                // Don't die here, try next statements (e.g., if table already exists)
            }
        }
    }

    echo "Database structure imported.\n";

    // Reset Admin Password to 'nagaraj123' explicitly after setup
    $username = 'admin';
    $password = 'nagaraj123';
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Check if admin exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);

    if ($admin = $stmt->fetch()) {
        $update = $pdo->prepare("UPDATE users SET password = ? WHERE username = ?");
        $update->execute([$hashed_password, $username]);
        echo "Admin password updated for existing user.\n";
    } else {
        $insert = $pdo->prepare("INSERT INTO users (username, password, full_name) VALUES (?, ?, ?)");
        $insert->execute([$username, $hashed_password, 'System Admin']);
        echo "Admin account created.\n";
    }
    echo "Admin password confirmed as 'nagaraj123'.\n";

} catch (PDOException $e) {
    die("DB ERROR: " . $e->getMessage() . "\n");
}
?>