<?php
session_start();
require_once '../../database/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$message = '';

/* Get available batches */
$batches = $conn->query("
    SELECT id, batch_number, quantity_litres, status
    FROM batches
    ORDER BY id DESC
");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $batch_id = (int)($_POST['batch_id'] ?? 0);
    $status = $_POST['pasteurization_status'] ?? 'pending';
    $quantity = trim($_POST['processed_quantity_litres'] ?? '');
    $processing_datetime = trim($_POST['processing_datetime'] ?? '');
    $operator_id = trim($_POST['operator_id'] ?? '');

    if ($batch_id <= 0 || $quantity === '') {
        $message = "Please select a batch and enter processed quantity.";
    } elseif (!is_numeric($quantity) || $quantity <= 0) {
        $message = "Please enter a valid processed quantity.";
    } else {

        $datetime_value = ($processing_datetime === '')
            ? null
            : $processing_datetime;

        $operator_value = ($operator_id === '')
            ? null
            : (int)$operator_id;

        $stmt = $conn->prepare("
            INSERT INTO processing_batches
            (
                batch_id,
                pasteurization_status,
                processed_quantity_litres,
                processing_datetime,
                operator_id
            )
            VALUES (?, ?, ?, ?, ?)
        ");

        if ($stmt) {

            $stmt->bind_param(
                "isdsi",
                $batch_id,
                $status,
                $quantity,
                $datetime_value,
                $operator_value
            );

            if ($stmt->execute()) {
                header("Location: index.php?success=1");
                exit;
            } else {
                $message = "Failed to save processing record: " . $stmt->error;
            }

            $stmt->close();

        } else {
            $message = "Database error: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Add Processing - Smart Dairy</title>

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
            opacity: .9;
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
            box-shadow: 0 5px 20px rgba(0,0,0,.08);
        }

        .title {
            color: #087f5b;
            margin-bottom: 25px;
            font-size: 22px;
        }

        .message {
            background: #f8d7da;
            color: #842029;
            padding: 13px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
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

        .hint {
            margin-top: 6px;
            font-size: 12px;
            color: #777;
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
        }
    </style>
</head>

<body>

<div class="header">
    <h1>⚙️ Add Processing</h1>
    <p>Record milk pasteurization and processing details</p>
</div>

<div class="container">

    <div class="form-box">

        <h2 class="title">Processing Details</h2>

        <?php if ($message !== ''): ?>
            <div class="message">
                <?= htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <div class="form-grid">

                <div class="form-group full">
                    <label>Batch *</label>

                    <select name="batch_id" required>

                        <option value="">
                            -- Select Batch --
                        </option>

                        <?php if ($batches && $batches->num_rows > 0): ?>

                            <?php while ($batch = $batches->fetch_assoc()): ?>

                                <option
                                    value="<?= $batch['id']; ?>"
                                    <?= (($_POST['batch_id'] ?? '') == $batch['id']) ? 'selected' : ''; ?>
                                >
                                    <?= htmlspecialchars($batch['batch_number']); ?>
                                    -
                                    <?= htmlspecialchars($batch['quantity_litres']); ?> L
                                    -
                                    <?= htmlspecialchars($batch['status']); ?>
                                </option>

                            <?php endwhile; ?>

                        <?php endif; ?>

                    </select>
                </div>

                <div class="form-group">
                    <label>Pasteurization Status</label>

                    <select name="pasteurization_status">

                        <option value="pending">Pending</option>
                        <option value="completed">Completed</option>
                        <option value="failed">Failed</option>

                    </select>
                </div>

                <div class="form-group">
                    <label>Processed Quantity (Litres) *</label>

                    <input
                        type="number"
                        name="processed_quantity_litres"
                        step="0.01"
                        min="0.01"
                        placeholder="Example: 100"
                        value="<?= htmlspecialchars($_POST['processed_quantity_litres'] ?? ''); ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label>Processing Date & Time</label>

                    <input
                        type="datetime-local"
                        name="processing_datetime"
                        value="<?= htmlspecialchars($_POST['processing_datetime'] ?? ''); ?>"
                    >
                </div>

                <div class="form-group">
                    <label>Operator ID</label>

                    <input
                        type="number"
                        name="operator_id"
                        min="1"
                        placeholder="Optional"
                        value="<?= htmlspecialchars($_POST['operator_id'] ?? ''); ?>"
                    >

                    <div class="hint">
                        Leave empty if operator is not assigned.
                    </div>
                </div>

            </div>

            <div class="buttons">

                <button type="submit" class="btn save">
                    💾 Save Processing
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