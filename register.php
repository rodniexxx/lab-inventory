<?php
require_once 'config.php';

if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($username) || empty($password) || empty($confirmPassword)) {
        $error = "All fields are required.";
    } elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match.";
    } else {
        // Check if username exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);

        if ($stmt->fetch()) {
            $error = "Username already exists.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $stmt->execute([$username, $hashedPassword]);

            $success = "Registration successful! You can now login.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Register - Lab Inventory</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
* { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins', sans-serif; }

body {
    height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    background: linear-gradient(135deg,#CCFBFF,#EF96C5);
    overflow:hidden;
    position:relative;
}

.register-card {
    position:relative;
    z-index:1;
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(20px);
    border-radius: 25px;
    padding: 50px 40px;
    width:100%;
    max-width:400px;
    box-shadow: 0 15px 30px rgba(0,0,0,0.2);
    text-align:center;
    color:#fff;
    border:1px solid rgba(255,255,255,0.2);
}

.register-card h2 {
    font-size:2rem;
    margin-bottom:30px;
    font-weight:600;
    letter-spacing:1px;
    color:#fff;
}

.input-group {
    position: relative;
    margin-bottom: 20px;
    text-align: left;
}
.input-group i {
    position:absolute;
    top:50%;
    left:15px;
    transform: translateY(-50%);
    color: rgba(255,255,255,0.7);
}
.input-group input {
    width:100%;
    padding:14px 18px 14px 45px;
    border-radius:12px;
    border:none;
    background: rgba(255,255,255,0.2);
    color:#fff;
    font-size:1rem;
    outline:none;
    transition: all 0.3s ease;
}
.input-group input::placeholder { color: rgba(255,255,255,0.6); }
.input-group input:focus { background: rgba(255,255,255,0.35); }

button {
    width:100%;
    padding:14px;
    border-radius:12px;
    border:none;
    background: linear-gradient(135deg,#ff758c,#ff7eb3);
    color:#fff;
    font-weight:600;
    font-size:1rem;
    cursor:pointer;
    box-shadow: 0 5px 20px rgba(255,255,255,0.2);
    transition: all 0.3s ease;
}
button:hover { transform: scale(1.05); box-shadow:0 8px 30px rgba(255,255,255,0.35); }

.error {
    background: rgba(255,0,0,0.2);
    color:#ff4d4d;
    padding:12px 15px;
    border-radius:8px;
    border-left:4px solid #ff4d4d;
    margin-bottom:20px;
    text-align:left;
}

.success {
    background: rgba(0,255,0,0.2);
    color:#00ff4d;
    padding:12px 15px;
    border-radius:8px;
    border-left:4px solid #00ff4d;
    margin-bottom:20px;
    text-align:left;
}

.login-link {
    margin-top:20px;
    font-size:0.9rem;
    color:#fff;
    opacity:0.8;
}
.login-link a { color:#fff; text-decoration:none; font-weight:500; }
.login-link a:hover { text-decoration:underline; opacity:1; }

@media(max-width:480px){ .register-card { padding:40px 25px; } }
</style>
</head>
<body>

<div class="register-card">
    <h2>Register</h2>

    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <?php if ($success): ?>
        <p class="success"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <form method="post">
        <div class="input-group">
            <i class="fas fa-user"></i>
            <input type="text" name="username" placeholder="Username" required autofocus>
        </div>
        <div class="input-group">
            <i class="fas fa-lock"></i>
            <input type="password" name="password" placeholder="Password" required>
        </div>
        <div class="input-group">
            <i class="fas fa-lock"></i>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
        </div>

        <button type="submit">Register</button>

        <div class="login-link">
            Already have an account? <a href="login.php">Login</a>
        </div>
    </form>
</div>

</body>
</html>