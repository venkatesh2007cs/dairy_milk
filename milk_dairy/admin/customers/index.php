<?php
session_start();
require_once '../../database/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$result = $conn->query("
    SELECT id, customer_code, name, customer_type,
           phone, email, address, status, created_at
    FROM customers
    ORDER BY id DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Customer Management</title>

<style>
*{box-sizing:border-box}

body{
    margin:0;
    font-family:Arial,sans-serif;
    background:#f4f8f6;
    color:#263238;
}

.header{
    background:linear-gradient(135deg,#087f5b,#12b886);
    color:white;
    padding:24px 30px;
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

.top-bar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:22px;
}

.top-bar h2{
    margin:0;
    color:#087f5b;
}

.btn{
    text-decoration:none;
    background:#087f5b;
    color:white;
    padding:11px 18px;
    border-radius:8px;
    font-weight:bold;
}

.btn:hover{
    background:#066b4d;
}

.table-box{
    background:white;
    border-radius:14px;
    padding:20px;
    box-shadow:0 5px 18px rgba(0,0,0,.08);
    overflow-x:auto;
}

table{
    width:100%;
    min-width:1000px;
    border-collapse:collapse;
}

th{
    background:#087f5b;
    color:white;
    padding:13px;
    text-align:left;
}

td{
    padding:13px;
    border-bottom:1px solid #e8eeee;
}

tr:hover{
    background:#f3fbf7;
}

.badge{
    padding:6px 10px;
    border-radius:20px;
    font-size:12px;
    font-weight:bold;
}

.active{
    background:#d3f9d8;
    color:#087f5b;
}

.inactive{
    background:#ffe3e3;
    color:#c92a2a;
}

.company{
    background:#dbeafe;
    color:#1d4ed8;
}

.retail{
    background:#fff3bf;
    color:#946200;
}

.empty{
    text-align:center;
    padding:30px;
    color:#777;
}
</style>
</head>

<body>

<div class="header">
    <h1>🏢 Customer Management</h1>
    <p>Manage dairy companies and customers</p>
</div>

<div class="container">

    <div class="top-bar">
        <h2>Customer List</h2>

        <a href="add.php" class="btn">+ Add Customer</a>
    </div>

    <div class="table-box">

        <table>

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Customer Code</th>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Address</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>

            <?php if ($result && $result->num_rows > 0): ?>

                <?php while ($row = $result->fetch_assoc()): ?>

                    <tr>

                        <td><?= (int)$row['id'] ?></td>

                        <td>
                            <strong>
                                <?= htmlspecialchars($row['customer_code']) ?>
                            </strong>
                        </td>

                        <td>
                            <?= htmlspecialchars($row['name']) ?>
                        </td>

                        <td>
                            <span class="badge <?= htmlspecialchars($row['customer_type']) ?>">
                                <?= htmlspecialchars(ucfirst($row['customer_type'])) ?>
                            </span>
                        </td>

                        <td>
                            <?= htmlspecialchars($row['phone'] ?? '-') ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($row['email'] ?? '-') ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($row['address'] ?? '-') ?>
                        </td>

                        <td>
                            <span class="badge <?= htmlspecialchars($row['status']) ?>">
                                <?= htmlspecialchars(ucfirst($row['status'])) ?>
                            </span>
                        </td>

                    </tr>

                <?php endwhile; ?>

            <?php else: ?>

                <tr>
                    <td colspan="8" class="empty">
                        No customers found.
                    </td>
                </tr>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

</body>
</html>