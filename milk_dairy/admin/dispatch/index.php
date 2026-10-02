<?php
session_start();
require_once '../../database/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$sql = "SELECT 
            d.id,
            b.batch_number,
            c.name AS customer_name,
            v.vehicle_number,
            d.quantity_litres,
            d.seal_number,
            d.dispatch_datetime,
            d.delivery_status,
            d.received_datetime,
            d.created_at
        FROM dispatches d
        LEFT JOIN batches b ON d.batch_id = b.id
        LEFT JOIN customers c ON d.customer_id = c.id
        LEFT JOIN vehicles v ON d.vehicle_id = v.id
        ORDER BY d.id DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dispatch Management</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f8f6;
            color: #263238;
        }

        .header {
            background: linear-gradient(135deg, #087f5b, #12b886);
            color: white;
            padding: 22px 30px;
        }

        .header h1 {
            margin: 0;
            font-size: 28px;
        }

        .header p {
            margin: 6px 0 0;
            opacity: .9;
        }

        .container {
            padding: 30px;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 22px;
        }

        .top-bar h2 {
            margin: 0;
            color: #087f5b;
        }

        .btn {
            text-decoration: none;
            background: #087f5b;
            color: white;
            padding: 11px 18px;
            border-radius: 8px;
            font-weight: bold;
        }

        .btn:hover {
            background: #066b4d;
        }

        .table-box {
            background: white;
            border-radius: 14px;
            padding: 20px;
            box-shadow: 0 5px 18px rgba(0,0,0,.08);
            overflow-x: auto;
        }

        table {
            width: 100%;
            min-width: 1100px;
            border-collapse: collapse;
        }

        th {
            background: #087f5b;
            color: white;
            padding: 13px;
            text-align: left;
        }

        td {
            padding: 13px;
            border-bottom: 1px solid #e8eeee;
        }

        tr:hover {
            background: #f3fbf7;
        }

        .badge {
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        .pending {
            background: #fff3bf;
            color: #946200;
        }

        .in_transit {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .delivered {
            background: #d3f9d8;
            color: #087f5b;
        }

        .cancelled {
            background: #ffe3e3;
            color: #c92a2a;
        }

        .success {
            background: #d3f9d8;
            color: #087f5b;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .empty {
            text-align: center;
            padding: 30px;
            color: #777;
        }
    </style>
</head>

<body>

<div class="header">
    <h1>🚚 Dispatch Management</h1>
    <p>Manage milk dispatch, vehicle and delivery tracking</p>
</div>

<div class="container">

    <?php if (isset($_GET['success'])): ?>
        <div class="success">
            Dispatch record added successfully.
        </div>
    <?php endif; ?>

    <div class="top-bar">
        <h2>Dispatch Records</h2>

        <a href="add.php" class="btn">+ Add Dispatch</a>
    </div>

    <div class="table-box">

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Batch</th>
                    <th>Customer</th>
                    <th>Vehicle</th>
                    <th>Quantity (L)</th>
                    <th>Seal Number</th>
                    <th>Dispatch Time</th>
                    <th>Status</th>
                    <th>Received Time</th>
                </tr>
            </thead>

            <tbody>

            <?php if ($result && $result->num_rows > 0): ?>

                <?php while ($row = $result->fetch_assoc()): ?>

                    <?php
                    $status = $row['delivery_status'] ?? 'pending';
                    $status_class = $status;
                    ?>

                    <tr>

                        <td><?= (int)$row['id'] ?></td>

                        <td>
                            <strong>
                                <?= htmlspecialchars($row['batch_number'] ?? '-') ?>
                            </strong>
                        </td>

                        <td>
                            <?= htmlspecialchars($row['customer_name'] ?? '-') ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($row['vehicle_number'] ?? '-') ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($row['quantity_litres']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($row['seal_number'] ?? '-') ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($row['dispatch_datetime'] ?? '-') ?>
                        </td>

                        <td>
                            <span class="badge <?= htmlspecialchars($status_class) ?>">
                                <?= htmlspecialchars(
                                    ucwords(str_replace('_', ' ', $status))
                                ) ?>
                            </span>
                        </td>

                        <td>
                            <?= htmlspecialchars($row['received_datetime'] ?? '-') ?>
                        </td>

                    </tr>

                <?php endwhile; ?>

            <?php else: ?>

                <tr>
                    <td colspan="9" class="empty">
                        No dispatch records found.
                    </td>
                </tr>

            <?php endif; ?>

            </tbody>
        </table>

    </div>

</div>

</body>
</html>