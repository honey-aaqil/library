<?php
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$role = $_SESSION['role'] ?? 'admin';
$member_id = $_SESSION['user_id'];

// Fetch Books
$stmt = $pdo->query("SELECT * FROM books ORDER BY created_at DESC");
$books = $stmt->fetchAll();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['request_book_id'])) {
    if ($role === 'member') {
        $book_id = $_POST['request_book_id'];

        // Check if member already requested this book
        $check_stmt = $pdo->prepare("SELECT id FROM transactions WHERE member_id = ? AND book_id = ? AND status IN ('pending', 'issued')");
        $check_stmt->execute([$member_id, $book_id]);

        if ($check_stmt->fetch()) {
            $error = "You already have a pending request or an active issue for this book.";
        } else {
            // Check availability
            $avail_stmt = $pdo->prepare("SELECT available_qty FROM books WHERE id = ?");
            $avail_stmt->execute([$book_id]);
            $qty = $avail_stmt->fetchColumn();

            if ($qty > 0) {
                // Request book
                $sql = "INSERT INTO transactions (book_id, member_id, issue_date, return_date, status) VALUES (?, ?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 14 DAY), 'pending')";
                $stmt = $pdo->prepare($sql);
                if ($stmt->execute([$book_id, $member_id])) {
                    $success = "Book requested successfully. Waiting for admin approval.";
                } else {
                    $error = "Failed to request book.";
                }
            } else {
                $error = "Book is out of stock.";
            }
        }
    }
}

include 'header.php';
include 'sidebar.php';
?>

<div class="animate__animated animate__fadeIn">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2><?= $role === 'admin' ? 'Book Inventory' : 'Browse Books' ?></h2>
        <?php if ($role === 'admin'): ?>
            <a href="add_book.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Book
            </a>
        <?php endif; ?>
    </div>

    <?php if ($error): ?>
        <div style="background: rgba(255, 100, 100, 0.2); color: #ffadad; padding: 10px; border-radius: var(--radius-sm); margin-bottom: 1rem;">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div style="background: rgba(100, 255, 150, 0.2); color: #80ff9f; padding: 10px; border-radius: var(--radius-sm); margin-bottom: 1rem;">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <div class="glass-panel" style="padding: 0;">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="padding-left: 2rem;">ID</th>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Category</th>
                        <th>ISBN</th>
                        <th>Stock</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($books) > 0): ?>
                        <?php foreach ($books as $book): ?>
                            <tr>
                                <td style="padding-left: 2rem; color: var(--text-muted);">#
                                    <?= $book['id'] ?>
                                </td>
                                <td style="font-weight: 500; color: white;">
                                    <?= htmlspecialchars($book['title']) ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($book['author']) ?>
                                </td>
                                <td><span
                                        style="background: rgba(255,255,255,0.1); padding: 4px 10px; border-radius: 20px; font-size: 0.8rem;">
                                        <?= htmlspecialchars($book['category']) ?>
                                    </span></td>
                                <td>
                                    <?= htmlspecialchars($book['isbn']) ?>
                                </td>
                                <td>
                                    <span style="<?= $book['available_qty'] > 0 ? 'color: #80ff9f;' : 'color: #ffadad;' ?>">
                                        <?= $book['available_qty'] ?> /
                                        <?= $book['quantity'] ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($role === 'admin'): ?>
                                        <div style="display: flex; gap: 10px;">
                                            <a href="add_book.php?id=<?= $book['id'] ?>" style="color: var(--primary-light);"><i
                                                    class="fas fa-edit"></i></a>
                                            <a href="delete_book.php?id=<?= $book['id'] ?>" style="color: #ff6b6b;"
                                                onclick="return confirm('Are you sure you want to delete this book?');"><i
                                                    class="fas fa-trash"></i></a>
                                        </div>
                                    <?php else: ?>
                                        <?php if ($book['available_qty'] > 0): ?>
                                            <form method="POST" style="margin: 0;">
                                                <input type="hidden" name="request_book_id" value="<?= $book['id'] ?>">
                                                <button type="submit" class="btn" style="padding: 5px 10px; font-size: 0.8rem; background: rgba(100, 100, 255, 0.2); color: #8080ff;" onclick="return confirm('Request this book?');">
                                                    Request Book
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span style="color: var(--text-muted); font-size: 0.8rem;">Out of Stock</span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 2rem; color: var(--text-muted);">No books
                                found in the library.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>