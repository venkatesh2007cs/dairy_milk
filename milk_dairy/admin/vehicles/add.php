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

    $vehicle_number = trim($_POST['vehicle_number'] ?? '');
    $vehicle_type   = trim($_POST['vehicle_type'] ?? '');
    $driver_name    = trim($_POST['driver_name'] ?? '');
    $driver_phone   = trim($_POST['driver_phone'] ?? '');
    $status         = $_POST['status'] ?? 'available';

    if ($vehicle_number === '') {
        $message = 'Vehicle number is required.';
        $message_type = 'error';
    } else {

        $stmt = $conn->prepare(
            "INSERT INTO vehicles
            (vehicle_number, vehicle_type, driver_name, driver_phone, status)
            VALUES (?, ?, ?, ?, ?)"
        );

        if ($stmt) {

            $stmt->bind_param(
                "sssss",
                $vehicle_number,
                $vehicle_type,
                $driver_name,
                $driver_phone,
                $status
            );

            if ($stmt->execute()) {
                header("Location: index.php?success=1");
                exit;
            } else {
                $message = "Error: " . $stmt->error;
                $message_type = 'error';
            }

            $stmt->close();

        } else {
            $message = "Database error: " . $conn->error;
            $message_type = 'error';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Add Vehicle - Smart Dairy Management System</title>

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
            max-width: 850px;
            margin: 35px auto;
            padding: 0 20px;
        }

        .card {
            background: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 6px 22px rgba(0,0,0,0.08);
        }

        .card h2 {
            margin-top: 0;
            color: #087f5b;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
        }

        input,
        select {
            width: 100%;
            padding: 12px;
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
            display: flex;
            gap: 12px;
            margin-top: 25px;
        }

        .btn {
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 15px;
            font-weight: bold;
            text-decoration: none;
        }

        .save {
            background: #087f5b;
            color: white;
        }

        .save:hover {
            background: #066b4d;
        }

        .back {
            background: #e9ecef;
            color: #333;
        }

        .message {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .error {
            background: #ffe3e3;
            color: #c92a2a;
        }

        .required {
            color: #d00000;
        }
    </style>
</head>

<body>

<div class="header">
    <h1>🚚 Add Vehicle</h1>
    <p>Register a new dairy transport vehicle</p>
</div>

<div class="container">

    <div class="card">

        <h2>Vehicle Details</h2>

        <?php if ($message !== ''): ?>
            <div class="message <?= $message_type ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <div class="form-group">
                <label>
                    Vehicle Number <span class="required">*</span>
                </label>

                <input
                    type="text"
                    name="vehicle_number"
                    placeholder="Example: TN41AB1234"
                    required
                >
            </div>

            <div class="form-group">
                <label>Vehicle Type</label>

                <input
                    type="text"
                    name="vehicle_type"
                    placeholder="Example: Milk Tanker"
                >
            </div>

            <div class="form-group">
                <label>Driver Name</label>

                <input
                    type="text"
                    name="driver_name"
                    placeholder="Enter driver name"
                >
            </div>

            <div class="form-group">
                <label>Driver Phone</label>

                <input
                    type="text"
                    name="driver_phone"
                    placeholder="Enter driver phone"
                >
            </div>

            <div class="form-group">
                <label>Status</label>

                <select name="status">

                    <option value="available">
                        Available
                    </option>

                    <option value="in_transit">
                        In Transit
                    </option>

                    <option value="maintenance">
                        Maintenance
                    </option>

                    <option value="inactive">
                        Inactive
                    </option>

                </select>
            </div>

            <div class="buttons">

                <button type="submit" class="btn save">
                    Save Vehicle
                </button>

                <a href="index.php" class="btn back">
                    ← Back
                </a>

            </div>

        </form>

    </div>

</div>

</body>
</html>