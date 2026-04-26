<?php
require_once 'config.php';
redirectIfNotLoggedIn();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: dashboard.php"); exit;
}

$stmt = $pdo->prepare("SELECT * FROM items WHERE id = ?");
$stmt->execute([$id]);
$item = $stmt->fetch();
if (!$item) die("Item not found.");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Item Details #<?= $item['id'] ?></title>
    <link rel="stylesheet" href="main.css">
</head>
<body>
<div class="wrapper">
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="header">
            <h2><?= htmlspecialchars($item['name']) ?></h2>
            <div class="header-actions">
                <a href="dashboard.php" class="btn btn-view">Back</a>
                <a href="edit.php?id=<?= $item['id'] ?>" class="btn btn-edit">Edit</a>
            </div>
        </div>

        <div class="item-card-horizontal">
            <div class="item-image">
                <?php if (!empty($item['image_filename']) && file_exists('images/items/' . $item['image_filename'])): ?>
                    <img src="images/items/<?= htmlspecialchars($item['image_filename']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                <?php else: ?>
                    <div class="placeholder">No Image</div>
                <?php endif; ?>
            </div>

            <div class="item-details">
                <p><strong>ID:</strong> <?= $item['id'] ?></p>
                <p><strong>Serial:</strong> <?= htmlspecialchars($item['serial_number'] ?: '-') ?></p>
                <p><strong>Category:</strong> <?= htmlspecialchars($item['category']) ?></p>
                <p><strong>Quantity:</strong> <?= $item['quantity'] ?></p>
                <p><strong>Status:</strong> 
                    <span class="status status-<?= strtolower(str_replace(' ', '-', $item['status'])) ?>">
                        <?= htmlspecialchars($item['status']) ?>
                    </span>
                </p>
                <p><strong>Location:</strong> <?= htmlspecialchars($item['location'] ?: '-') ?></p>
                <p><strong>Description:</strong><br><?= nl2br(htmlspecialchars($item['description'] ?? '')) ?></p>
                <p><strong>Last Updated:</strong> <?= $item['last_updated'] ?></p>

                <div class="borrow-form">
                <?php if ($item['status'] === 'Working' && $item['quantity'] > 0): ?>
                    <form method="post" action="borrow.php">
                        <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                        <input type="text" name="borrower_name" placeholder="Borrower's Name" required>
                        <input type="number" name="quantity" value="1" min="1" max="<?= $item['quantity'] ?>">
                        <button type="submit" class="btn btn-borrow">Borrow</button>
                    </form>
                <?php else: ?>
                    <span class="not-available">Not Available</span>
                <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>