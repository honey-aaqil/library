<?php
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$role = $_SESSION['role'] ?? 'admin';
$member_id = $_SESSION['user_id'];

// Handle Approval/Rejection
if ($role === 'admin' && isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $txn_id = $_GET['id'];

    if ($action === 'approve') {
        try {
            $pdo->beginTransaction();
            // Get book id
            $stmt = $pdo->prepare("SELECT book_id FROM transactions WHERE id = ? AND status = 'pending'");
            $stmt->execute([$txn_id]);
            $txn = $stmt->fetch();

            if ($txn) {
                // Check if book still available
                $avail_stmt = $pdo->prepare("SELECT available_qty FROM books WHERE id = ?");
                $avail_stmt->execute([$txn['book_id']]);
                if ($avail_stmt->fetchColumn() > 0) {
                    // Update book qty
                    $pdo->prepare("UPDATE books SET available_qty = available_qty - 1 WHERE id = ?")->execute([$txn['book_id']]);
                    // Update transaction
                    $pdo->prepare("UPDATE transactions SET status = 'issued', issue_date = CURDATE(), return_date = DATE_ADD(CURDATE(), INTERVAL 14 DAY) WHERE id = ?")->execute([$txn_id]);
                    $pdo->commit();
                } else {
                    $pdo->rollBack();
                    // Auto-reject if out of stock
                    $pdo->prepare("UPDATE transactions SET status = 'rejected' WHERE id = ?")->execute([$txn_id]);
                }
            } else {
                $pdo->rollBack();
            }
        } catch (Exception $e) {
            $pdo->rollBack();
        }
    } elseif ($action === 'reject') {
        $pdo->prepare("UPDATE transactions SET status = 'rejected' WHERE id = ?")->execute([$txn_id]);
    }

    header("Location: transactions.php");
    exit;
}

// Fetch Transactions with Joins
if ($role === 'admin') {
    $sql = "SELECT t.*, b.title as book_title, m.full_name as member_name
    FROM transactions t
    JOIN books b ON t.book_id = b.id
    JOIN members m ON t.member_id = m.id
    ORDER BY CASE WHEN t.status = 'pending' THEN 1 ELSE 2 END, t.issue_date DESC";
    $stmt = $pdo->query($sql);
    $transactions = $stmt->fetchAll();
} else {
    $sql = "SELECT t.*, b.title as book_title, m.full_name as member_name
    FROM transactions t
    JOIN books b ON t.book_id = b.id
    JOIN members m ON t.member_id = m.id
    WHERE t.member_id = ?
    ORDER BY t.issue_date DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$member_id]);
    $transactions = $stmt->fetchAll();
}

include 'header.php';
include 'sidebar.php';
?>

<div class="animate__animated animate__fadeIn">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2><?= $role === 'admin' ? 'Transactions' : 'My Transactions' ?></h2>
        <?php if ($role === 'admin'): ?>
            <a href="issue_book.php" class="btn btn-primary">
                <i class="fas fa-hand-holding"></i> Issue New Book
            </a>
        <?php else: ?>
            <a href="books.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Request New Book
            </a>
        <?php endif; ?>
    </div>

    <div class="glass-panel" style="padding: 0;">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="padding-left: 2rem;">ID</th>
                        <th>Book</th>
                        <th>Member</th>
                        <th>Issue Date</th>
                        <th>Return Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($transactions) > 0): ?>
                        <?php foreach ($transactions as $t): ?>
                            <tr>
                                <td style="padding-left: 2rem; color: var(--text-muted);">#
                                    <?= $t['id'] ?>
                                </td>
                                <td style="font-weight: 500; color: white;">
                                    <?= htmlspecialchars($t['book_title']) ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($t['member_name']) ?>
                                </td>
                                <td>
                                    <?= date('M d, Y', strtotime($t['issue_date'])) ?>
                                </td>
                                <td>
                                    <?php if ($t['status'] == 'returned'): ?>
                                        <span style="color: var(--text-muted);">
                                            <?= date('M d, Y', strtotime($t['returned_on'])) ?> (Returned)
                                        </span>
                                    <?php elseif ($t['status'] == 'pending'): ?>
                                        <span style="color: var(--text-muted);">TBD</span>
                                    <?php elseif ($t['status'] == 'rejected'): ?>
                                        <span style="color: var(--text-muted);">-</span>
                                    <?php else: ?>
                                        <?= date('M d, Y', strtotime($t['return_date'])) ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($t['status'] == 'returned'): ?>
                                        <span
                                            style="background: rgba(100,255,150,0.1); color: #80ff9f; padding: 4px 10px; border-radius: 20px; font-size: 0.8rem;">Returned</span>
                                    <?php elseif ($t['status'] == 'pending'): ?>
                                        <span
                                            style="background: rgba(255,150,100,0.1); color: #ff9f80; padding: 4px 10px; border-radius: 20px; font-size: 0.8rem;">Pending</span>
                                    <?php elseif ($t['status'] == 'rejected'): ?>
                                        <span
                                            style="background: rgba(255,100,100,0.1); color: #ffadad; padding: 4px 10px; border-radius: 20px; font-size: 0.8rem;">Rejected</span>
                                    <?php else: ?>
                                        <?php
                                        $today = date('Y-m-d');
                                        $is_overdue = $today > $t['return_date'];
                                        ?>
                                        <span
                                            style="background: <?= $is_overdue ? 'rgba(255,100,100,0.1)' : 'rgba(255,200,100,0.1)' ?>; color: <?= $is_overdue ? '#ffadad' : '#ffcd80' ?>; padding: 4px 10px; border-radius: 20px; font-size: 0.8rem;">
                                            <?= $is_overdue ? 'Overdue' : 'Issued' ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($role === 'admin' && $t['status'] == 'pending'): ?>
                                        <div style="display: flex; gap: 5px;">
                                            <a href="transactions.php?action=approve&id=<?= $t['id'] ?>" class="btn"
                                                style="padding: 5px 10px; font-size: 0.8rem; background: rgba(100,255,150,0.2); color: #80ff9f;">
                                                <i class="fas fa-check"></i>
                                            </a>
                                            <a href="transactions.php?action=reject&id=<?= $t['id'] ?>" class="btn"
                                                style="padding: 5px 10px; font-size: 0.8rem; background: rgba(255,100,100,0.2); color: #ffadad;"
                                                onclick="return confirm('Are you sure you want to reject this request?');">
                                                <i class="fas fa-times"></i>
                                            </a>
                                        </div>
                                    <?php elseif ($role === 'admin' && $t['status'] == 'issued'): ?>
                                        <a href="return_book.php?id=<?= $t['id'] ?>" class="btn"
                                            style="padding: 5px 10px; font-size: 0.8rem; background: rgba(100,255,150,0.2); color: #80ff9f;">
                                            Return
                                        </a>
                                    <?php else: ?>
                                        <span style="color: var(--text-muted);">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 2rem; color: var(--text-muted);">No
                                transactions found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>