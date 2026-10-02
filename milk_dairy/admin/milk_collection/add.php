<?php
session_start();
require_once '../../database/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../../login.php');
    exit;
}

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $farmer_id = (int)($_POST['farmer_id'] ?? 0);
    $collection_date = $_POST['collection_date'] ?? '';
    $shift = $_POST['shift'] ?? '';
    $quantity_litres = (float)($_POST['quantity_litres'] ?? 0);
    $collection_time = $_POST['collection_time'] ?? '';

    if (
        $farmer_id <= 0 ||
        $collection_date === '' ||
        !in_array($shift, ['morning', 'evening'], true) ||
        $quantity_litres <= 0
    ) {
        $message = 'Please fill all required fields correctly.';
        $message_type = 'error';
    } else {

        $stmt = $conn->prepare("
            INSERT INTO milk_collections
            (farmer_id, collection_date, shift, quantity_litres, collection_time)
            VALUES (?, ?, ?, ?, ?)
        ");

        if ($stmt) {

            $stmt->bind_param(
                "issds",
                $farmer_id,
                $collection_date,
                $shift,
                $quantity_litres,
                $collection_time
            );

            if ($stmt->execute()) {
                $stmt->close();

                header('Location: index.php');
                exit;
            } else {
                $message = 'Unable to save milk collection.';
                $message_type = 'error';
            }

            $stmt->close();

        } else {
            $message = 'Database error: ' . $conn->error;
            $message_type = 'error';
        }
    }
}

$farmers = $conn->query("
    SELECT id, farmer_code, name
    FROM farmers
    ORDER BY name ASC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Add Milk Collection - Smart Dairy</title>

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
        }

        .header h1 {
            font-size: 26px;
        }

        .header p {
            margin-top: 6px;
            opacity: 0.9;
        }

        .container {
            width: 94%;
            max-width: 850px;
            margin: 35px auto;
        }

        .card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 6px 25px rgba(0,0,0,0.08);
        }

        .card h2 {
            color: #087f5b;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .required {
            color: #d32f2f;
        }

        input,
        select {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #ccd8d3;
            border-radius: 8px;
            font-size: 15px;
            outline: none;
        }

        input:focus,
        select:focus {
            border-color: #087f5b;
        }

        .row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .message {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .error {
            background: #ffebee;
            color: #c62828;
        }

        .buttons {
            display: flex;
            gap: 12px;
            margin-top: 25px;
        }

        .btn {
            border: none;
            padding: 12px 22px;
            border-radius: 8px;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
        }

        .save-btn {
            background: #087f5b;
            color: white;
        }

        .save-btn:hover {
            background: #066b4d;
        }

        .cancel-btn {
            background: #e8ecea;
            color: #333;
        }

        .hint {
            font-size: 13px;
            color: #777;
            margin-top: 5px;
        }

        @media(max-width: 650px) {
            .row {
                grid-template-columns: 1fr;
            }

            .card {
                padding: 20px;
            }
        }
    </style>
</head>

<body>

<div class="header">
    <h1>🥛 Add Milk Collection</h1>
    <p>Record daily milk collected from farmers</p>
</div>

<div class="container">

    <div class="card">

        <h2>Milk Collection Details</h2>

        <?php if ($message !== ''): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <div class="form-group">
                <label>
                    Farmer <span class="required">*</span>
                </label>

                <select name="farmer_id" required>
                    <option value="">-- Select Farmer --</option>

                    <?php if ($farmers && $farmers->num_rows > 0): ?>

                        <?php while ($farmer = $farmers->fetch_assoc()): ?>

                            <option value="<?php echo (int)$farmer['id']; ?>">
                                <?php echo htmlspecialchars(
                                    $farmer['farmer_code'] . ' - ' . $farmer['name']
                                ); ?>
                            </option>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <option value="">No farmers available</option>

                    <?php endif; ?>

                </select>

                <div class="hint">
                    Select the farmer who supplied the milk.
                </div>
            </div>


            <div class="row">

                <div class="form-group">
                    <label>
                        Collection Date <span class="required">*</span>
                    </label>

                    <input
                        type="date"
                        name="collection_date"
                        value="<?php echo date('Y-m-d'); ?>"
                        required
                    >
                </div>


                <div class="form-group">
                    <label>
                        Shift <span class="required">*</span>
                    </label>

                    <select name="shift" required>
                        <option value="">-- Select Shift --</option>
                        <option value="morning">Morning</option>
                        <option value="evening">Evening</option>
                    </select>
                </div>

            </div>


            <div class="row">

                <div class="form-group">
                    <label>
                        Quantity (Litres) <span class="required">*</span>
                    </label>

                    <input
                        type="number"
                        name="quantity_litres"
                        step="0.01"
                        min="0.01"
                        placeholder="Example: 25.50"
                        required
                    >
                </div>


                <div class="form-group">
                    <label>
                        Collection Time
                    </label>

                    <input
                        type="time"
                        name="collection_time"
                    >
                </div>

            </div>


            <div class="buttons">

                <button type="submit" class="btn save-btn">
                    Save Collection
                </button>

                <a href="index.php" class="btn cancel-btn">
                    Cancel
                </a>

            </div>

        </form>

    </div>

</div>

</body>
</html>