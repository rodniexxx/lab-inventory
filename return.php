<?php
require_once 'config.php';
redirectIfNotLoggedIn();

$borrow_id = (int)($_GET['id'] ?? 0);
if ($borrow_id <= 0) die("Invalid borrow ID.");

// Get borrow 
$stmt = $pdo->prepare("SELECT * FROM borrows WHERE id = ?");
$stmt->execute([$borrow_id]);
$borrow = $stmt->fetch();
if (!$borrow) die("Borrow record not found.");
if ($borrow['status'] !== 'Borrowed') die("Item already returned.");

// Update borrow 
$stmt = $pdo->prepare("UPDATE borrows SET status='Returned', return_date=NOW() WHERE id = ?");
$stmt->execute([$borrow_id]);

// Restore 
$stmt = $pdo->prepare("UPDATE items SET quantity = quantity + ? WHERE id = ?");
$stmt->execute([$borrow['quantity'], $borrow['item_id']]);

header("Location: dashboard.php?msg=Item marked as returned!");
exit;