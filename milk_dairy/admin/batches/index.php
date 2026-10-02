<?php
session_start();
require_once '../../database/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$result = $conn->query("
    SELECT
        b.id,
        b.batch_number,
        b.source_collection_id,
        b.quantity_litres,
        b.production_datetime,
        b.holding_until,
        b.status,
        b.created_at
    FROM batches b
    ORDER BY b.id DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Batches - Smart Dairy</title>

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
            min-width: 950px;
        }

        th {
            background: #e7f6ef;
            color: #087f5b;
            padding: 14px;
            text-align: left;
            font-size: 13px;
            white-space: nowrap;
        }

        td {
            padding: 14px;
            border-bottom: 1px solid #eee;
            font-size: 13px;
            white-space: nowrap;
        }

        tr:hover {
            background: #f8fffb;
        }

        .batch-number {
            font-weight: bold;
            color: #087f5b;
        }

        .status {
            display: inline-block;
            padding: 6px 11px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
            text-transform: capitalize;
        }

        .created {
            background: #e7f1ff;
            color: #2457a6;
        }

        .chilling {
            background: #dff5ff;
            color: #087a9c;
        }

        .processing {
            background: #fff0d9;
            color: #9a5b00;
        }

        .stored {
            background: #d8f3dc;
            color: #1b7f3a;
        }

        .dispatched {
            background: #e5ddff;
            color: #5b3aa4;
        }

        .hold {
            background: #fff3cd;
            color: #856404;
        }

        .rejected {
            background: #f8d7da;
            color: #842029;
        }

        .empty {
            text-align: center;
            padding: 45px;
            color: #777;
        }

        .empty h3 {
            margin-bottom: 8px;
        }

        @media (max-width: 700px) {
            .container {
                padding: 15px;
            }

            .topbar {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .header {
                padding: 20px;
            }
        }
    </style>
</head>

<body>

<div class="header">
    <h1>📦 Batch Management</h1>
    <p>Manage milk batches, quantity and production status</p>
</div>

<div class="container">

    <div class="topbar">
        <h2>Milk Batches</h2>

        <a href="add.php" class="add-btn">
            + Create New Batch
        </a>
    </div>

    <div class="table-box">

        <?php if ($result && $result->num_rows > 0): ?>

            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Batch Number</th>
                        <th>Collection ID</th>
                        <th>Quantity (L)</th>
                        <th>Production Date & Time</th>
                        <th>Holding Until</th>
                        <th>Status</th>
                        <th>Created At</th>
                    </tr>
                </thead>

                <tbody>

                <?php $i = 1; ?>

                <?php while ($row = $result->fetch_assoc()): ?>

                    <tr>
                        <td><?= $i++; ?></td>

                        <td class="batch-number">
                            <?= htmlspecialchars($row['batch_number']); ?>
                        </td>

                        <td>
                            <?= $row['source_collection_id'] !== null
                                ? htmlspecialchars($row['source_collection_id'])
                                : '—'; ?>
                        </td>

                        <td>
                            <?= number_format((float)$row['quantity_litres'], 2); ?>
                        </td>

                        <td>
                            <?= $row['production_datetime']
                                ? htmlspecialchars($row['production_datetime'])
                                : '—'; ?>
                        </td>

                        <td>
                            <?= $row['holding_until']
                                ? htmlspecialchars($row['holding_until'])
                                : '—'; ?>
                        </td>

                        <td>
                            <span class="status <?= htmlspecialchars($row['status']); ?>">
                                <?= htmlspecialchars($row['status']); ?>
                            </span>
                        </td>

                        <td>
                            <?= htmlspecialchars($row['created_at']); ?>
                        </td>
                    </tr>

                <?php endwhile; ?>

                </tbody>
            </table>

        <?php else: ?>

            <div class="empty">
                <h3>No Batches Found</h3>
                <p>Click "Create New Batch" to add your first batch.</p>
            </div>

        <?php endif; ?>

    </div>

</div>

</body>
</html>