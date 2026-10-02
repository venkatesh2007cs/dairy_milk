<?php
session_start();
require_once '../../database/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $tank_code = trim($_POST['tank_code'] ?? '');
    $capacity = trim($_POST['capacity_litres'] ?? '');
    $quantity = trim($_POST['current_quantity_litres'] ?? '0');
    $temperature = trim($_POST['temperature_c'] ?? '');
    $status = $_POST['status'] ?? 'active';

    if ($tank_code === '' || $capacity === '') {
        $message = "Please enter Tank Code and Capacity.";
        $message_type = "error";
    } elseif (!is_numeric($capacity) || $capacity <= 0) {
        $message = "Please enter a valid capacity.";
        $message_type = "error";
    } elseif (!is_numeric($quantity) || $quantity < 0) {
        $message = "Please enter a valid current quantity.";
        $message_type = "error";
    } elseif ($temperature !== '' && !is_numeric($temperature)) {
        $message = "Please enter a valid temperature.";
        $message_type = "error";
    } else {

        $temperature_value = ($temperature === '') ? null : (float)$temperature;

        $stmt = $conn->prepare("
            INSERT INTO chilling_tanks
            (
                tank_code,
                capacity_litres,
                current_quantity_litres,
                temperature_c,
                status
            )
            VALUES (?, ?, ?, ?, ?)
        ");

        if ($stmt) {

            $stmt->bind_param(
                "sddds",
                $tank_code,
                $capacity,
                $quantity,
                $temperature_value,
                $status
            );

            if ($stmt->execute()) {
                header("Location: index.php?success=1");
                exit;
            } else {
                $message = "Failed to add chilling tank.";
                $message_type = "error";
            }

            $stmt->close();

        } else {
            $message = "Database error: " . $conn->error;
            $message_type = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Add Chilling Tank - Smart Dairy</title>

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
        }

        .header h1 {
            font-size: 26px;
        }

        .header p {
            margin-top: 5px;
            opacity: 0.9;
        }

        .container {
            max-width: 850px;
            margin: 35px auto;
            padding: 0 20px;
        }

        .form-box {
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }

        .form-title {
            color: #087f5b;
            margin-bottom: 25px;
            font-size: 22px;
        }

        .message {
            padding: 13px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .error {
            background: #f8d7da;
            color: #842029;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .full {
            grid-column: 1 / -1;
        }

        label {
            margin-bottom: 8px;
            font-weight: bold;
            color: #37474f;
        }

        input,
        select {
            padding: 13px;
            border: 1px solid #ccd8d3;
            border-radius: 8px;
            font-size: 15px;
            outline: none;
        }

        input:focus,
        select:focus {
            border-color: #087f5b;
        }

        .buttons {
            margin-top: 28px;
            display: flex;
            gap: 12px;
        }

        .btn {
            border: none;
            padding: 13px 22px;
            border-radius: 8px;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
        }

        .save {
            background: #087f5b;
            color: white;
        }

        .save:hover {
            background: #056044;
        }

        .cancel {
            background: #e9ecef;
            color: #333;
        }

        @media (max-width: 650px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .full {
                grid-column: auto;
            }

            .header {
                padding: 20px;
            }

            .container {
                margin-top: 20px;
            }
        }
    </style>
</head>

<body>

<div class="header">
    <h1>🧊 Add Chilling Tank</h1>
    <p>Register a new milk chilling storage tank</p>
</div>

<div class="container">

    <div class="form-box">

        <h2 class="form-title">Chilling Tank Details</h2>

        <?php if ($message !== ''): ?>
            <div class="message <?= $message_type; ?>">
                <?= htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <div class="form-grid">

                <div class="form-group">
                    <label>Tank Code *</label>
                    <input
                        type="text"
                        name="tank_code"
                        placeholder="Example: TANK-001"
                        required
                    >
                </div>

                <div class="form-group">
                    <label>Capacity (Litres) *</label>
                    <input
                        type="number"
                        name="capacity_litres"
                        step="0.01"
                        min="0.01"
                        placeholder="Example: 5000"
                        required
                    >
                </div>

                <div class="form-group">
                    <label>Current Quantity (Litres)</label>
                    <input
                        type="number"
                        name="current_quantity_litres"
                        step="0.01"
                        min="0"
                        value="0"
                        placeholder="Example: 1000"
                    >
                </div>

                <div class="form-group">
                    <label>Temperature (°C)</label>
                    <input
                        type="number"
                        name="temperature_c"
                        step="0.01"
                        placeholder="Example: 4.00"
                    >
                </div>

                <div class="form-group full">
                    <label>Status</label>

                    <select name="status">
                        <option value="active">Active</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

            </div>

            <div class="buttons">

                <button type="submit" class="btn save">
                    💾 Save Chilling Tank
                </button>

                <a href="index.php" class="btn cancel">
                    ← Cancel
                </a>

            </div>

        </form>

    </div>

</div>

</body>
</html>