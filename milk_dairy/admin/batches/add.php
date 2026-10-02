<?php
session_start();
require_once '../../database/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$message = '';
$message_type = '';

/* Milk collections */
$collections = $conn->query("
    SELECT
        mc.id,
        mc.collection_date,
        mc.shift,
        mc.quantity_litres,
        f.farmer_code,
        f.name AS farmer_name
    FROM milk_collections mc
    LEFT JOIN farmers f ON mc.farmer_id = f.id
    ORDER BY mc.id DESC
");

/* Form submission */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $batch_number = trim($_POST['batch_number'] ?? '');
    $source_collection_id = trim($_POST['source_collection_id'] ?? '');
    $quantity_litres = trim($_POST['quantity_litres'] ?? '');
    $production_datetime = trim($_POST['production_datetime'] ?? '');
    $holding_until = trim($_POST['holding_until'] ?? '');
    $status = $_POST['status'] ?? 'created';

    if ($batch_number === '' || $quantity_litres === '') {

        $message = "Please enter Batch Number and Quantity.";
        $message_type = "error";

    } elseif (!is_numeric($quantity_litres) || $quantity_litres <= 0) {

        $message = "Please enter a valid quantity.";
        $message_type = "error";

    } else {

        $collection_id = ($source_collection_id === '')
            ? null
            : (int)$source_collection_id;

        $production_value = ($production_datetime === '')
            ? null
            : $production_datetime;

        $holding_value = ($holding_until === '')
            ? null
            : $holding_until;

        $stmt = $conn->prepare("
            INSERT INTO batches
            (
                batch_number,
                source_collection_id,
                quantity_litres,
                production_datetime,
                holding_until,
                status
            )
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        if ($stmt) {

            $stmt->bind_param(
                "sidsss",
                $batch_number,
                $collection_id,
                $quantity_litres,
                $production_value,
                $holding_value,
                $status
            );

            if ($stmt->execute()) {
                header("Location: index.php?success=1");
                exit;
            } else {
                $message = "Failed to create batch: " . $stmt->error;
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

    <title>Create Batch - Smart Dairy</title>

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
            max-width: 900px;
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
            width: 100%;
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

            .container {
                margin-top: 20px;
            }
        }
    </style>
</head>

<body>

<div class="header">
    <h1>📦 Create New Batch</h1>
    <p>Create and track a new milk batch</p>
</div>

<div class="container">

    <div class="form-box">

        <h2 class="title">Batch Details</h2>

        <?php if ($message !== ''): ?>
            <div class="message <?= htmlspecialchars($message_type); ?>">
                <?= htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <div class="form-grid">

                <div class="form-group">
                    <label>Batch Number *</label>

                    <input
                        type="text"
                        name="batch_number"
                        placeholder="Example: BATCH-001"
                        value="<?= htmlspecialchars($_POST['batch_number'] ?? ''); ?>"
                        required
                    >

                    <div class="hint">
                        Enter a unique batch number.
                    </div>
                </div>


                <div class="form-group">
                    <label>Source Collection</label>

                    <select name="source_collection_id">

                        <option value="">
                            -- Select Collection --
                        </option>

                        <?php if ($collections && $collections->num_rows > 0): ?>

                            <?php while ($collection = $collections->fetch_assoc()): ?>

                                <option
                                    value="<?= $collection['id']; ?>"
                                    <?= (($_POST['source_collection_id'] ?? '') == $collection['id']) ? 'selected' : ''; ?>
                                >
                                    #<?= $collection['id']; ?>
                                    -
                                    <?= htmlspecialchars($collection['farmer_code'] ?? ''); ?>
                                    -
                                    <?= htmlspecialchars($collection['farmer_name'] ?? 'Unknown Farmer'); ?>
                                    -
                                    <?= htmlspecialchars($collection['collection_date']); ?>
                                    -
                                    <?= htmlspecialchars($collection['shift']); ?>
                                    -
                                    <?= htmlspecialchars($collection['quantity_litres']); ?> L
                                </option>

                            <?php endwhile; ?>

                        <?php endif; ?>

                    </select>

                    <div class="hint">
                        Select the milk collection used for this batch.
                    </div>
                </div>


                <div class="form-group">
                    <label>Quantity (Litres) *</label>

                    <input
                        type="number"
                        name="quantity_litres"
                        step="0.01"
                        min="0.01"
                        placeholder="Example: 500"
                        value="<?= htmlspecialchars($_POST['quantity_litres'] ?? ''); ?>"
                        required
                    >
                </div>


                <div class="form-group">
                    <label>Production Date & Time</label>

                    <input
                        type="datetime-local"
                        name="production_datetime"
                        value="<?= htmlspecialchars($_POST['production_datetime'] ?? ''); ?>"
                    >
                </div>


                <div class="form-group">
                    <label>Holding Until</label>

                    <input
                        type="datetime-local"
                        name="holding_until"
                        value="<?= htmlspecialchars($_POST['holding_until'] ?? ''); ?>"
                    >

                    <div class="hint">
                        Optional holding/expiry time.
                    </div>
                </div>


                <div class="form-group">
                    <label>Status</label>

                    <select name="status">

                        <?php
                        $statuses = [
                            'created' => 'Created',
                            'chilling' => 'Chilling',
                            'processing' => 'Processing',
                            'stored' => 'Stored',
                            'dispatched' => 'Dispatched',
                            'hold' => 'Hold',
                            'rejected' => 'Rejected'
                        ];
                        ?>

                        <?php foreach ($statuses as $value => $label): ?>

                            <option
                                value="<?= $value; ?>"
                                <?= (($_POST['status'] ?? 'created') === $value) ? 'selected' : ''; ?>
                            >
                                <?= $label; ?>
                            </option>

                        <?php endforeach; ?>

                    </select>
                </div>

            </div>


            <div class="buttons">

                <button type="submit" class="btn save">
                    💾 Create Batch
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