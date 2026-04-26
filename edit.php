<?php
require_once 'config.php';
redirectIfNotLoggedIn();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: dashboard.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM items WHERE id = ?");
$stmt->execute([$id]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    die("Item not found.");
}

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name          = trim($_POST['name'] ?? '');
    $serial        = trim($_POST['serial_number'] ?? '');
    $category      = $_POST['category'] ?? 'Other';
    $quantity      = (int)($_POST['quantity'] ?? 1);
    $status        = $_POST['status'] ?? 'Working';
    $location      = trim($_POST['location'] ?? '');
    $description   = trim($_POST['description'] ?? '');

    if (empty($name)) {
        $error = "Item name is required.";
    } elseif ($quantity < 1) {
        $error = "Quantity must be at least 1.";
    } else {
        try {
            $stmt = $pdo->prepare("
                UPDATE items SET
                    name = ?,
                    serial_number = ?,
                    category = ?,
                    quantity = ?,
                    status = ?,
                    location = ?,
                    description = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $name,
                $serial ?: null,
                $category,
                $quantity,
                $status,
                $location,
                $description,
                $id
            ]);

            $success = "Item updated successfully!";

            // Refresh item from database
            $stmt = $pdo->prepare("SELECT * FROM items WHERE id = ?");
            $stmt->execute([$id]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }

    $image_filename = $item['image_filename']; 

if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

    if (in_array($ext, $allowed) && $_FILES['image']['size'] <= 2000000) {
        $new_name = 'item-' . time() . '-' . uniqid() . '.' . $ext;
        $upload_path = 'images/items/' . $new_name;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
            
            if ($image_filename && file_exists('images/items/' . $image_filename)) {
                unlink('images/items/' . $image_filename);
            }
            $image_filename = $new_name;
        } else {
            $error = "Image upload failed.";
        }
    } else {
        $error = "Invalid image (allowed: jpg/png/gif, max 2MB).";
    }
}


$stmt = $pdo->prepare("
    UPDATE items SET
        name = ?, serial_number = ?, category = ?, quantity = ?,
        status = ?, location = ?, description = ?, image_filename = ?
    WHERE id = ?
");
$stmt->execute([$name, $serial ?: null, $category, $quantity, $status, $location, $description, $image_filename, $id]);


}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Item #<?= $item['id'] ?></title>
    <link rel="stylesheet" href="main.css">
</head>
<body>
<div class="wrapper">
<?php include 'sidebar.php'; ?>
    <div class="container">
        <h2>Edit Item #<?= $item['id'] ?></h2>
        <div style="margin: 10px 0;">
    <a href="dashboard.php"
       style="background:#74A9CF;color:#fff;padding:8px 14px;border-radius:6px;text-decoration:none;display:inline-block;">
        ← Back
    </a>
</div>
        <?php if ($error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <?php if ($success): ?>
            <p class="success"><?= htmlspecialchars($success) ?></p>
        <?php endif; ?>

        <form method="post">
            <div class="form-group">
                <label>Name / Model *</label>
                <input type="text" name="name" value="<?= htmlspecialchars($item['name']) ?>" required autofocus>
            </div>

            <div class="form-group">
                <label>Serial Number / Asset Tag</label>
                <input type="text" name="serial_number" value="<?= htmlspecialchars($item['serial_number'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Category</label>
                <select name="category" required>
                    <?php
                    $categories = ['Computer', 'Monitor', 'Keyboard', 'Mouse', 'Printer', 'Other'];
                    foreach ($categories as $cat) {
                        $selected = ($item['category'] === $cat) ? 'selected' : '';
                        echo "<option value=\"$cat\" $selected>$cat</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label>Quantity</label>
                <input type="number" name="quantity" value="<?= (int)$item['quantity'] ?>" min="1" required>
            </div>

            <div class="form-group">
                <label>Status</label>
                <select name="status" required>
                    <?php
                    $statuses = ['Working', 'Damaged', 'Disposed', 'In Repair'];
                    foreach ($statuses as $s) {
                        $selected = ($item['status'] === $s) ? 'selected' : '';
                        echo "<option value=\"$s\" $selected>$s</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label>Location</label>
                <input type="text" name="location" value="<?= htmlspecialchars($item['location'] ?? 'Lab 101') ?>" required>
            </div>

            <div class="form-group">
                <label>Description / Notes</label>
                <textarea name="description" rows="4"><?= htmlspecialchars($item['description'] ?? '') ?></textarea>
            </div>
           
            <div class="form-group">
    <label>Current Image</label><br>
    <?php if (!empty($item['image_filename']) && file_exists('images/items/' . $item['image_filename'])): ?>
        <img src="images/items/<?= htmlspecialchars($item['image_filename']) ?>" 
             alt="Current item image" style="max-width:200px; border:1px solid #444; border-radius:4px;">
        <br><small>Leave empty to keep current image</small>
    <?php else: ?>
        <p><em>No image uploaded yet</em></p>
    <?php endif; ?>
</div>

<div class="form-group">
    <label>Change Image (optional)</label>
    <input type="file" name="image" accept="image/*">
    <small>jpg, png, gif — max 2MB</small>
</div>


            <button type="submit">Save Changes</button>
        </form>
    </div>
</div>
</body>
</html>