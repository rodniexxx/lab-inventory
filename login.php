<?php
require_once 'config.php';

if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Invalid credentials.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login - Lab Inventory</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="login.css">
</head>
<body>

<div class="page-wrap">
    <div class="login-card">
        <div class="login-side login-side--image">
            <div class="image-overlay"></div>
            <div class="brand-block">
                <img src="logo1.png" alt="Lab Inventory Logo" class="panel-logo">
                <div>
                    <h3>Casaul</h3>
                    <p>Computer Laboratory Inventory</p>
                </div>
            </div>
        </div>

        <div class="login-side login-side--form">
            <div class="login-header">
                <h1>Admin Login</h1>
            </div>

            <?php if ($error): ?>
                <p class="error"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>

            <form method="post">
                <div class="input-group">
                    <i class="fas fa-envelope"></i>
                    <input type="text" name="username" placeholder="Username" required autofocus>
                </div>
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" placeholder="Password" required>
                </div>

                <button type="submit">Login</button>

            </form>

            <p class="terms">Manage and track computer lab inventory easily. here in DRLCEFI.</p>
        </div>
    </div>
</div>

</body>
</html>