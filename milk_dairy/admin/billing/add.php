<?php
session_start();
require_once '../../database/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$message = '';
$error = '';

$customers = $conn->query("
    SELECT id, customer_code, name
    FROM customers
    WHERE status = 'active'
    ORDER BY name ASC
");

$batches = $conn->query("
    SELECT id, batch_number, quantity_litres
    FROM batches
    WHERE status NOT IN ('rejected', 'dispatched')
    ORDER BY id DESC
");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $invoice_number = trim($_POST['invoice_number'] ?? '');
    $customer_id = (int)($_POST['customer_id'] ?? 0);
    $batch_id = !empty($_POST['batch_id']) ? (int)$_POST['batch_id'] : null;
    $quantity = (float)($_POST['quantity_litres'] ?? 0);
    $rate = (float)($_POST['rate_per_litre'] ?? 0);
    $tax_percent = (float)($_POST['tax_percent'] ?? 0);
    $invoice_date = $_POST['invoice_date'] ?? date('Y-m-d');
    $payment_status = $_POST['payment_status'] ?? 'Pending';
    $notes = trim($_POST['notes'] ?? '');

    $subtotal = $quantity * $rate;
    $tax_amount = ($subtotal * $tax_percent) / 100;
    $grand_total = $subtotal + $tax_amount;

    if (
        $invoice_number === '' ||
        $customer_id <= 0 ||
        $quantity <= 0 ||
        $rate < 0 ||
        $invoice_date === ''
    ) {
        $error = "Please fill all required fields correctly.";
    } else {

        $stmt = $conn->prepare("
            INSERT INTO bills
            (
                invoice_number,
                customer_id,
                batch_id,
                quantity_litres,
                rate_per_litre,
                subtotal,
                tax_percent,
                tax_amount,
                grand_total,
                invoice_date,
                payment_status,
                notes
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        if ($stmt) {

            $stmt->bind_param(
                "siidddddssss",
                $invoice_number,
                $customer_id,
                $batch_id,
                $quantity,
                $rate,
                $subtotal,
                $tax_percent,
                $tax_amount,
                $grand_total,
                $invoice_date,
                $payment_status,
                $notes
            );

            if ($stmt->execute()) {

                $new_id = $stmt->insert_id;

                header("Location: view.php?id=" . $new_id);
                exit;

            } else {

                if ($stmt->errno == 1062) {
                    $error = "Invoice number already exists.";
                } else {
                    $error = "Unable to create bill: " . $stmt->error;
                }
            }

            $stmt->close();

        } else {
            $error = "Database error: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Create Bill</title>

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f1f8f5;
    color: #243b35;
}

.header {
    background: linear-gradient(135deg, #087f5b, #12b886);
    color: white;
    padding: 22px 30px;
}

.header h1 {
    margin: 0;
    font-size: 26px;
}

.header p {
    margin: 6px 0 0;
}

.container {
    max-width: 950px;
    margin: 30px auto;
    padding: 0 20px;
}

.card {
    background: white;
    padding: 30px;
    border-radius: 16px;
    box-shadow: 0 6px 25px rgba(0,0,0,.08);
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
    font-weight: bold;
    margin-bottom: 8px;
}

input,
select,
textarea {
    padding: 12px;
    border: 1px solid #ccd8d3;
    border-radius: 8px;
    font-size: 15px;
}

input:focus,
select:focus,
textarea:focus {
    outline: none;
    border-color: #087f5b;
}

textarea {
    min-height: 100px;
    resize: vertical;
}

.summary {
    margin-top: 25px;
    padding: 20px;
    background: #eaf8f3;
    border-radius: 12px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
}

.total {
    border-top: 2px solid #087f5b;
    margin-top: 10px;
    padding-top: 15px;
    font-size: 20px;
    font-weight: bold;
    color: #087f5b;
}

.actions {
    margin-top: 25px;
    display: flex;
    gap: 12px;
}

.btn {
    border: none;
    padding: 12px 20px;
    border-radius: 8px;
    cursor: pointer;
    text-decoration: none;
    font-weight: bold;
    font-size: 15px;
}

.btn-save {
    background: #087f5b;
    color: white;
}

.btn-save:hover {
    background: #056b4d;
}

.btn-back {
    background: #e9ecef;
    color: #333;
}

.error {
    background: #ffe3e3;
    color: #c92a2a;
    padding: 13px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.required {
    color: #d6336c;
}

@media(max-width: 700px) {

    .form-grid {
        grid-template-columns: 1fr;
    }

    .full {
        grid-column: auto;
    }

    .actions {
        flex-direction: column;
    }
}

</style>

</head>

<body>

<div class="header">

    <h1>🧾 Create New Bill</h1>

    <p>Create customer invoice for dairy products / milk supply</p>

</div>

<div class="container">

<div class="card">

<?php if ($error !== ''): ?>

<div class="error">
    <?= htmlspecialchars($error) ?>
</div>

<?php endif; ?>

<form method="POST">

<div class="form-grid">

<div class="form-group">

<label>
    Invoice Number <span class="required">*</span>
</label>

<input
    type="text"
    name="invoice_number"
    placeholder="INV-001"
    value="<?= htmlspecialchars($_POST['invoice_number'] ?? '') ?>"
    required
>

</div>


<div class="form-group">

<label>
    Invoice Date <span class="required">*</span>
</label>

<input
    type="date"
    name="invoice_date"
    value="<?= htmlspecialchars($_POST['invoice_date'] ?? date('Y-m-d')) ?>"
    required
>

</div>


<div class="form-group">

<label>
    Customer / Company <span class="required">*</span>
</label>

<select name="customer_id" required>

<option value="">-- Select Customer --</option>

<?php if ($customers): ?>

<?php while ($customer = $customers->fetch_assoc()): ?>

<option
    value="<?= (int)$customer['id'] ?>"
    <?= (($_POST['customer_id'] ?? '') == $customer['id']) ? 'selected' : '' ?>
>
    <?= htmlspecialchars($customer['customer_code']) ?>
    -
    <?= htmlspecialchars($customer['name']) ?>
</option>

<?php endwhile; ?>

<?php endif; ?>

</select>

</div>


<div class="form-group">

<label>Batch Number</label>

<select name="batch_id">

<option value="">-- Select Batch --</option>

<?php if ($batches): ?>

<?php while ($batch = $batches->fetch_assoc()): ?>

<option
    value="<?= (int)$batch['id'] ?>"
    <?= (($_POST['batch_id'] ?? '') == $batch['id']) ? 'selected' : '' ?>
>
    <?= htmlspecialchars($batch['batch_number']) ?>
    -
    <?= number_format($batch['quantity_litres'], 2) ?> L
</option>

<?php endwhile; ?>

<?php endif; ?>

</select>

</div>


<div class="form-group">

<label>
    Quantity (Litres) <span class="required">*</span>
</label>

<input
    type="number"
    step="0.01"
    min="0.01"
    id="quantity"
    name="quantity_litres"
    placeholder="100"
    value="<?= htmlspecialchars($_POST['quantity_litres'] ?? '') ?>"
    required
>

</div>


<div class="form-group">

<label>
    Rate per Litre (₹) <span class="required">*</span>
</label>

<input
    type="number"
    step="0.01"
    min="0"
    id="rate"
    name="rate_per_litre"
    placeholder="50"
    value="<?= htmlspecialchars($_POST['rate_per_litre'] ?? '') ?>"
    required
>

</div>


<div class="form-group">

<label>
    Tax / GST (%)
</label>

<input
    type="number"
    step="0.01"
    min="0"
    id="tax"
    name="tax_percent"
    value="<?= htmlspecialchars($_POST['tax_percent'] ?? '0') ?>"
>

</div>


<div class="form-group">

<label>
    Payment Status
</label>

<select name="payment_status">

<option value="Pending">Pending</option>

<option
    value="Paid"
    <?= (($_POST['payment_status'] ?? '') === 'Paid') ? 'selected' : '' ?>
>
    Paid
</option>

<option
    value="Partially Paid"
    <?= (($_POST['payment_status'] ?? '') === 'Partially Paid') ? 'selected' : '' ?>
>
    Partially Paid
</option>

</select>

</div>


<div class="form-group full">

<label>Notes</label>

<textarea
    name="notes"
    placeholder="Additional invoice notes..."
><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea>

</div>

</div>


<div class="summary">

<div class="summary-row">
    <span>Subtotal</span>
    <strong id="subtotal">₹0.00</strong>
</div>

<div class="summary-row">
    <span>Tax</span>
    <strong id="taxAmount">₹0.00</strong>
</div>

<div class="summary-row total">
    <span>Grand Total</span>
    <strong id="grandTotal">₹0.00</strong>
</div>

</div>


<div class="actions">

<button type="submit" class="btn btn-save">
    💾 Create Bill
</button>

<a href="index.php" class="btn btn-back">
    ← Back to Billing
</a>

</div>

</form>

</div>

</div>


<script>

function calculateBill() {

    let quantity =
        parseFloat(document.getElementById('quantity').value) || 0;

    let rate =
        parseFloat(document.getElementById('rate').value) || 0;

    let tax =
        parseFloat(document.getElementById('tax').value) || 0;

    let subtotal = quantity * rate;

    let taxAmount = subtotal * tax / 100;

    let grandTotal = subtotal + taxAmount;

    document.getElementById('subtotal').textContent =
        '₹' + subtotal.toFixed(2);

    document.getElementById('taxAmount').textContent =
        '₹' + taxAmount.toFixed(2);

    document.getElementById('grandTotal').textContent =
        '₹' + grandTotal.toFixed(2);
}

document.getElementById('quantity').addEventListener('input', calculateBill);
document.getElementById('rate').addEventListener('input', calculateBill);
document.getElementById('tax').addEventListener('input', calculateBill);

calculateBill();

</script>

</body>
</html>