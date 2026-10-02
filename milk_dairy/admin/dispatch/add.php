<?php
session_start();
require_once '../../database/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$message = '';
$message_type = '';

/* Fetch batches */
$batches = $conn->query("
    SELECT id, batch_number, quantity_litres
    FROM batches
    WHERE status NOT IN ('rejected', 'dispatched')
    ORDER BY id DESC
");

/* Fetch customers */
$customers = $conn->query("
    SELECT id, name
    FROM customers
    ORDER BY name ASC
");

/* Fetch vehicles */
$vehicles = $conn->query("
    SELECT id, vehicle_number, driver_name
    FROM vehicles
    WHERE status = 'available'
    ORDER BY vehicle_number ASC
");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $batch_id = (int)($_POST['batch_id'] ?? 0);
    $customer_id = (int)($_POST['customer_id'] ?? 0);

    $vehicle_id = !empty($_POST['vehicle_id'])
        ? (int)$_POST['vehicle_id']
        : null;

    $quantity_litres = (float)($_POST['quantity_litres'] ?? 0);

    $seal_number = trim($_POST['seal_number'] ?? '');

    $dispatch_datetime = !empty($_POST['dispatch_datetime'])
        ? $_POST['dispatch_datetime']
        : null;

    $delivery_status = $_POST['delivery_status'] ?? 'pending';

    if ($batch_id <= 0 || $customer_id <= 0 || $quantity_litres <= 0) {

        $message = 'Please fill all required fields.';
        $message_type = 'error';

    } else {

        $stmt = $conn->prepare("
            INSERT INTO dispatches
            (
                batch_id,
                customer_id,
                vehicle_id,
                quantity_litres,
                seal_number,
                dispatch_datetime,
                delivery_status
            )
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        if ($stmt) {

            $stmt->bind_param(
                "iiidsss",
                $batch_id,
                $customer_id,
                $vehicle_id,
                $quantity_litres,
                $seal_number,
                $dispatch_datetime,
                $delivery_status
            );

            if ($stmt->execute()) {

                /* Update vehicle status */
                if ($vehicle_id !== null) {

                    $vehicle_stmt = $conn->prepare("
                        UPDATE vehicles
                        SET status = 'in_transit'
                        WHERE id = ?
                    ");

                    if ($vehicle_stmt) {
                        $vehicle_stmt->bind_param("i", $vehicle_id);
                        $vehicle_stmt->execute();
                        $vehicle_stmt->close();
                    }
                }

                /* Update batch status */
                $batch_stmt = $conn->prepare("
                    UPDATE batches
                    SET status = 'dispatched'
                    WHERE id = ?
                ");

                if ($batch_stmt) {
                    $batch_stmt->bind_param("i", $batch_id);
                    $batch_stmt->execute();
                    $batch_stmt->close();
                }

                header("Location: index.php?success=1");
                exit;

            } else {

                $message = "Database error: " . $stmt->error;
                $message_type = 'error';
            }

            $stmt->close();

        } else {

            $message = "Prepare error: " . $conn->error;
            $message_type = 'error';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Add Dispatch - Smart Dairy</title>

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
            padding: 24px 30px;
        }

        .header h1 {
            margin: 0;
            font-size: 28px;
        }

        .header p {
            margin: 7px 0 0;
            opacity: .9;
        }

        .container {
            max-width: 900px;
            margin: 35px auto;
            padding: 0 20px;
        }

        .card {
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 6px 22px rgba(0,0,0,.08);
        }

        .card h2 {
            margin-top: 0;
            color: #087f5b;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 19px;
        }

        label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
        }

        .required {
            color: #d00000;
        }

        input,
        select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccd8d3;
            border-radius: 8px;
            font-size: 15px;
            background: white;
        }

        input:focus,
        select:focus {
            outline: none;
            border-color: #087f5b;
        }

        .buttons {
            display: flex;
            gap: 12px;
            margin-top: 25px;
        }

        button,
        .back {
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
        }

        button {
            border: none;
            background: #087f5b;
            color: white;
        }

        button:hover {
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

        .info {
            background: #e7f5ff;
            color: #1864ab;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

    </style>

</head>

<body>

<div class="header">

    <h1>🚚 Add Dispatch</h1>

    <p>
        Create a new milk dispatch record
    </p>

</div>

<div class="container">

    <div class="card">

        <h2>Dispatch Details</h2>

        <?php if ($message !== ''): ?>

            <div class="message <?= htmlspecialchars($message_type) ?>">
                <?= htmlspecialchars($message) ?>
            </div>

        <?php endif; ?>

        <div class="info">
            Select the batch, customer and available vehicle
            for dispatch.
        </div>

        <form method="POST">

            <!-- Batch -->

            <div class="form-group">

                <label>
                    Batch <span class="required">*</span>
                </label>

                <select name="batch_id" required>

                    <option value="">
                        -- Select Batch --
                    </option>

                    <?php if ($batches && $batches->num_rows > 0): ?>

                        <?php while ($batch = $batches->fetch_assoc()): ?>

                            <option value="<?= (int)$batch['id'] ?>">

                                <?= htmlspecialchars($batch['batch_number']) ?>

                                -
                                <?= htmlspecialchars($batch['quantity_litres']) ?>
                                L

                            </option>

                        <?php endwhile; ?>

                    <?php endif; ?>

                </select>

            </div>


            <!-- Customer -->

            <div class="form-group">

                <label>
                    Receiving Customer / Company
                    <span class="required">*</span>
                </label>

                <select name="customer_id" required>

                    <option value="">
                        -- Select Customer --
                    </option>

                    <?php if ($customers && $customers->num_rows > 0): ?>

                        <?php while ($customer = $customers->fetch_assoc()): ?>

                            <option value="<?= (int)$customer['id'] ?>">

                                <?= htmlspecialchars($customer['name']) ?>

                            </option>

                        <?php endwhile; ?>

                    <?php endif; ?>

                </select>

            </div>


            <!-- Vehicle -->

            <div class="form-group">

                <label>
                    Vehicle
                </label>

                <select name="vehicle_id">

                    <option value="">
                        -- Select Available Vehicle --
                    </option>

                    <?php if ($vehicles && $vehicles->num_rows > 0): ?>

                        <?php while ($vehicle = $vehicles->fetch_assoc()): ?>

                            <option value="<?= (int)$vehicle['id'] ?>">

                                <?= htmlspecialchars($vehicle['vehicle_number']) ?>

                                <?php if (!empty($vehicle['driver_name'])): ?>

                                    -
                                    <?= htmlspecialchars($vehicle['driver_name']) ?>

                                <?php endif; ?>

                            </option>

                        <?php endwhile; ?>

                    <?php endif; ?>

                </select>

            </div>


            <!-- Quantity -->

            <div class="form-group">

                <label>
                    Quantity (Litres)
                    <span class="required">*</span>
                </label>

                <input
                    type="number"
                    name="quantity_litres"
                    step="0.01"
                    min="0.01"
                    placeholder="Example: 500"
                    required
                >

            </div>


            <!-- Seal -->

            <div class="form-group">

                <label>
                    Seal Number
                </label>

                <input
                    type="text"
                    name="seal_number"
                    placeholder="Example: SEAL-1001"
                >

            </div>


            <!-- Dispatch Date -->

            <div class="form-group">

                <label>
                    Dispatch Date & Time
                </label>

                <input
                    type="datetime-local"
                    name="dispatch_datetime"
                >

            </div>


            <!-- Status -->

            <div class="form-group">

                <label>
                    Delivery Status
                </label>

                <select name="delivery_status">

                    <option value="pending">
                        Pending
                    </option>

                    <option value="in_transit">
                        In Transit
                    </option>

                    <option value="delivered">
                        Delivered
                    </option>

                    <option value="cancelled">
                        Cancelled
                    </option>

                </select>

            </div>


            <div class="buttons">

                <button type="submit">
                    💾 Save Dispatch
                </button>

                <a href="index.php" class="back">
                    ← Back
                </a>

            </div>

        </form>

    </div>

</div>

</body>
</html>