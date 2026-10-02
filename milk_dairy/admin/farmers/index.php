<?php
session_start();
require_once '../../database/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../../login.php');
    exit;
}

$result = $conn->query("SELECT * FROM farmers ORDER BY id DESC");

if (!$result) {
    die("Database Error: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmers | Smart Dairy Management System</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background: #f4f7fb;
            color: #1f2937;
        }

        .header {
            background: linear-gradient(135deg, #087f5b, #0ca678);
            color: white;
            padding: 22px 35px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.12);
        }

        .header h1 {
            font-size: 25px;
        }

        .header p {
            margin-top: 5px;
            font-size: 14px;
            opacity: 0.9;
        }

        .back-btn {
            text-decoration: none;
            color: white;
            border: 1px solid rgba(255,255,255,0.5);
            padding: 10px 18px;
            border-radius: 8px;
            transition: 0.3s;
        }

        .back-btn:hover {
            background: white;
            color: #087f5b;
        }

        .container {
            padding: 30px;
            max-width: 1250px;
            margin: auto;
        }

        .top-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            gap: 15px;
            flex-wrap: wrap;
        }

        .title h2 {
            font-size: 24px;
            color: #111827;
        }

        .title p {
            color: #6b7280;
            margin-top: 5px;
        }

        .add-btn {
            background: #087f5b;
            color: white;
            text-decoration: none;
            padding: 12px 20px;
            border-radius: 9px;
            font-weight: bold;
            box-shadow: 0 5px 12px rgba(8,127,91,0.2);
            transition: 0.3s;
        }

        .add-btn:hover {
            background: #066b4d;
            transform: translateY(-2px);
        }

        .card {
            background: white;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.07);
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 750px;
        }

        th {
            background: #f0fdf8;
            color: #087f5b;
            text-align: left;
            padding: 16px;
            font-size: 14px;
            border-bottom: 1px solid #e5e7eb;
        }

        td {
            padding: 15px 16px;
            border-bottom: 1px solid #eef0f3;
            font-size: 14px;
        }

        tr:hover td {
            background: #fafdfc;
        }

        .farmer-id {
            font-weight: bold;
            color: #087f5b;
        }

        .status {
            background: #dcfce7;
            color: #166534;
            padding: 6px 11px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        .empty {
            text-align: center;
            padding: 55px 20px;
            color: #6b7280;
        }

        .empty-icon {
            font-size: 45px;
            margin-bottom: 12px;
        }

        @media (max-width: 700px) {
            .header {
                padding: 18px;
            }

            .container {
                padding: 18px;
            }

            .header h1 {
                font-size: 20px;
            }
        }
    </style>
</head>

<body>

<div class="header">
    <div>
        <h1>🥛 Smart Dairy Management</h1>
        <p>Farmer Management Module</p>
    </div>

    <a href="../dashboard.php" class="back-btn">
        ← Dashboard
    </a>
</div>

<div class="container">

    <div class="top-section">

        <div class="title">
            <h2>👨‍🌾 Farmers</h2>
            <p>Manage registered farmers and their details.</p>
        </div>

        <a href="add.php" class="add-btn">
            + Add New Farmer
        </a>

    </div>

    <div class="card">

        <div class="table-wrapper">

            <?php if ($result->num_rows > 0): ?>

                <table>

                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Farmer ID</th>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Address</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php while ($farmer = $result->fetch_assoc()): ?>

                        <tr>

                            <td>
                                <?= htmlspecialchars($farmer['id']) ?>
                            </td>

                            <td class="farmer-id">
                                <?= htmlspecialchars($farmer['farmer_code']) ?>
                            </td>

                            <td>
                                <strong>
                                    <?= htmlspecialchars($farmer['name']) ?>
                                </strong>
                            </td>

                            <td>
                                <?= htmlspecialchars($farmer['phone']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($farmer['address']) ?>
                            </td>

                            <td>
                                <span class="status">
                                    Active
                                </span>
                            </td>

                        </tr>

                    <?php endwhile; ?>

                    </tbody>

                </table>

            <?php else: ?>

                <div class="empty">
                    <div class="empty-icon">👨‍🌾</div>

                    <h3>No Farmers Found</h3>

                    <p>
                        No farmer records are available yet.
                    </p>

                    <br>

                    <a href="add.php" class="add-btn">
                        + Add First Farmer
                    </a>
                </div>

            <?php endif; ?>

        </div>

    </div>

</div>

</body>
</html>