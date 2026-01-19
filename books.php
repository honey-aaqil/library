require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
header("Location: index.php");
exit;
}

// Fetch Books
$stmt = $pdo->query("SELECT * FROM books ORDER BY created_at DESC");
$books = $stmt->fetchAll();

include 'header.php';
include 'sidebar.php';
?>

<div class="animate__animated animate__fadeIn">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2>Book Inventory</h2>
        <a href="add_book.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Book
        </a>
    </div>

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
                                    <div style="display: flex; gap: 10px;">
                                        <a href="add_book.php?id=<?= $book['id'] ?>" style="color: var(--primary-light);"><i
                                                class="fas fa-edit"></i></a>
                                        <a href="delete_book.php?id=<?= $book['id'] ?>" style="color: #ff6b6b;"
                                            onclick="return confirm('Are you sure you want to delete this book?');"><i
                                                class="fas fa-trash"></i></a>
                                    </div>
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