<?php
session_start();
require_once '../../database/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$sql = "
    SELECT 
        b.id,
        b.invoice_number,
        c.name AS customer_name,
        ba.batch_number,
        b.quantity_litres,
        b.rate_per_litre,
        b.subtotal,
        b.tax_percent,
        b.tax_amount,
        b.grand_total,
        b.invoice_date,
        b.payment_status
    FROM bills b
    INNER JOIN customers c ON b.customer_id = c.id
    LEFT JOIN batches ba ON b.batch_id = ba.id
    ORDER BY b.id DESC
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Billing Management</title>

<style>
* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f1f8f5;
    color: #243b35;
}

.header {
    background: linear-gradient(135deg, #087f5b, #12b886);
    color: white;
    padding: 22px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.header h1 {
    margin: 0;
    font-size: 26px;
}

.header p {
    margin: 5px 0 0;
    opacity: .9;
}

.btn {
    text-decoration: none;
    color: white;
    background: #087f5b;
    padding: 11px 18px;
    border-radius: 8px;
    font-weight: bold;
}

.btn:hover {
    background: #056b4d;
}

.container {
    padding: 30px;
}

.card {
    background: white;
    border-radius: 15px;
    padding: 22px;
    box-shadow: 0 5px 20px rgba(0,0,0,.08);
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
    min-width: 1100px;
}

th {
    background: #e6f7f1;
    color: #087f5b;
    padding: 14px;
    text-align: left;
}

td {
    padding: 13px;
    border-bottom: 1px solid #eee;
}

tr:hover {
    background: #f8fffc;
}

.status {
    padding: 6px 10px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: bold;
}

.pending {
    background: #fff3cd;
    color: #856404;
}

.paid {
    background: #d1e7dd;
    color: #0f5132;
}

.partial {
    background: #cfe2ff;
    color: #084298;
}

.view {
    background: #087f5b;
    color: white;
    padding: 7px 12px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 13px;
}

.empty {
    text-align: center;
    padding: 40px;
    color: #777;
}
</style>
</head>

<body>

<div class="header">
    <div>
        <h1>🧾 Billing Management</h1>
        <p>Customer invoices and payment details</p>
    </div>

    <a href="add.php" class="btn">+ Create Bill</a>
</div>

<div class="container">

<div class="card">

<?php if ($result && $result->num_rows > 0): ?>

<table>

<thead>
<tr>
    <th>Invoice No</th>
    <th>Customer</th>
    <th>Batch</th>
    <th>Quantity</th>
    <th>Rate/Litre</th>
    <th>Subtotal</th>
    <th>Tax</th>
    <th>Grand Total</th>
    <th>Date</th>
    <th>Status</th>
    <th>Action</th>
</tr>
</thead>

<tbody>

<?php while ($row = $result->fetch_assoc()): ?>

<tr>

<td>
    <strong><?= htmlspecialchars($row['invoice_number']) ?></strong>
</td>

<td>
    <?= htmlspecialchars($row['customer_name']) ?>
</td>

<td>
    <?= htmlspecialchars($row['batch_number'] ?? '-') ?>
</td>

<td>
    <?= number_format($row['quantity_litres'], 2) ?> L
</td>

<td>
    ₹<?= number_format($row['rate_per_litre'], 2) ?>
</td>

<td>
    ₹<?= number_format($row['subtotal'], 2) ?>
</td>

<td>
    <?= number_format($row['tax_percent'], 2) ?>%
</td>

<td>
    <strong>₹<?= number_format($row['grand_total'], 2) ?></strong>
</td>

<td>
    <?= htmlspecialchars($row['invoice_date']) ?>
</td>

<td>

<?php
$status = $row['payment_status'];

if ($status === 'Paid') {
    echo '<span class="status paid">Paid</span>';
} elseif ($status === 'Partially Paid') {
    echo '<span class="status partial">Partially Paid</span>';
} else {
    echo '<span class="status pending">Pending</span>';
}
?>

</td>

<td>
    <a class="view"
       href="view.php?id=<?= (int)$row['id'] ?>">
       View
    </a>
</td>

</tr>

<?php endwhile; ?>

</tbody>

</table>

<?php else: ?>

<div class="empty">
    <h2>No Bills Found</h2>
    <p>Create your first customer invoice.</p>
    <br>
    <a href="add.php" class="btn">+ Create First Bill</a>
</div>

<?php endif; ?>

</div>

</div>

</body>
</html>