<?php

$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar">
    <ul>
        <li><a href="dashboard.php" class="<?= $current_page === 'dashboard.php' ? 'active' : '' ?>">Dashboard</a></li>
        <li><a href="dashboard1.php" class="<?= $current_page === 'dashboard1.php' ? 'active' : '' ?>">List Of All Items</a></li>
        <li><a href="borrow_items.php" class="<?= $current_page === 'borrow_items.php' ? 'active' : '' ?>">Borrowed Items</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</div>

<div id="overlay"></div>