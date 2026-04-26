<?php
require_once 'config.php';
redirectIfNotLoggedIn();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: borrow_items.php");
    exit;
}

$borrow_id = (int)$_GET['id'];

try {
    
    $stmt = $pdo->prepare("DELETE FROM borrows WHERE id = ?");
    $stmt->execute([$borrow_id]);
    header("Location: borrow_items.php?msg=deleted");
} catch (PDOException $e) {
    die("Error deleting record: " . $e->getMessage());
}
?>