<?php
session_start();
require_once '../../database/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$sql = "SELECT id, vehicle_number, vehicle_type, driver_name, driver_phone, status, created_at
        FROM vehicles
        ORDER BY id DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicles - Smart Dairy Management System</title>

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
            opacity: 0.9;
        }

        .container {
            padding: 30px;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 22px;
            gap: 15px;
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
            display: inline-block;
        }

        .btn:hover {
            background: #066b4d;
        }

        .table-box {
            background: white;
            border-radius: 14px;
            padding: 20px;
            box-shadow: 0 5px 18px rgba(0,0,0,0.08);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 850px;
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

        .available {
            background: #d3f9d8;
            color: #087f5b;
        }

        .transit {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .maintenance {
            background: #fff3bf;
            color: #946200;
        }

        .inactive {
            background: #ffe3e3;
            color: #c92a2a;
        }

        .empty {
            text-align: center;
            padding: 30px;
            color: #777;
        }

        @media (max-width: 700px) {
            .container {
                padding: 15px;
            }

            .top-bar {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>

<body>

<div class="header">
    <h1>🚚 Vehicle Management</h1>
    <p>Manage dairy transport vehicles and drivers</p>
</div>

<div class="container">

    <div class="top-bar">
        <h2>Vehicle List</h2>

        <a href="add.php" class="btn">+ Add Vehicle</a>
    </div>

    <div class="table-box">

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Vehicle Number</th>
                    <th>Vehicle Type</th>
                    <th>Driver Name</th>
                    <th>Driver Phone</th>
                    <th>Status</th>
                    <th>Created At</th>
                </tr>
            </thead>

            <tbody>

            <?php if ($result && $result->num_rows > 0): ?>

                <?php while ($row = $result->fetch_assoc()): ?>

                    <?php
                    $statusClass = '';

                    if ($row['status'] === 'available') {
                        $statusClass = 'available';
                    } elseif ($row['status'] === 'in_transit') {
                        $statusClass = 'transit';
                    } elseif ($row['status'] === 'maintenance') {
                        $statusClass = 'maintenance';
                    } else {
                        $statusClass = 'inactive';
                    }
                    ?>

                    <tr>
                        <td><?= (int)$row['id'] ?></td>

                        <td>
                            <strong><?= htmlspecialchars($row['vehicle_number']) ?></strong>
                        </td>

                        <td>
                            <?= htmlspecialchars($row['vehicle_type'] ?? '-') ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($row['driver_name'] ?? '-') ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($row['driver_phone'] ?? '-') ?>
                        </td>

                        <td>
                            <span class="badge <?= $statusClass ?>">
                                <?= htmlspecialchars(ucwords(str_replace('_', ' ', $row['status']))) ?>
                            </span>
                        </td>

                        <td>
                            <?= htmlspecialchars($row['created_at']) ?>
                        </td>
                    </tr>

                <?php endwhile; ?>

            <?php else: ?>

                <tr>
                    <td colspan="7" class="empty">
                        No vehicles found.
                    </td>
                </tr>

            <?php endif; ?>

            </tbody>
        </table>

    </div>

</div>

</body>
</html>