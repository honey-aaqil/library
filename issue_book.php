<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
require_once 'db.php';

// Fetch Books (Available > 0)
$books_stmt = $pdo->query("SELECT id, title FROM books WHERE available_qty > 0 ORDER BY title ASC");
$books = $books_stmt->fetchAll();

// Fetch Members
$members_stmt = $pdo->query("SELECT id, full_name FROM members ORDER BY full_name ASC");
$members = $members_stmt->fetchAll();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $book_id = $_POST['book_id'];
    $member_id = $_POST['member_id'];
    $return_date = $_POST['return_date'];

    if (empty($book_id) || empty($member_id) || empty($return_date)) {
        $error = "All fields are required.";
    } else {
        try {
            $pdo->beginTransaction();

            // Double check availability
            $check_stmt = $pdo->prepare("SELECT available_qty FROM books WHERE id = ?");
            $check_stmt->execute([$book_id]);
            $qty = $check_stmt->fetchColumn();

            if ($qty > 0) {
                // Create Transaction
                $sql = "INSERT INTO transactions (book_id, member_id, issue_date, return_date, status) VALUES (?, ?, CURDATE(), ?, 'issued')";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$book_id, $member_id, $return_date]);

                // Update Book Stock
                $update_sql = "UPDATE books SET available_qty = available_qty - 1 WHERE id = ?";
                $update_stmt = $pdo->prepare($update_sql);
                $update_stmt->execute([$book_id]);

                $pdo->commit();
                $success = "Book issued successfully!";

                // Refresh book list
                $books_stmt = $pdo->query("SELECT id, title FROM books WHERE available_qty > 0 ORDER BY title ASC");
                $books = $books_stmt->fetchAll();
            } else {
                $pdo->rollBack();
                $error = "Book is out of stock.";
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Database Error: " . $e->getMessage();
        }
    }
}

include 'header.php';
include 'sidebar.php';
?>

<div class="animate__animated animate__fadeIn">
    <div style="margin-bottom: 2rem;">
        <a href="transactions.php" style="color: var(--text-muted); text-decoration: none; font-size: 0.9rem;">
            <i class="fas fa-arrow-left"></i> Back to Transactions
        </a>
        <h2 style="margin-top: 1rem;">Issue Book</h2>
    </div>

    <div class="glass-panel" style="max-width: 600px; padding: 2rem;">
        <?php if ($error): ?>
            <div
                style="background: rgba(255, 100, 100, 0.2); color: #ffadad; padding: 10px; border-radius: var(--radius-sm); margin-bottom: 1rem;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div
                style="background: rgba(100, 255, 150, 0.2); color: #80ff9f; padding: 10px; border-radius: var(--radius-sm); margin-bottom: 1rem;">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Select Book</label>
                <select name="book_id" required>
                    <option value="">-- Select Book --</option>
                    <?php foreach ($books as $b): ?>
                        <option value="<?= $b['id'] ?>">
                            <?= htmlspecialchars($b['title']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Select Member</label>
                <select name="member_id" required>
                    <option value="">-- Select Member --</option>
                    <?php foreach ($members as $m): ?>
                        <option value="<?= $m['id'] ?>">
                            <?= htmlspecialchars($m['full_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Expected Return Date</label>
                <input type="date" name="return_date" min="<?= date('Y-m-d') ?>" required>
            </div>

            <button type="submit" class="btn btn-primary">
                Confirm Issue
            </button>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>