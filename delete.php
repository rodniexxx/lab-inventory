<?php
require_once 'config.php';
redirectIfNotLoggedIn();

$id = (int)($_GET['id'] ?? 0);
if ($id > 0) {
    $pdo->prepare("DELETE FROM borrows WHERE item_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM items WHERE id = ?")->execute([$id]);
}
header("Location: dashboard.php");
exit;