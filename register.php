<?php
require_once 'config.php';
redirectIfNotLoggedIn();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_id'])) {
        $deleteId = intval($_POST['delete_id']);

        if ($deleteId === intval($_SESSION['user_id'])) {
            $error = "You cannot delete the account you are currently logged in with.";
        } else {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$deleteId]);

            if ($stmt->rowCount()) {
                $success = "Account deleted successfully.";
            } else {
                $error = "Could not delete the selected account.";
            }
        }
    } else {
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

                $success = "Account created successfully!";
            }
        }
    }
}

// Fetch all users
$stmt = $pdo->query("SELECT id, username FROM users ORDER BY username");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Accounts - Lab Inventory</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="main.css">
</head>
<body>

<div class="wrapper">
<?php include 'sidebar.php'; ?>

<button id="menu-toggle">☰</button>

<div class="main-content">
<header>
    <h1>Manage Accounts</h1>
</header>

<div class="account-grid">
    <div class="register-card">
        <h2>Create New Account</h2>

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

            <button type="submit">Create Account</button>
        </form>
    </div>

    <div class="card-table account-table">
        <div class="table-heading">
            <h2>Existing Accounts</h2>
        </div>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['id']) ?></td>
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td>
                        <?php if ($user['id'] !== intval($_SESSION['user_id'])): ?>
                            <form method="post" class="inline-form" onsubmit="return confirm('Are you sure you want to delete this account?');">
                                <input type="hidden" name="delete_id" value="<?= htmlspecialchars($user['id']) ?>">
                                <button type="submit" class="btn btn-delete">Delete</button>
                            </form>
                        <?php else: ?>
                            <span class="current-user">Current</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</div>
</div>
</body>
</html>