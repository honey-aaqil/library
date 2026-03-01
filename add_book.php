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

$book = [
    'id' => '',
    'title' => '',
    'author' => '',
    'isbn' => '',
    'category' => '',
    'quantity' => 1
];
$is_edit = false;

if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $fetch_book = $stmt->fetch();
    if ($fetch_book) {
        $book = $fetch_book;
        $is_edit = true;
    }
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $isbn = trim($_POST['isbn']);
    $category = trim($_POST['category']);
    $quantity = (int) $_POST['quantity'];

    // Simple validation
    if (empty($title) || empty($author) || empty($quantity)) {
        $error = "Title, Author and Quantity are required.";
    } else {
        try {
            if ($is_edit) {
                // Calculate difference in quantity to update available_qty
// Logic: available_qty = old_available + (new_qty - old_qty)
// But simplified: Reset available based on issues count? No, that's complex.
// Let's assume editing quantity only adds/removes total stock.
// We should probably recalculate available based on issued copies.
// Simplified: available = total - issued.

                // Get current issued count
                $stmt_issued = $pdo->prepare("SELECT COUNT(*) FROM transactions WHERE book_id = ? AND status = 'issued'");
                $stmt_issued->execute([$book['id']]);
                $issued_count = $stmt_issued->fetchColumn();

                $new_available = $quantity - $issued_count;

                if ($new_available < 0) {
                    $error = "Cannot reduce quantity below current issued count ($issued_count).";
                } else {
                    $sql = "UPDATE books SET title=?, author=?, isbn=?, category=?, quantity=?, available_qty=? WHERE id=?";
                    $stmt = $pdo->
                        prepare($sql);
                    $stmt->execute([$title, $author, $isbn, $category, $quantity, $new_available, $book['id']]);
                    $success = "Book updated successfully!";
                    // Refresh data
                    $book = array_merge($book, $_POST);
                    $book['available_qty'] = $new_available;
                }
            } else {
                // New Book
                $sql = "INSERT INTO books (title, author, isbn, category, quantity, available_qty) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$title, $author, $isbn, $category, $quantity, $quantity]);
                $success = "Book added successfully!";
                // Reset form
                $book = ['id' => '', 'title' => '', 'author' => '', 'isbn' => '', 'category' => '', 'quantity' => 1];
            }
        } catch (PDOException $e) {
            $error = "Database Error: " . $e->getMessage();
        }
    }
}

include 'header.php';
include 'sidebar.php';
?>

<div class="animate__animated animate__fadeIn">
    <div style="margin-bottom: 2rem;">
        <a href="books.php" style="color: var(--text-muted); text-decoration: none; font-size: 0.9rem;">
            <i class="fas fa-arrow-left"></i> Back to Books
        </a>
        <h2 style="margin-top: 1rem;">
            <?= $is_edit ? 'Edit Book' : 'Add New Book' ?>
        </h2>
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
                <label>Book Title *</label>
                <input type="text" name="title" value="<?= htmlspecialchars($book['title']) ?>" required>
            </div>

            <div class="form-group">
                <label>Author *</label>
                <input type="text" name="author" value="<?= htmlspecialchars($book['author']) ?>" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <div class="form-group">
                    <label>ISBN</label>
                    <input type="text" name="isbn" value="<?= htmlspecialchars($book['isbn']) ?>">
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <input type="text" name="category" value="<?= htmlspecialchars($book['category']) ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Total Quantity *</label>
                <input type="number" name="quantity" min="1" value="<?= htmlspecialchars($book['quantity']) ?>"
                    required>
            </div>

            <button type="submit" class="btn btn-primary">
                <?= $is_edit ? 'Update Book' : 'Save Book' ?>
            </button>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>