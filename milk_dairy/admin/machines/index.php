<?php
session_start();
require_once '../../database/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$result = $conn->query("
    SELECT *
    FROM machines
    ORDER BY id DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Machines - Smart Dairy</title>

<style>
*{box-sizing:border-box;font-family:Arial,sans-serif}

body{
    margin:0;
    background:#f0fdf4;
    color:#1f2937;
}

.header{
    background:linear-gradient(135deg,#087f5b,#12b886);
    color:white;
    padding:25px 35px;
}

.header h1{
    margin:0;
    font-size:28px;
}

.header p{
    margin:7px 0 0;
    opacity:.9;
}

.container{
    padding:30px;
}

.topbar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:20px;
}

.topbar h2{
    color:#087f5b;
    margin:0;
}

.btn{
    background:#087f5b;
    color:white;
    text-decoration:none;
    padding:11px 18px;
    border-radius:8px;
    font-weight:bold;
}

.btn:hover{
    background:#056b4c;
}

.card{
    background:white;
    padding:20px;
    border-radius:15px;
    box-shadow:0 5px 20px rgba(0,0,0,.08);
    overflow-x:auto;
}

table{
    width:100%;
    border-collapse:collapse;
    min-width:900px;
}

th{
    background:#087f5b;
    color:white;
    padding:13px;
    text-align:left;
}

td{
    padding:13px;
    border-bottom:1px solid #e5e7eb;
}

tr:hover{
    background:#f0fdf4;
}

.status{
    font-weight:bold;
}

.running{
    color:#087f5b;
}

.idle{
    color:#2563eb;
}

.maintenance{
    color:#d97706;
}

.inactive{
    color:#dc2626;
}

.empty{
    text-align:center;
    padding:35px;
    color:#6b7280;
}

@media(max-width:700px){
    .container{
        padding:15px;
    }

    .topbar{
        flex-direction:column;
        align-items:flex-start;
        gap:15px;
    }
}
</style>
</head>

<body>

<div class="header">
    <h1>🔧 Machines</h1>
    <p>Manage dairy processing machines and maintenance dates</p>
</div>

<div class="container">

<div class="topbar">
    <h2>Machine List</h2>

    <a href="add.php" class="btn">
        + Add Machine
    </a>
</div>

<div class="card">

<?php if ($result && $result->num_rows > 0): ?>

<table>

<tr>
    <th>ID</th>
    <th>Machine Name</th>
    <th>Machine Code</th>
    <th>Status</th>
    <th>Last Maintenance</th>
    <th>Next Maintenance</th>
    <th>Created At</th>
</tr>

<?php while($row = $result->fetch_assoc()): ?>

<tr>

<td>
    <?= htmlspecialchars($row['id']) ?>
</td>

<td>
    <?= htmlspecialchars($row['machine_name']) ?>
</td>

<td>
    <?= $row['machine_code']
        ? htmlspecialchars($row['machine_code'])
        : '-' ?>
</td>

<td>

<?php
$status = $row['status'];
?>

<span class="status <?= htmlspecialchars($status) ?>">

<?php
if ($status === 'running') {
    echo '🟢 Running';
} elseif ($status === 'idle') {
    echo '🔵 Idle';
} elseif ($status === 'maintenance') {
    echo '🟠 Maintenance';
} else {
    echo '🔴 Inactive';
}
?>

</span>

</td>

<td>
    <?= $row['last_maintenance']
        ? htmlspecialchars($row['last_maintenance'])
        : '-' ?>
</td>

<td>
    <?= $row['next_maintenance']
        ? htmlspecialchars($row['next_maintenance'])
        : '-' ?>
</td>

<td>
    <?= htmlspecialchars($row['created_at']) ?>
</td>

</tr>

<?php endwhile; ?>

</table>

<?php else: ?>

<div class="empty">

    <h3>No machines found</h3>

    <p>
        Click "Add Machine" to register your first machine.
    </p>

</div>

<?php endif; ?>

</div>

</div>

</body>
</html>