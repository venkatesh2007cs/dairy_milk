<?php
session_start();
require_once '../../database/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../../login.php');
    exit;
}

$sql = "
    SELECT
        mc.id,
        mc.collection_date,
        mc.shift,
        mc.quantity_litres,
        mc.collection_time,
        f.farmer_code,
        f.name AS farmer_name
    FROM milk_collections mc
    LEFT JOIN farmers f ON mc.farmer_id = f.id
    ORDER BY mc.id DESC
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Milk Collection - Smart Dairy</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f4f8f6;
            color: #263238;
        }

        .header {
            background: linear-gradient(135deg, #087f5b, #20a878);
            color: white;
            padding: 22px 35px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            font-size: 26px;
        }

        .header p {
            margin-top: 5px;
            opacity: 0.9;
        }

        .back-btn {
            background: white;
            color: #087f5b;
            text-decoration: none;
            padding: 10px 18px;
            border-radius: 8px;
            font-weight: bold;
        }

        .container {
            width: 94%;
            max-width: 1200px;
            margin: 30px auto;
        }

        .top-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .top-section h2 {
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
            background: #066b4d;
        }

        .card {
            background: white;
            border-radius: 14px;
            padding: 22px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #087f5b;
            color: white;
            padding: 14px;
            text-align: left;
        }

        td {
            padding: 13px;
            border-bottom: 1px solid #e5e5e5;
        }

        tr:hover {
            background: #f1faf6;
        }

        .shift {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: bold;
        }

        .morning {
            background: #fff3cd;
            color: #856404;
        }

        .evening {
            background: #e2e3ff;
            color: #383d9b;
        }

        .quantity {
            font-weight: bold;
            color: #087f5b;
        }

        .empty {
            text-align: center;
            padding: 40px;
            color: #777;
        }

        @media(max-width: 700px) {
            .header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            .top-section {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
        }
    </style>
</head>

<body>

<div class="header">
    <div>
        <h1>🥛 Milk Collection</h1>
        <p>Daily farmer-wise milk collection management</p>
    </div>

    <a href="../dashboard.php" class="back-btn">← Dashboard</a>
</div>

<div class="container">

    <div class="top-section">
        <h2>Collection Records</h2>

        <a href="add.php" class="add-btn">
            + Add Milk Collection
        </a>
    </div>

    <div class="card">

        <?php if ($result && $result->num_rows > 0): ?>

            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Farmer ID</th>
                        <th>Farmer Name</th>
                        <th>Date</th>
                        <th>Shift</th>
                        <th>Quantity</th>
                        <th>Collection Time</th>
                    </tr>
                </thead>

                <tbody>

                <?php while ($row = $result->fetch_assoc()): ?>

                    <tr>
                        <td><?php echo (int)$row['id']; ?></td>

                        <td>
                            <?php echo htmlspecialchars($row['farmer_code'] ?? 'N/A'); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($row['farmer_name'] ?? 'Unknown Farmer'); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($row['collection_date']); ?>
                        </td>

                        <td>
                            <span class="shift <?php echo strtolower($row['shift']); ?>">
                                <?php echo ucfirst($row['shift']); ?>
                            </span>
                        </td>

                        <td class="quantity">
                            <?php echo htmlspecialchars($row['quantity_litres']); ?> L
                        </td>

                        <td>
                            <?php
                            echo !empty($row['collection_time'])
                                ? htmlspecialchars($row['collection_time'])
                                : '-';
                            ?>
                        </td>
                    </tr>

                <?php endwhile; ?>

                </tbody>
            </table>

        <?php else: ?>

            <div class="empty">
                <h3>No Milk Collection Records Found</h3>
                <p>Click "Add Milk Collection" to create the first record.</p>
            </div>

        <?php endif; ?>

    </div>

</div>

</body>
</html>