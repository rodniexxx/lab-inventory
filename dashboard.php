<?php
require_once 'config.php';
redirectIfNotLoggedIn();


$search    = trim($_GET['search'] ?? '');
$category  = $_GET['category'] ?? '';
$sort      = $_GET['sort'] ?? 'name';
$order     = $_GET['order'] ?? 'ASC';

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
$order_sql = "ORDER BY $sort $order";

// items
$sql = "SELECT * FROM items $where_sql $order_sql";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// categories dropdown
$cat_stmt = $pdo->query("SELECT DISTINCT category FROM items ORDER BY category");
$categories = $cat_stmt->fetchAll(PDO::FETCH_COLUMN);


// category
$catData = $pdo->query("SELECT category, COUNT(*) as total FROM items GROUP BY category")->fetchAll(PDO::FETCH_ASSOC);
$catLabels = array_column($catData, 'category');
$catTotals = array_column($catData, 'total');

// status
$statusData = $pdo->query("SELECT status, COUNT(*) as total FROM items GROUP BY status")->fetchAll(PDO::FETCH_ASSOC);
$statusLabels = array_column($statusData, 'status');
$statusTotals = array_column($statusData, 'total');

// top products
$productData = $pdo->query("SELECT name, COUNT(*) as total FROM items GROUP BY name ORDER BY total DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
$productLabels = array_column($productData, 'name');
$productTotals = array_column($productData, 'total');
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Dashboard - Computer Lab Inventory</title>
<link rel="stylesheet" href="main.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>
<body>

<div class="wrapper">
<?php include 'sidebar.php'; ?>

<button id="menu-toggle">☰</button>

<div class="main-content">
<header>
    <h1>Casaul Computer Laboratory Inventory</h1>
    <h3>Welcome, <?= htmlspecialchars($_SESSION['username'])?>!</h3>
</header>

    <div style="width:100%; display:block; margin:20px 0; clear:both;">
    <div style="flex-basis:100%;"></div>
    <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:15px;">

        <div style="background:#fff; padding:10px; border-radius:8px; height:260px;">
            <h4 style="font-size:14px; text-align:center; margin-bottom:12px;">Items by Category</h4>
            <canvas id="categoryChart"></canvas>
        </div>

        <div style="background:#fff; padding:10px; border-radius:8px; height:260px; display:flex; flex-direction:column;">

        <h4 style="font-size:14px; text-align:center; margin-bottom:10px;">
            Items by Status
        </h4>
    <div style="flex:1; display:flex; align-items:center; justify-content:center;">
        <div style="width:200px; height:200px; position:relative;">
            <canvas id="statusChart"></canvas>
        </div>
    </div>

</div>

        <div style="background:#fff; padding:10px; border-radius:8px; height:260px;">
            <h4 style="font-size:14px; text-align:center; margin-bottom:12px;">Top Products</h4>
            <canvas id="productChart"></canvas>
        </div>

    </div>


   <div class="inventory-cards">
    <?php foreach ($items as $item): ?>
        <div class="card">

            <div class="card-content">
                <h3><?= htmlspecialchars($item['name']) ?></h3>
                <p>Serial: <?= htmlspecialchars($item['serial_number']) ?></p>
                <p>Category: <?= htmlspecialchars($item['category']) ?></p>
                <p>Quantity: <?= $item['quantity'] ?></p>
                <p><span class="status status-<?= strtolower(str_replace(' ', '-', $item['status'])) ?>"><?= htmlspecialchars($item['status']) ?></span></p>

            <div style="margin-top:10px; display:flex; flex-direction:column; gap:6px;">

                    <a class="btn btn-view"
                    href="view.php?id=<?= $item['id'] ?>">
                        View
                    </a>

                    <a class="btn btn-edit"
                    href="edit.php?id=<?= $item['id'] ?>">
                        Edit
                    </a>

                    <a class="btn btn-delete"
                    href="delete.php?id=<?= $item['id'] ?>"
                    onclick="return confirm('Are you sure you want to delete this item?')">
                        Delete
                    </a>

            </div>
         </div>

     </div>
        <?php endforeach; ?>
        </div>
    </div>

</div>

</div>
</div>


<script>

const toggleBtn = document.getElementById("menu-toggle");
const sidebar = document.getElementById("sidebar");
const overlay = document.getElementById("overlay");

toggleBtn.addEventListener("click", function () {
    sidebar.classList.toggle("active");
    overlay.classList.toggle("active");
});

overlay.addEventListener("click", function () {
    sidebar.classList.remove("active");
    overlay.classList.remove("active");
});

// Category Chart
new Chart(document.getElementById('categoryChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($catLabels) ?>,
        datasets: [{
            label: 'Items',
            data: <?= json_encode($catTotals) ?>,
            backgroundColor: [
                '#5b9bfc', 
                '#6cfd7b', 
                '#ffd467', 
                '#fd6868', 
                '#a569ff', 
                '#70f1ff'  
            ]
        }]
    },
    options: {
        plugins: { legend: { display: false } }
    }
});

new Chart(document.getElementById('statusChart'), {
    type: 'pie',
    data: {
        labels: <?= json_encode($statusLabels) ?>,
        datasets: [{
            data: <?= json_encode($statusTotals) ?>,
            backgroundColor: [
                '#5b9bfc', 
                '#6cfd7b', 
                '#ffd467', 
                '#fd6868', 
                '#a569ff', 
                '#70f1ff' 
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Top Products
new Chart(document.getElementById('productChart'), {
    type: 'line',
    data: {
        labels: <?= json_encode($productLabels) ?>,
        datasets: [{
            label: 'Top Products',
            data: <?= json_encode($productTotals) ?>,
            borderColor: '#fb82ff',
            backgroundColor: 'rgba(83, 41, 85, 0.2)',
            fill: true,
            tension: 0.4
        }]
    }
});
</script>

</body>
</html>