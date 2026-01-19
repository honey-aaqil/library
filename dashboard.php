<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
require_once 'db.php';

// Fetch Stats
$total_books = $pdo->query("SELECT COUNT(*) FROM books")->fetchColumn();
$total_members = $pdo->query("SELECT COUNT(*) FROM members")->fetchColumn();
$issued_books = $pdo->query("SELECT COUNT(*) FROM transactions WHERE status = 'issued'")->fetchColumn();
$total_transactions = $pdo->query("SELECT COUNT(*) FROM transactions")->fetchColumn();

include 'header.php';
include 'sidebar.php';
?>

<div class="animate__animated animate__fadeIn">
    <h2 style="margin-bottom: 1.5rem;">Dashboard Overview</h2>

    <div class="stats-grid">
        <!-- Total Books -->
        <div class="stat-card glass-panel">
            <div class="stat-icon" style="background: rgba(100, 100, 255, 0.2); color: #8080ff;">
                <i class="fas fa-book"></i>
            </div>
            <div class="stat-info">
                <div class="stat-value">
                    <?= $total_books ?>
                </div>
                <div class="stat-label">Total Books</div>
            </div>
        </div>

        <!-- Total Members -->
        <div class="stat-card glass-panel">
            <div class="stat-icon" style="background: rgba(255, 100, 255, 0.2); color: #ff80ff;">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-info">
                <div class="stat-value">
                    <?= $total_members ?>
                </div>
                <div class="stat-label">Members</div>
            </div>
        </div>

        <!-- Active Issues -->
        <div class="stat-card glass-panel">
            <div class="stat-icon" style="background: rgba(255, 200, 100, 0.2); color: #ffcd80;">
                <i class="fas fa-book-reader"></i>
            </div>
            <div class="stat-info">
                <div class="stat-value">
                    <?= $issued_books ?>
                </div>
                <div class="stat-label">Active Issues</div>
            </div>
        </div>

        <!-- Total Transactions -->
        <div class="stat-card glass-panel">
            <div class="stat-icon" style="background: rgba(100, 255, 150, 0.2); color: #80ff9f;">
                <i class="fas fa-history"></i>
            </div>
            <div class="stat-info">
                <div class="stat-value">
                    <?= $total_transactions ?>
                </div>
                <div class="stat-label">Total Transactions</div>
            </div>
        </div>
    </div>

    <!-- Recent Activity / Quick Actions could go here -->
    <div class="glass-panel" style="padding: 2rem; margin-top: 2rem;">
        <h3 style="margin-bottom: 1rem;">Quick Actions</h3>
        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
            <a href="add_book.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Book
            </a>
            <a href="members.php" class="btn" style="background: rgba(255,255,255,0.1); color: white;">
                <i class="fas fa-user-plus"></i> Add Member
            </a>
            <a href="transactions.php" class="btn" style="background: rgba(255,255,255,0.1); color: white;">
                <i class="fas fa-hand-holding"></i> Issue Book
            </a>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>