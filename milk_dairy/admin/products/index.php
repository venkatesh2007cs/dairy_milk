<?php
session_start();
require_once '../../database/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$result = $conn->query("SELECT * FROM products ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Products - Smart Dairy</title>

<style>
*{box-sizing:border-box;font-family:Arial,sans-serif}
body{margin:0;background:#f0fdf4;color:#1f2937}
.header{
    background:linear-gradient(135deg,#087f5b,#12b886);
    color:white;padding:25px 35px
}
.header h1{margin:0}
.header p{margin:7px 0 0}
.container{padding:30px}
.topbar{
    display:flex;justify-content:space-between;
    align-items:center;margin-bottom:20px
}
.topbar h2{color:#087f5b}
.btn{
    background:#087f5b;color:white;text-decoration:none;
    padding:11px 18px;border-radius:8px;font-weight:bold
}
.card{
    background:white;padding:20px;border-radius:15px;
    box-shadow:0 5px 20px rgba(0,0,0,.08);
    overflow-x:auto
}
table{width:100%;border-collapse:collapse;min-width:700px}
th{background:#087f5b;color:white;padding:13px;text-align:left}
td{padding:13px;border-bottom:1px solid #e5e7eb}
tr:hover{background:#f0fdf4}
.active{color:#087f5b;font-weight:bold}
.inactive{color:#dc2626;font-weight:bold}
.empty{text-align:center;padding:35px;color:#6b7280}
</style>
</head>

<body>

<div class="header">
    <h1>🥛 Products</h1>
    <p>Manage dairy products and stock</p>
</div>

<div class="container">

<div class="topbar">
    <h2>Product List</h2>
    <a href="add.php" class="btn">+ Add Product</a>
</div>

<div class="card">

<?php if ($result && $result->num_rows > 0): ?>

<table>
<tr>
    <th>ID</th>
    <th>Product Name</th>
    <th>Unit</th>
    <th>Quantity</th>
    <th>Price</th>
    <th>Status</th>
    <th>Created At</th>
</tr>

<?php while ($row = $result->fetch_assoc()): ?>

<tr>
    <td><?= htmlspecialchars($row['id']) ?></td>

    <td><?= htmlspecialchars($row['name']) ?></td>

    <td><?= htmlspecialchars($row['unit'] ?? 'litre') ?></td>

    <td><?= number_format($row['quantity'] ?? 0, 2) ?></td>

    <td>₹<?= number_format($row['price'] ?? 0, 2) ?></td>

    <td>
        <?php if ($row['status'] === 'active'): ?>
            <span class="active">✓ Active</span>
        <?php else: ?>
            <span class="inactive">● Inactive</span>
        <?php endif; ?>
    </td>

    <td><?= htmlspecialchars($row['created_at']) ?></td>
</tr>

<?php endwhile; ?>

</table>

<?php else: ?>

<div class="empty">
    <h3>No products found</h3>
    <p>Click "Add Product" to add your first dairy product.</p>
</div>

<?php endif; ?>

</div>
</div>

</body>
</html>