<?php
session_start();
require_once '../../database/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../../login.php');
    exit;
}

$sql = "
    SELECT
        qt.id,
        qt.collection_id,
        qt.batch_id,
        qt.fat_percent,
        qt.snf_percent,
        qt.temperature_c,
        qt.analyzer_result,
        qt.quality_status,
        qt.tested_at
    FROM quality_tests qt
    ORDER BY qt.id DESC
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quality Testing - Smart Dairy</title>

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
            opacity: .9;
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
            width: 95%;
            max-width: 1250px;
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
            box-shadow: 0 5px 20px rgba(0,0,0,.08);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 950px;
        }

        th {
            background: #087f5b;
            color: white;
            padding: 13px;
            text-align: left;
            white-space: nowrap;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #e5e5e5;
        }

        tr:hover {
            background: #f1faf6;
        }

        .status {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        .pass {
            background: #d8f3dc;
            color: #1b5e20;
        }

        .hold {
            background: #fff3cd;
            color: #856404;
        }

        .reject {
            background: #ffebee;
            color: #c62828;
        }

        .value {
            font-weight: bold;
            color: #087f5b;
        }

        .empty {
            text-align: center;
            padding: 45px;
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
                align-items: flex-start;
                gap: 15px;
            }
        }
    </style>
</head>

<body>

<div class="header">
    <div>
        <h1>🧪 Quality Testing</h1>
        <p>Milk quality analysis and safety monitoring</p>
    </div>

    <a href="../dashboard.php" class="back-btn">← Dashboard</a>
</div>

<div class="container">

    <div class="top-section">
        <h2>Quality Test Records</h2>

        <a href="add.php" class="add-btn">
            + Add Quality Test
        </a>
    </div>

    <div class="card">

        <?php if ($result && $result->num_rows > 0): ?>

            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Collection ID</th>
                        <th>Batch ID</th>
                        <th>Fat %</th>
                        <th>SNF %</th>
                        <th>Temperature</th>
                        <th>Analyzer Result</th>
                        <th>Status</th>
                        <th>Tested At</th>
                    </tr>
                </thead>

                <tbody>

                <?php while ($row = $result->fetch_assoc()): ?>

                    <?php
                    $status = strtoupper($row['quality_status']);
                    $status_class = strtolower($status);
                    ?>

                    <tr>

                        <td>
                            <?php echo (int)$row['id']; ?>
                        </td>

                        <td>
                            <?php echo $row['collection_id'] !== null
                                ? (int)$row['collection_id']
                                : '-'; ?>
                        </td>

                        <td>
                            <?php echo $row['batch_id'] !== null
                                ? (int)$row['batch_id']
                                : '-'; ?>
                        </td>

                        <td class="value">
                            <?php echo $row['fat_percent'] !== null
                                ? htmlspecialchars($row['fat_percent']) . '%'
                                : '-'; ?>
                        </td>

                        <td class="value">
                            <?php echo $row['snf_percent'] !== null
                                ? htmlspecialchars($row['snf_percent']) . '%'
                                : '-'; ?>
                        </td>

                        <td>
                            <?php echo $row['temperature_c'] !== null
                                ? htmlspecialchars($row['temperature_c']) . ' °C'
                                : '-'; ?>
                        </td>

                        <td>
                            <?php echo $row['analyzer_result']
                                ? htmlspecialchars($row['analyzer_result'])
                                : '-'; ?>
                        </td>

                        <td>
                            <span class="status <?php echo $status_class; ?>">
                                <?php echo htmlspecialchars($status); ?>
                            </span>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($row['tested_at']); ?>
                        </td>

                    </tr>

                <?php endwhile; ?>

                </tbody>
            </table>

        <?php else: ?>

            <div class="empty">
                <h3>No Quality Test Records Found</h3>
                <p>Click "Add Quality Test" to create the first record.</p>
            </div>

        <?php endif; ?>

    </div>

</div>

</body>
</html>