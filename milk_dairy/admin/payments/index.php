<?php
session_start();
require_once '../../database/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$sql = "SELECT 
            fp.id,
            f.farmer_code,
            f.name AS farmer_name,
            fp.collection_id,
            fp.amount,
            fp.payment_date,
            fp.payment_status,
            fp.created_at
        FROM farmer_payments fp
        INNER JOIN farmers f ON fp.farmer_id = f.id
        ORDER BY fp.id DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmer Payments</title>

    <style>
        * {
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            margin: 0;
            background: #f0fdf4;
            color: #1f2937;
        }

        .header {
            background: linear-gradient(135deg, #087f5b, #12b886);
            color: white;
            padding: 22px 35px;
        }

        .header h1 {
            margin: 0;
            font-size: 28px;
        }

        .header p {
            margin: 7px 0 0;
            opacity: 0.9;
        }

        .container {
            padding: 30px;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            gap: 15px;
        }

        .topbar h2 {
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
            background: #056b4c;
        }

        .card {
            background: white;
            border-radius: 14px;
            padding: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
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
            border-bottom: 1px solid #e5e7eb;
        }

        tr:hover {
            background: #f0fdf4;
        }

        .paid {
            color: #087f5b;
            font-weight: bold;
        }

        .pending {
            color: #d97706;
            font-weight: bold;
        }

        .empty {
            text-align: center;
            padding: 35px;
            color: #6b7280;
        }

        @media(max-width:700px) {
            .container {
                padding: 15px;
            }

            .topbar {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>

<body>

<div class="header">
    <h1>💰 Farmer Payments</h1>
    <p>Manage farmer payment records and payment status</p>
</div>

<div class="container">

    <div class="topbar">
        <h2>Payment Records</h2>
        <a href="add.php" class="btn">+ Add Payment</a>
    </div>

    <div class="card">

        <?php if ($result && $result->num_rows > 0): ?>

            <table>
                <tr>
                    <th>ID</th>
                    <th>Farmer Code</th>
                    <th>Farmer Name</th>
                    <th>Collection ID</th>
                    <th>Amount</th>
                    <th>Payment Date</th>
                    <th>Status</th>
                    <th>Created At</th>
                </tr>

                <?php while ($row = $result->fetch_assoc()): ?>

                    <tr>
                        <td><?= htmlspecialchars($row['id']) ?></td>

                        <td>
                            <?= htmlspecialchars($row['farmer_code']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($row['farmer_name']) ?>
                        </td>

                        <td>
                            <?= $row['collection_id'] !== null
                                ? htmlspecialchars($row['collection_id'])
                                : '-' ?>
                        </td>

                        <td>
                            ₹<?= number_format($row['amount'], 2) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($row['payment_date']) ?>
                        </td>

                        <td>
                            <?php if ($row['payment_status'] === 'paid'): ?>
                                <span class="paid">✓ Paid</span>
                            <?php else: ?>
                                <span class="pending">● Pending</span>
                            <?php endif; ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($row['created_at']) ?>
                        </td>
                    </tr>

                <?php endwhile; ?>

            </table>

        <?php else: ?>

            <div class="empty">
                <h3>No payment records found</h3>
                <p>Click "Add Payment" to create the first farmer payment.</p>
            </div>

        <?php endif; ?>

    </div>

</div>

</body>
</html>