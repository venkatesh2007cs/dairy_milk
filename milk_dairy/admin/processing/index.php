<?php
session_start();
require_once '../../database/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$result = $conn->query("
    SELECT
        p.id,
        p.batch_id,
        b.batch_number,
        p.pasteurization_status,
        p.processed_quantity_litres,
        p.processing_datetime,
        p.operator_id,
        p.created_at
    FROM processing_batches p
    LEFT JOIN batches b ON p.batch_id = b.id
    ORDER BY p.id DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Processing - Smart Dairy</title>

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
            box-shadow: 0 4px 15px rgba(0,0,0,.12);
        }

        .header h1 {
            font-size: 26px;
        }

        .header p {
            margin-top: 5px;
            opacity: .9;
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
        }

        .add-btn:hover {
            background: #056044;
        }

        .table-box {
            background: white;
            border-radius: 14px;
            padding: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,.08);
            overflow-x: auto;
        }

        table {
            width: 100%;
            min-width: 900px;
            border-collapse: collapse;
        }

        th {
            background: #e7f6ef;
            color: #087f5b;
            padding: 14px;
            text-align: left;
            white-space: nowrap;
            font-size: 13px;
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

        .batch {
            color: #087f5b;
            font-weight: bold;
        }

        .status {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
            text-transform: capitalize;
        }

        .pending {
            background: #fff3cd;
            color: #856404;
        }

        .completed {
            background: #d8f3dc;
            color: #1b7f3a;
        }

        .failed {
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
    <h1>⚙️ Milk Processing</h1>
    <p>Manage pasteurization and milk processing batches</p>
</div>

<div class="container">

    <div class="topbar">
        <h2>Processing Batches</h2>

        <a href="add.php" class="add-btn">
            + Add Processing
        </a>
    </div>

    <div class="table-box">

        <?php if ($result && $result->num_rows > 0): ?>

            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Batch Number</th>
                        <th>Batch ID</th>
                        <th>Processed Quantity (L)</th>
                        <th>Pasteurization Status</th>
                        <th>Processing Date & Time</th>
                        <th>Operator ID</th>
                        <th>Created At</th>
                    </tr>
                </thead>

                <tbody>

                <?php $i = 1; ?>

                <?php while ($row = $result->fetch_assoc()): ?>

                    <tr>
                        <td><?= $i++; ?></td>

                        <td class="batch">
                            <?= htmlspecialchars($row['batch_number'] ?? 'Unknown'); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($row['batch_id']); ?>
                        </td>

                        <td>
                            <?= $row['processed_quantity_litres'] !== null
                                ? number_format((float)$row['processed_quantity_litres'], 2)
                                : '—'; ?>
                        </td>

                        <td>
                            <span class="status <?= htmlspecialchars($row['pasteurization_status'] ?? 'pending'); ?>">
                                <?= htmlspecialchars($row['pasteurization_status'] ?? 'pending'); ?>
                            </span>
                        </td>

                        <td>
                            <?= $row['processing_datetime']
                                ? htmlspecialchars($row['processing_datetime'])
                                : '—'; ?>
                        </td>

                        <td>
                            <?= $row['operator_id'] !== null
                                ? htmlspecialchars($row['operator_id'])
                                : '—'; ?>
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
                <h3>No Processing Records Found</h3>
                <p>Click "Add Processing" to create your first processing record.</p>
            </div>

        <?php endif; ?>

    </div>

</div>

</body>
</html>