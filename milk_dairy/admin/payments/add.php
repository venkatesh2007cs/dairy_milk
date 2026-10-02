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

    $farmer_id = intval($_POST['farmer_id'] ?? 0);
    $collection_id = !empty($_POST['collection_id'])
        ? intval($_POST['collection_id'])
        : null;

    $amount = floatval($_POST['amount'] ?? 0);
    $payment_date = $_POST['payment_date'] ?? '';
    $payment_status = $_POST['payment_status'] ?? 'pending';

    if ($farmer_id <= 0 || $amount <= 0 || $payment_date === '') {

        $message = "Please fill all required fields.";
        $message_type = "error";

    } else {

        $stmt = $conn->prepare(
            "INSERT INTO farmer_payments
            (farmer_id, collection_id, amount, payment_date, payment_status)
            VALUES (?, ?, ?, ?, ?)"
        );

        if ($stmt) {

            $stmt->bind_param(
                "iidss",
                $farmer_id,
                $collection_id,
                $amount,
                $payment_date,
                $payment_status
            );

            if ($stmt->execute()) {

                header("Location: index.php?success=1");
                exit;

            } else {

                $message = "Payment could not be saved.";
                $message_type = "error";
            }

            $stmt->close();

        } else {

            $message = "Database error: " . $conn->error;
            $message_type = "error";
        }
    }
}

/* Farmers */
$farmers = $conn->query(
    "SELECT id, farmer_code, name
     FROM farmers
     ORDER BY name ASC"
);

/* Collections */
$collections = $conn->query(
    "SELECT
        mc.id,
        mc.farmer_id,
        mc.collection_date,
        mc.shift,
        mc.quantity_litres,
        f.farmer_code,
        f.name AS farmer_name
     FROM milk_collections mc
     INNER JOIN farmers f ON mc.farmer_id = f.id
     ORDER BY mc.id DESC"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Add Farmer Payment</title>

<style>

* {
    box-sizing: border-box;
    font-family: Arial, sans-serif;
}

body {
    margin: 0;
    background: #f0fdf4;
    color: #1f2937;
}

.header {
    background: linear-gradient(135deg, #087f5b, #12b886);
    color: white;
    padding: 25px 35px;
}

.header h1 {
    margin: 0;
    font-size: 28px;
}

.header p {
    margin: 7px 0 0;
    opacity: 0.9;
}

.container {
    max-width: 850px;
    margin: 35px auto;
    padding: 0 20px;
}

.card {
    background: white;
    padding: 30px;
    border-radius: 16px;
    box-shadow: 0 6px 25px rgba(0,0,0,0.08);
}

.form-group {
    margin-bottom: 20px;
}

label {
    display: block;
    font-weight: bold;
    margin-bottom: 8px;
    color: #374151;
}

.required {
    color: red;
}

input,
select {
    width: 100%;
    padding: 12px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 15px;
    outline: none;
}

input:focus,
select:focus {
    border-color: #087f5b;
    box-shadow: 0 0 0 2px rgba(8,127,91,0.1);
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
    cursor: pointer;
    font-weight: bold;
    text-decoration: none;
    font-size: 15px;
}

.btn-save {
    background: #087f5b;
    color: white;
}

.btn-save:hover {
    background: #056b4c;
}

.btn-cancel {
    background: #e5e7eb;
    color: #374151;
}

.alert {
    padding: 13px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.alert-error {
    background: #fee2e2;
    color: #991b1b;
}

.info {
    background: #ecfdf5;
    border-left: 4px solid #087f5b;
    padding: 12px;
    margin-bottom: 22px;
    border-radius: 5px;
    color: #065f46;
}

</style>

</head>

<body>

<div class="header">

    <h1>💰 Add Farmer Payment</h1>

    <p>
        Record payment made to a farmer
    </p>

</div>

<div class="container">

    <div class="card">

        <?php if ($message !== ''): ?>

            <div class="alert alert-error">
                <?= htmlspecialchars($message) ?>
            </div>

        <?php endif; ?>


        <div class="info">
            Enter the farmer, collection details, payment amount and status.
        </div>


        <form method="POST">


            <!-- Farmer -->

            <div class="form-group">

                <label>
                    Farmer <span class="required">*</span>
                </label>

                <select name="farmer_id" id="farmer_id" required>

                    <option value="">-- Select Farmer --</option>

                    <?php if ($farmers && $farmers->num_rows > 0): ?>

                        <?php while ($farmer = $farmers->fetch_assoc()): ?>

                            <option
                                value="<?= $farmer['id'] ?>"
                            >
                                <?= htmlspecialchars($farmer['farmer_code']) ?>
                                -
                                <?= htmlspecialchars($farmer['name']) ?>
                            </option>

                        <?php endwhile; ?>

                    <?php endif; ?>

                </select>

            </div>


            <!-- Collection -->

            <div class="form-group">

                <label>
                    Milk Collection
                </label>

                <select name="collection_id" id="collection_id">

                    <option value="">
                        -- Select Collection (Optional) --
                    </option>

                    <?php if ($collections && $collections->num_rows > 0): ?>

                        <?php while ($collection = $collections->fetch_assoc()): ?>

                            <option
                                value="<?= $collection['id'] ?>"
                                data-farmer="<?= $collection['farmer_id'] ?>"
                            >

                                Collection #<?= $collection['id'] ?>

                                -
                                <?= htmlspecialchars($collection['farmer_code']) ?>

                                -
                                <?= htmlspecialchars($collection['collection_date']) ?>

                                -
                                <?= htmlspecialchars($collection['shift']) ?>

                                -
                                <?= htmlspecialchars($collection['quantity_litres']) ?> L

                            </option>

                        <?php endwhile; ?>

                    <?php endif; ?>

                </select>

            </div>


            <!-- Amount -->

            <div class="form-group">

                <label>
                    Payment Amount (₹) <span class="required">*</span>
                </label>

                <input
                    type="number"
                    name="amount"
                    step="0.01"
                    min="0.01"
                    placeholder="Enter amount"
                    required
                >

            </div>


            <!-- Date -->

            <div class="form-group">

                <label>
                    Payment Date <span class="required">*</span>
                </label>

                <input
                    type="date"
                    name="payment_date"
                    value="<?= date('Y-m-d') ?>"
                    required
                >

            </div>


            <!-- Status -->

            <div class="form-group">

                <label>
                    Payment Status
                </label>

                <select name="payment_status">

                    <option value="pending">
                        Pending
                    </option>

                    <option value="paid">
                        Paid
                    </option>

                </select>

            </div>


            <div class="buttons">

                <button
                    type="submit"
                    class="btn btn-save"
                >
                    💾 Save Payment
                </button>

                <a
                    href="index.php"
                    class="btn btn-cancel"
                >
                    Cancel
                </a>

            </div>

        </form>

    </div>

</div>


<script>

const farmerSelect = document.getElementById('farmer_id');
const collectionSelect = document.getElementById('collection_id');

farmerSelect.addEventListener('change', function() {

    const farmerId = this.value;

    const options = collectionSelect.querySelectorAll('option');

    options.forEach(function(option, index) {

        if (index === 0) {
            option.style.display = '';
            return;
        }

        const optionFarmer = option.getAttribute('data-farmer');

        if (farmerId === '' || optionFarmer === farmerId) {
            option.style.display = '';
        } else {
            option.style.display = 'none';
        }

    });

    collectionSelect.value = '';

});

</script>

</body>
</html>