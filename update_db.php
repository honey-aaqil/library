<?php
require_once 'db.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS sessions (
        id VARCHAR(128) NOT NULL PRIMARY KEY,
        access INT(10) UNSIGNED,
        data TEXT
    )";
    $pdo->exec($sql);
    echo "Sessions table created successfully.\n";
} catch (PDOException $e) {
    die("Error creating table: " . $e->getMessage() . "\n");
}
?>