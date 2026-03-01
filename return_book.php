<?php
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
if (isset($_SESSION['role']) && $_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

if (isset($_GET['id'])) {
    $transaction_id = $_GET['id'];

    try {
        $pdo->beginTransaction();

        // Get book_id from transaction
        $stmt = $pdo->prepare("SELECT book_id, status FROM transactions WHERE id = ?");
        $stmt->execute([$transaction_id]);
        $transaction = $stmt->fetch();

        if ($transaction && $transaction['status'] == 'issued') {
            // Update Transaction
            $update_trans = $pdo->prepare("UPDATE transactions SET status = 'returned', returned_on = CURDATE() WHERE id = ?");
            $update_trans->execute([$transaction_id]);

            // Update Book Stock
            $update_book = $pdo->prepare("UPDATE books SET available_qty = available_qty + 1 WHERE id = ?");
            $update_book->execute([$transaction['book_id']]);

            $pdo->commit();
        } else {
            $pdo->rollBack();
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
    }
}

header("Location: transactions.php");
exit;
?>