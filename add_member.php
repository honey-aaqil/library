require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
header("Location: index.php");
exit;
}

$member = [
'id' => '',
'full_name' => '',
'email' => '',
'phone' => ''
];
$is_edit = false;

if (isset($_GET['id'])) {
$stmt = $pdo->prepare("SELECT * FROM members WHERE id = ?");
$stmt->execute([$_GET['id']]);
$fetch_member = $stmt->fetch();
if ($fetch_member) {
$member = $fetch_member;
$is_edit = true;
}
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
$full_name = trim($_POST['full_name']);
$email = trim($_POST['email']);
$phone = trim($_POST['phone']);

if (empty($full_name) || empty($email)) {
$error = "Name and Email are required.";
} else {
try {
if ($is_edit) {
$sql = "UPDATE members SET full_name=?, email=?, phone=? WHERE id=?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$full_name, $email, $phone, $member['id']]);
$success = "Member updated successfully!";
$member = array_merge($member, $_POST);
} else {
$sql = "INSERT INTO members (full_name, email, phone) VALUES (?, ?, ?)";
$stmt = $pdo->prepare($sql);
$stmt->execute([$full_name, $email, $phone]);
$success = "Member added successfully!";
$member = ['id' => '', 'full_name' => '', 'email' => '', 'phone' => ''];
}
} catch (PDOException $e) {
if ($e->getCode() == 23000) { // Duplicate entry
$error = "Email already exists.";
} else {
$error = "Database Error: " . $e->getMessage();
}
}
}
}

include 'header.php';
include 'sidebar.php';
?>

<div class="animate__animated animate__fadeIn">
    <div style="margin-bottom: 2rem;">
        <a href="members.php" style="color: var(--text-muted); text-decoration: none; font-size: 0.9rem;">
            <i class="fas fa-arrow-left"></i> Back to Members
        </a>
        <h2 style="margin-top: 1rem;">
            <?= $is_edit ? 'Edit Member' : 'Add New Member' ?>
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
                <label>Full Name *</label>
                <input type="text" name="full_name" value="<?= htmlspecialchars($member['full_name']) ?>" required>
            </div>

            <div class="form-group">
                <label>Email Address *</label>
                <input type="email" name="email" value="<?= htmlspecialchars($member['email']) ?>" required>
            </div>

            <div class="form-group">
                <label>Phone Number</label>
                <input type="text" name="phone" value="<?= htmlspecialchars($member['phone']) ?>">
            </div>

            <button type="submit" class="btn btn-primary">
                <?= $is_edit ? 'Update Member' : 'Save Member' ?>
            </button>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>