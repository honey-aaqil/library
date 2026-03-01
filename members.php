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

// Fetch Members
$stmt = $pdo->query("SELECT * FROM members ORDER BY join_date DESC");
$members = $stmt->fetchAll();

include 'header.php';
include 'sidebar.php';
?>

<div class="animate__animated animate__fadeIn">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2>Member List</h2>
        <a href="add_member.php" class="btn btn-primary">
            <i class="fas fa-user-plus"></i> Add New Member
        </a>
    </div>

    <div class="glass-panel" style="padding: 0;">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="padding-left: 2rem;">ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Joined Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($members) > 0): ?>
                        <?php foreach ($members as $member): ?>
                            <tr>
                                <td style="padding-left: 2rem; color: var(--text-muted);">#
                                    <?= $member['id'] ?>
                                </td>
                                <td style="font-weight: 500; color: white;">
                                    <?= htmlspecialchars($member['full_name']) ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($member['email']) ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($member['phone']) ?>
                                </td>
                                <td>
                                    <?= date('M d, Y', strtotime($member['join_date'])) ?>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 10px;">
                                        <a href="add_member.php?id=<?= $member['id'] ?>" style="color: var(--primary-light);"><i
                                                class="fas fa-edit"></i></a>
                                        <a href="delete_member.php?id=<?= $member['id'] ?>" style="color: #ff6b6b;"
                                            onclick="return confirm('Are you sure you want to delete this member?');"><i
                                                class="fas fa-trash"></i></a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 2rem; color: var(--text-muted);">No members
                                found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>