<?php
session_start();
require_once '../../database/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../../login.php');
    exit;
}

$message = '';
$message_type = '';

/* Get Milk Collection Records */
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

/* Save Quality Test */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $collection_id = !empty($_POST['collection_id'])
        ? (int)$_POST['collection_id']
        : null;

    $batch_id = !empty($_POST['batch_id'])
        ? (int)$_POST['batch_id']
        : null;

    $fat_percent = $_POST['fat_percent'] !== ''
        ? (float)$_POST['fat_percent']
        : null;

    $snf_percent = $_POST['snf_percent'] !== ''
        ? (float)$_POST['snf_percent']
        : null;

    $temperature_c = $_POST['temperature_c'] !== ''
        ? (float)$_POST['temperature_c']
        : null;

    $analyzer_result = trim($_POST['analyzer_result'] ?? '');

    $quality_status = $_POST['quality_status'] ?? 'HOLD';

    if (!in_array($quality_status, ['PASS', 'HOLD', 'REJECT'], true)) {
        $quality_status = 'HOLD';
    }

    $stmt = $conn->prepare("
        INSERT INTO quality_tests
        (
            collection_id,
            batch_id,
            fat_percent,
            snf_percent,
            temperature_c,
            analyzer_result,
            quality_status
        )
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    if ($stmt) {

        $stmt->bind_param(
            "iidddss",
            $collection_id,
            $batch_id,
            $fat_percent,
            $snf_percent,
            $temperature_c,
            $analyzer_result,
            $quality_status
        );

        if ($stmt->execute()) {
            $stmt->close();

            header('Location: index.php');
            exit;
        } else {
            $message = 'Unable to save quality test: ' . $stmt->error;
            $message_type = 'error';
        }

        $stmt->close();

    } else {
        $message = 'Database error: ' . $conn->error;
        $message_type = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Add Quality Test - Smart Dairy</title>

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
            opacity: .9;
        }

        .container {
            width: 94%;
            max-width: 900px;
            margin: 35px auto;
        }

        .card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 6px 25px rgba(0,0,0,.08);
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
        select,
        textarea {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #ccd8d3;
            border-radius: 8px;
            font-size: 15px;
            outline: none;
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: #087f5b;
        }

        textarea {
            resize: vertical;
            min-height: 90px;
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

        .info {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 14px;
            border-radius: 8px;
            margin-bottom: 22px;
            line-height: 1.5;
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

    <h1>🧪 Add Quality Test</h1>

    <p>
        Record milk quality parameters and analyzer results
    </p>

</div>


<div class="container">

    <div class="card">

        <h2>Quality Test Details</h2>

        <?php if ($message !== ''): ?>

            <div class="message <?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>

        <?php endif; ?>


        <div class="info">
            <strong>Quality Parameters:</strong>
            Enter the Fat %, SNF %, temperature and analyzer result.
            Select PASS, HOLD or REJECT based on the quality test.
        </div>


        <form method="POST">


            <!-- Milk Collection -->

            <div class="form-group">

                <label>
                    Milk Collection
                </label>

                <select name="collection_id">

                    <option value="">
                        -- Select Milk Collection --
                    </option>

                    <?php if ($collections && $collections->num_rows > 0): ?>

                        <?php while ($collection = $collections->fetch_assoc()): ?>

                            <option value="<?php echo (int)$collection['id']; ?>">

                                <?php
                                echo htmlspecialchars(
                                    '#' . $collection['id']
                                    . ' - '
                                    . ($collection['farmer_code'] ?? 'N/A')
                                    . ' - '
                                    . ($collection['farmer_name'] ?? 'Unknown')
                                    . ' - '
                                    . $collection['collection_date']
                                    . ' - '
                                    . ucfirst($collection['shift'])
                                    . ' - '
                                    . $collection['quantity_litres']
                                    . ' L'
                                );
                                ?>

                            </option>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <option value="">
                            No milk collection records available
                        </option>

                    <?php endif; ?>

                </select>

                <div class="hint">
                    Select the milk collection record being tested.
                </div>

            </div>


            <!-- Batch -->

            <div class="form-group">

                <label>
                    Batch ID
                </label>

                <input
                    type="number"
                    name="batch_id"
                    min="1"
                    placeholder="Enter Batch ID if available"
                >

                <div class="hint">
                    Leave empty if the milk has not been assigned to a batch.
                </div>

            </div>


            <!-- Fat and SNF -->

            <div class="row">

                <div class="form-group">

                    <label>
                        Fat Percentage (%)
                    </label>

                    <input
                        type="number"
                        name="fat_percent"
                        step="0.01"
                        min="0"
                        max="100"
                        placeholder="Example: 4.20"
                    >

                </div>


                <div class="form-group">

                    <label>
                        SNF Percentage (%)
                    </label>

                    <input
                        type="number"
                        name="snf_percent"
                        step="0.01"
                        min="0"
                        max="100"
                        placeholder="Example: 8.50"
                    >

                </div>

            </div>


            <!-- Temperature -->

            <div class="form-group">

                <label>
                    Temperature (°C)
                </label>

                <input
                    type="number"
                    name="temperature_c"
                    step="0.01"
                    placeholder="Example: 4.50"
                >

            </div>


            <!-- Analyzer -->

            <div class="form-group">

                <label>
                    Analyzer Result
                </label>

                <textarea
                    name="analyzer_result"
                    placeholder="Enter Milk Analyzer result..."
                ></textarea>

            </div>


            <!-- Status -->

            <div class="form-group">

                <label>
                    Quality Status <span class="required">*</span>
                </label>

                <select name="quality_status" required>

                    <option value="HOLD">
                        HOLD
                    </option>

                    <option value="PASS">
                        PASS
                    </option>

                    <option value="REJECT">
                        REJECT
                    </option>

                </select>

                <div class="hint">
                    PASS = accepted, HOLD = pending review, REJECT = not accepted.
                </div>

            </div>


            <!-- Buttons -->

            <div class="buttons">

                <button
                    type="submit"
                    class="btn save-btn"
                >
                    Save Quality Test
                </button>

                <a
                    href="index.php"
                    class="btn cancel-btn"
                >
                    Cancel
                </a>

            </div>

        </form>

    </div>

</div>

</body>

</html>