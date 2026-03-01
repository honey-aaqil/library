<?php
// Get current page to set active state
$current_page = basename($_SERVER['PHP_SELF']);
$role = $_SESSION['role'] ?? 'admin';
$username = $_SESSION['username'] ?? 'User';
?>
<aside class="sidebar glass-panel animate__animated animate__fadeInLeft">
    <div class="brand">
        <h2 style="color: var(--primary-light); display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-book-reader"></i> LMS
        </h2>
    </div>

    <nav class="nav-menu" style="display: flex; flex-direction: column; gap: 0.5rem;">
        <a href="dashboard.php" class="nav-link <?= $current_page == 'dashboard.php' ? 'active' : '' ?>">
            <i class="fas fa-home" style="width: 20px;"></i> Dashboard
        </a>
        <a href="books.php"
            class="nav-link <?= ($current_page == 'books.php' || $current_page == 'add_book.php') ? 'active' : '' ?>">
            <i class="fas fa-book" style="width: 20px;"></i> Books
        </a>
        <?php if ($role === 'admin'): ?>
            <a href="members.php" class="nav-link <?= $current_page == 'members.php' ? 'active' : '' ?>">
                <i class="fas fa-users" style="width: 20px;"></i> Members
            </a>
        <?php endif; ?>
        <a href="transactions.php" class="nav-link <?= $current_page == 'transactions.php' ? 'active' : '' ?>">
            <i class="fas fa-exchange-alt" style="width: 20px;"></i> <?= $role === 'admin' ? 'Transactions' : 'My History' ?>
        </a>

        <div style="margin-top: auto; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 1rem;">
            <a href="logout.php" class="nav-link" style="color: #ff6b6b;">
                <i class="fas fa-sign-out-alt" style="width: 20px;"></i> Logout
            </a>
        </div>
    </nav>
</aside>
<main class="main-content">
    <header class="glass-header glass-panel"
        style="margin-bottom: 2rem; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center;">
        <h3 style="margin: 0;">Library Management</h3>
        <div class="user-profile" style="display: flex; align-items: center; gap: 10px;">
            <span style="font-size: 0.9rem; color: var(--text-muted);"><?= ucfirst($role) ?></span>
            <div
                style="width: 35px; height: 35px; background: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;" title="<?= htmlspecialchars($username) ?>">
                <?= strtoupper(substr($username, 0, 1)) ?>
            </div>
        </div>
    </header>