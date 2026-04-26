<?php
require_once 'config.php';
redirectIfNotLoggedIn();

$item_id = (int)($_POST['item_id'] ?? 0);
$borrower_name = trim($_POST['borrower_name'] ?? '');
$quantity = (int)($_POST['quantity'] ?? 1);

if ($item_id <= 0 || empty($borrower_name) || $quantity <= 0) {
    die("Invalid request.");
}

// check kung available ang items
$stmt = $pdo->prepare("SELECT quantity, status FROM items WHERE id = ?");
$stmt->execute([$item_id]);
$item = $stmt->fetch();

if (!$item) die("Item not found.");
if ($item['status'] !== 'Working' || $item['quantity'] < $quantity) {
    die("Item not available in requested quantity.");
}

// borrow record
$stmt = $pdo->prepare("
    INSERT INTO borrows (borrower_name, item_id, quantity)
    VALUES (?, ?, ?)
");
$stmt->execute([$borrower_name, $item_id, $quantity]);

// Update
$stmt = $pdo->prepare("UPDATE items SET quantity = quantity - ? WHERE id = ?");
$stmt->execute([$quantity, $item_id]);

header("Location: dashboard.php?msg=Borrow recorded successfully!");
exit;