<?php
require_once 'config.php';
redirectIfNotLoggedIn();

// filters (same logic as your page)
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

$where_sql = count($where) ? "WHERE " . implode(" AND ", $where) : "";
$order_sql = "ORDER BY $sort $order";

$sql = "SELECT * FROM items $where_sql $order_sql";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Set headers for Excel download (CSV format)
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=inventory_items.csv');

$output = fopen('php://output', 'w');

// column headers
fputcsv($output, [
    'Name',
    'Serial Number',
    'Category',
    'Quantity',
    'Status',
    'Location',
    'Last Updated'
]);

// data rows
foreach ($items as $item) {
    fputcsv($output, [
        $item['name'],
        $item['serial_number'],
        $item['category'],
        $item['quantity'],
        $item['status'],
        $item['location'],
        $item['last_updated']
    ]);
}

fclose($output);
exit;