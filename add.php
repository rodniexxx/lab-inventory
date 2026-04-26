<?php
require_once 'config.php';
redirectIfNotLoggedIn();

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['name'] ?? '');
    $serial      = trim($_POST['serial_number'] ?? '');
    $category    = $_POST['category'] ?? 'Other';
    $quantity    = (int)($_POST['quantity'] ?? 1);
    $status      = $_POST['status'] ?? 'Working';
    $location    = trim($_POST['location'] ?? 'Lab 101');
    $description = trim($_POST['description'] ?? '');
    $image_filename = null;

    if (empty($name)) {
        $error = "Item name is required.";
    }

    //  img upload
    if (!$error && isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg','jpeg','png','gif'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            $error = "Only JPG, JPEG, PNG & GIF files are allowed.";
        } elseif ($_FILES['image']['size'] > 2000000) {
            $error = "Image is too large (max 2MB).";
        } else {
            $new_name = 'item-' . time() . '-' . uniqid() . '.' . $ext;
            $upload_path = 'images/items/' . $new_name;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $image_filename = $new_name;
            } else {
                $error = "Failed to upload image.";
            }
        }
    }

    if (!$error) {
        // check kung serial no. alr exist
        if ($serial) {
            $stmt = $pdo->prepare("SELECT id FROM items WHERE serial_number = ?");
            $stmt->execute([$serial]);
            if ($stmt->fetch()) {
                $error = "Serial number '$serial' already exists.";
            }
        }
    }

    if (!$error) {
        // insert incl image
        $stmt = $pdo->prepare("
            INSERT INTO items 
            (name, serial_number, category, quantity, status, location, description, image_filename)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$name, $serial ?: null, $category, $quantity, $status, $location, $description, $image_filename]);
        $success = "Item added successfully!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Item</title>
    <link rel="stylesheet" href="main.css">
</head>
<body>
<div class="wrapper">
<?php include 'sidebar.php'; ?>
    <div class="container">
        <h2>Add New Item</h2>

        <?php if ($error): ?><p class="error"><?=htmlspecialchars($error)?></p><?php endif; ?>
        <?php if ($success): ?><p class="success"><?=htmlspecialchars($success)?></p><?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label>Name / Model *</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>Serial Number / Asset Tag</label>
                <input type="text" name="serial_number">
            </div>
            <div class="form-group">
                <label>Category</label>
                <select name="category">
                    <option value="Computer">Computer</option>
                    <option value="Monitor">Monitor</option>
                    <option value="Keyboard">Keyboard</option>
                    <option value="Mouse">Mouse</option>
                    <option value="Printer">Printer</option>
                    <option value="Printer">Projector</option>
                    <option value="Printer">Modem</option>
                    <option value="Other" selected>Other</option>
                </select>
            </div>
            <div class="form-group">
                <label>Quantity</label>
                <input type="text" name="quantity" value="1" min="1">
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <option value="Working" selected>Working</option>
                    <option value="Damaged">Damaged</option>
                    <option value="Disposed">Disposed</option>
                    <option value="In Repair">In Repair</option>
                </select>
            </div>
            <div class="form-group">
                <label>Location</label>
                <select name="location">
                    <option value="Computer Laboratory" selected>Computer Laboratory</option>
                    <option value="Accounting">Accounting</option>
                    <option value="Registrar">Registrar</option>
                    <option value="Library">Library</option>
                </select>
            </div>
            <div class="form-group">
                <label>Description / Notes</label>
                <textarea name="description" rows="4"></textarea>
            </div>

            <div class="form-group">
                 <label>Barcode Image</label>
                <input type="file" name="image" accept="image/*">
                <small>jpg, png, gif — max 2MB recommended</small>
            </div>
            <button type="submit">Save Item</button>
        </form>
    </div>
    </div>
</body>
</html>