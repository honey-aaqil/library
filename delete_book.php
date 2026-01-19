<?php
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM books WHERE id = ?");
        $stmt->execute([$id]);
    } catch (PDOException $e) {
        // In a real app, you'd handle foreign key constraint errors nicely (e.g. can't delete if issued)
// For now, let's just redirect.
    }
}
header("Location: books.php");
exit;
?>