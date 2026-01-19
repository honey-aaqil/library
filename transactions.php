require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
header("Location: index.php");
exit;
}

// Fetch Transactions with Joins
$sql = "SELECT t.*, b.title as book_title, m.full_name as member_name
FROM transactions t
JOIN books b ON t.book_id = b.id
JOIN members m ON t.member_id = m.id
ORDER BY t.issue_date DESC";
$stmt = $pdo->query($sql);
$transactions = $stmt->fetchAll();

include 'header.php';
include 'sidebar.php';
?>

<div class="animate__animated animate__fadeIn">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2>Transactions</h2>
        <a href="issue_book.php" class="btn btn-primary">
            <i class="fas fa-hand-holding"></i> Issue New Book
        </a>
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
                                    <?php else: ?>
                                        <?= date('M d, Y', strtotime($t['return_date'])) ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($t['status'] == 'returned'): ?>
                                        <span
                                            style="background: rgba(100,255,150,0.1); color: #80ff9f; padding: 4px 10px; border-radius: 20px; font-size: 0.8rem;">Returned</span>
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
                                    <?php if ($t['status'] == 'issued'): ?>
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