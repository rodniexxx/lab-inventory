<?php
require_once 'config.php';
redirectIfNotLoggedIn();


$search    = trim($_GET['search']    ?? '');
$category  = $_GET['category']  ?? '';
$sort      = $_GET['sort']      ?? 'name';
$order     = $_GET['order']     ?? 'ASC';

$valid_sorts = ['name', 'category', 'status', 'last_updated', 'quantity'];
$sort = in_array($sort, $valid_sorts) ? $sort : 'name';

$order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';


$where = [];
$params = [];

if ($search !== '') {
    $where[] = "(name LIKE ? OR serial_number LIKE ? OR description LIKE ?)";
    $like = "%$search%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

if ($category !== '' && $category !== 'all') {
    $where[] = "category = ?";
    $params[] = $category;
}

$where_sql = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";

// order
$order_sql = "ORDER BY $sort $order";

// items
$sql = "SELECT * FROM items $where_sql $order_sql";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);


// categories dropdown
$cat_stmt = $pdo->query("SELECT DISTINCT category FROM items ORDER BY category");
$categories = $cat_stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>list of All Items</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="wrapper">

    <?php include 'sidebar.php'; ?>
    

    <div class="main-content">
        <div class="controls">
            <form method="get" style="display:flex; gap:15px; flex-wrap:wrap;">
                <input type="search" name="search" placeholder="Search name, serial, notes..." 
                       value="<?= htmlspecialchars($search) ?>">
                
                <select name="category">
                    <option value="all" <?= $category===''||$category==='all'?'selected':'' ?>>All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat) ?>" <?= $category===$cat?'selected':'' ?>>
                            <?= htmlspecialchars($cat) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="sort">
                    <option value="name"        <?= $sort==='name'       ?'selected':'' ?>>Name</option>
                    <option value="category"    <?= $sort==='category'   ?'selected':'' ?>>Category</option>
                    <option value="status"      <?= $sort==='status'     ?'selected':'' ?>>Status</option>
                    <option value="quantity"    <?= $sort==='quantity'   ?'selected':'' ?>>Quantity</option>
                    <option value="last_updated"<?= $sort==='last_updated'?'selected':'' ?>>Last Updated</option>
                </select>

                <select name="order">
                    <option value="ASC"  <?= $order==='ASC' ?'selected':'' ?>>↑ Ascending</option>
                    <option value="DESC" <?= $order==='DESC'?'selected':'' ?>>↓ Descending</option>
                </select>

                <button type="submit" class="btn btn-add">Search</button>
                <a href="dashboard.php" class="btn">Reset</a>
            </form>

            <a href="excel.php?search=<?= urlencode($search) ?>&category=<?= urlencode($category) ?>&sort=<?= urlencode($sort) ?>&order=<?= urlencode($order) ?>" 
                class="btn btn-download">
                Export
            </a>
            
            <a href="add.php" class="btn btn-add" style="margin-left:auto;">+ Add New Item</a>
        </div>

       <div class="inventory-table">
    <?php if (empty($items)): ?>
        <p class="info">No items found matching your criteria.</p>
    <?php else: ?>
       <table>
    <thead>
        <tr>
            <th>Barcode</th>
            <th>Name</th>
            <th>Serial Number</th>
            <th>Category</th>
            <th>Quantity</th>
            <th>Status</th>
            <th>Location</th>
            <th>Last Updated</th>
            <th>Actions</th>
        </tr>
    </thead>

    <tbody>
        <?php foreach ($items as $item): ?>
            <tr>
                <td>
                    <?php if (!empty($item['image_filename']) && file_exists('images/items/' . $item['image_filename'])): ?>
                        <img src="images/items/<?= htmlspecialchars($item['image_filename']) ?>" 
                             width="50" height="50" style="object-fit:cover;">
                    <?php else: ?>
                        No Image
                    <?php endif; ?>
                </td>

                <td><?= htmlspecialchars($item['name']) ?></td>
                <td><?= htmlspecialchars($item['serial_number'] ?: '-') ?></td>
                <td><?= htmlspecialchars($item['category']) ?></td>

                <td><?= htmlspecialchars($item['quantity']) ?></td>

                <td class="status status-<?= strtolower(str_replace(' ', '-', $item['status'])) ?>">
                    <?= htmlspecialchars($item['status']) ?>
                </td>

                <td><?= htmlspecialchars($item['location'] ?: '-') ?></td>

                <td><?= htmlspecialchars($item['last_updated']) ?></td>

                <td>
                    <a href="view.php?id=<?= $item['id'] ?>" class="btn btn-view">View</a>
                    <a href="edit.php?id=<?= $item['id'] ?>" class="btn btn-edit">Edit</a>
                    <a href="delete.php?id=<?= $item['id'] ?>" 
                       class="btn btn-delete" 
                       onclick="return confirm('Really delete this item?')">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
    <?php endif; ?>
</div>

</body>
</html>