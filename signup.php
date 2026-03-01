<?php
require_once 'db.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (empty($full_name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "Please fill all required fields.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM members WHERE email = :email");
        $stmt->execute(['email' => $email]);
        if ($stmt->fetch()) {
            $error = "An account with this email already exists.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO members (full_name, email, phone, password) VALUES (:full_name, :email, :phone, :password)");
            if ($stmt->execute([
                'full_name' => $full_name,
                'email' => $email,
                'phone' => $phone,
                'password' => $hashed_password
            ])) {
                $success = "Sign-up successful! You can now sign in.";
            } else {
                $error = "Something went wrong. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Library Management System</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Outfit:wght@500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
</head>

<body
    style="background-image: radial-gradient(at 0% 0%, hsla(253,16%,7%,1) 0, transparent 50%), radial-gradient(at 50% 0%, hsla(225,39%,30%,1) 0, transparent 50%), radial-gradient(at 100% 0%, hsla(339,49%,30%,1) 0, transparent 50%);">

    <div class="login-container">
        <div class="login-card glass-panel animate__animated animate__zoomIn">
            <div style="text-align: center; margin-bottom: 2rem;">
                <h1 style="color: var(--primary-light); margin-bottom: 0.5rem;">Join the Library</h1>
                <p style="color: var(--text-muted);">Create your member account</p>
            </div>

            <?php if ($error): ?>
                <div
                    style="background: rgba(255, 100, 100, 0.2); color: #ffadad; padding: 10px; border-radius: var(--radius-sm); margin-bottom: 1rem; text-align: center; border: 1px solid rgba(255,100,100,0.3);">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div
                    style="background: rgba(100, 255, 150, 0.2); color: #80ff9f; padding: 10px; border-radius: var(--radius-sm); margin-bottom: 1rem; text-align: center; border: 1px solid rgba(100,255,150,0.3);">
                    <?= htmlspecialchars($success) ?>
                    <br><a href="index.php" style="color: #80ff9f; text-decoration: underline;">Go to Sign In</a>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" name="full_name" required>
                </div>
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone">
                </div>
                <div class="form-group">
                    <label>Password *</label>
                    <input type="password" name="password" required>
                </div>
                <div class="form-group">
                    <label>Confirm Password *</label>
                    <input type="password" name="confirm_password" required>
                </div>
                <button type="submit" class="btn btn-primary"
                    style="width: 100%; justify-content: center; margin-top: 1rem;">
                    Sign Up
                </button>
            </form>
            <div style="text-align: center; margin-top: 1.5rem;">
                <p style="color: var(--text-muted); font-size: 0.9rem;">Already have an account? <a href="index.php" style="color: var(--primary-light); text-decoration: none;">Sign In</a></p>
            </div>
        </div>
    </div>

</body>

</html>