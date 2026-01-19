<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
require_once 'db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM members WHERE id = ?");
        $stmt->execute([$id]);
    } catch (PDOException $e) {
        // Handle failure (likely foreign key constraint if member has transactions)
    }
}
header("Location: members.php");
exit;
?>