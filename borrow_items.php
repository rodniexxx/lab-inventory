<?php
require_once 'config.php';
redirectIfNotLoggedIn();

// list kang borrowed items 
$borrow_stmt = $pdo->query("
    SELECT b.id AS borrow_id, b.borrower_name, b.quantity AS borrow_qty, b.borrow_date, b.return_date, b.status,
           i.id AS item_id, i.name AS item_name, i.serial_number
    FROM borrows b
    JOIN items i ON b.item_id = i.id
    ORDER BY b.borrow_date DESC
");
$borrows = $borrow_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Computer Lab Inventory</title>
    <link rel="stylesheet" href="main.css">
</head>
<body>

<div class="wrapper">

    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
    <h2>Borrowed Items</h2>

    <?php if (empty($borrows)): ?>
        <p class="info">No borrowed items yet.</p>
    <?php else: ?>
        <div class="card-table">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Borrower Name</th>
                        <th>Item</th>
                        <th>Serial / Asset</th>
                        <th>Quantity</th>
                        <th>Status</th>
                        <th>Borrow Date</th>
                        <th>Return Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($borrows as $b): ?>
                    <tr>
                        <td><?= $b['borrow_id'] ?></td>
                        <td><?= htmlspecialchars($b['borrower_name']) ?></td>
                        <td><?= htmlspecialchars($b['item_name']) ?></td>
                        <td><?= htmlspecialchars($b['serial_number'] ?: '-') ?></td>
                        <td><?= $b['borrow_qty'] ?></td>
                        <td class="status-<?= strtolower($b['status']) ?>">
                            <?= htmlspecialchars($b['status']) ?>
                        </td>
                        <td><?= $b['borrow_date'] ?></td>
                        <td><?= $b['return_date'] ?? '-' ?></td>
                        <td>
                            <?php if ($b['status'] === 'Borrowed'): ?>
                                <a href="return.php?id=<?= $b['borrow_id'] ?>" 
                                   class="btn btn-return"
                                   onclick="return confirm('Mark this item as returned?')">Return</a>
                            <?php else: ?>
                                <span style="color:#777;">Completed</span>
                            <?php endif; ?>

                            <a href="delete_borrow.php?id=<?= $b['borrow_id'] ?>" 
                               class="btn btn-delete"
                               onclick="return confirm('Are you sure you want to delete this borrow record?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
</div>
</body