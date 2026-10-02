<?php
session_start();
require_once '../../database/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$result = $conn->query("
    SELECT
        id,
        tank_code,
        capacity_litres,
        current_quantity_litres,
        temperature_c,
        status,
        updated_at
    FROM chilling_tanks
    ORDER BY id DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chilling Tanks - Smart Dairy</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f0f7f4;
            color: #263238;
        }

        .header {
            background: linear-gradient(135deg, #087f5b, #20a66a);
            color: white;
            padding: 22px 35px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.12);
        }

        .header h1 {
            font-size: 26px;
        }

        .header p {
            margin-top: 5px;
            opacity: 0.9;
        }

        .container {
            padding: 30px;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .topbar h2 {
            color: #087f5b;
        }

        .add-btn {
            background: #087f5b;
            color: white;
            text-decoration: none;
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: bold;
            transition: 0.3s;
        }

        .add-btn:hover {
            background: #056044;
        }

        .table-box {
            background: white;
            border-radius: 14px;
            padding: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #e7f6ef;
            color: #087f5b;
            padding: 14px;
            text-align: left;
            font-size: 14px;
        }

        td {
            padding: 14px;
            border-bottom: 1px solid #eee;
            font-size: 14px;
        }

        tr:hover {
            background: #f8fffb;
        }

        .status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: capitalize;
        }

        .active {
            background: #d8f3dc;
            color: #1b7f3a;
        }

        .maintenance {
            background: #fff3cd;
            color: #856404;
        }

        .inactive {
            background: #f8d7da;
            color: #842029;
        }

        .empty {
            text-align: center;
            padding: 40px;
            color: #777;
        }

        @media (max-width: 700px) {
            .header {
                padding: 18px;
            }

            .container {
                padding: 15px;
            }

            .topbar {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
        }
    </style>
</head>

<body>

<div class="header">
    <div>
        <h1>🧊 Chilling Tank Management</h1>
        <p>Monitor milk storage tanks and temperature</p>
    </div>
</div>

<div class="container">

    <div class="topbar">
        <h2>Chilling Tanks</h2>

        <a href="add.php" class="add-btn">
            + Add Chilling Tank
        </a>
    </div>

    <div class="table-box">

        <?php if ($result && $result->num_rows > 0): ?>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Tank Code</th>
                    <th>Capacity (L)</th>
                    <th>Current Quantity (L)</th>
                    <th>Temperature (°C)</th>
                    <th>Status</th>
                    <th>Last Updated</th>
                </tr>
            </thead>

            <tbody>

            <?php $i = 1; ?>

            <?php while ($row = $result->fetch_assoc()): ?>

                <tr>
                    <td><?= $i++; ?></td>

                    <td>
                        <strong><?= htmlspecialchars($row['tank_code']); ?></strong>
                    </td>

                    <td>
                        <?= number_format((float)$row['capacity_litres'], 2); ?>
                    </td>

                    <td>
                        <?= number_format((float)$row['current_quantity_litres'], 2); ?>
                    </td>

                    <td>
                        <?= $row['temperature_c'] !== null
                            ? number_format((float)$row['temperature_c'], 2) . " °C"
                            : "—"; ?>
                    </td>

                    <td>
                        <span class="status <?= htmlspecialchars($row['status']); ?>">
                            <?= htmlspecialchars($row['status']); ?>
                        </span>
                    </td>

                    <td>
                        <?= htmlspecialchars($row['updated_at']); ?>
                    </td>
                </tr>

            <?php endwhile; ?>

            </tbody>
        </table>

        <?php else: ?>

            <div class="empty">
                <h3>No Chilling Tanks Found</h3>
                <p>Click "Add Chilling Tank" to create your first tank.</p>
            </div>

        <?php endif; ?>

    </div>

</div>

</body>
</html>