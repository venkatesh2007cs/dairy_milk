<?php
session_start();
require_once '../../database/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    die("Invalid invoice ID.");
}

$stmt = $conn->prepare("
    SELECT
        b.*,
        c.customer_code,
        c.name AS customer_name,
        c.customer_type,
        c.phone,
        c.email,
        c.address,
        ba.batch_number
    FROM bills b
    INNER JOIN customers c ON b.customer_id = c.id
    LEFT JOIN batches ba ON b.batch_id = ba.id
    WHERE b.id = ?
    LIMIT 1
");

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();
$bill = $result->fetch_assoc();

$stmt->close();

if (!$bill) {
    die("Invoice not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>
Invoice <?= htmlspecialchars($bill['invoice_number']) ?>
</title>

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #eef5f2;
    color: #222;
}

.container {
    max-width: 900px;
    margin: 30px auto;
    padding: 20px;
}

.invoice {
    background: white;
    padding: 40px;
    border-radius: 15px;
    box-shadow: 0 5px 25px rgba(0,0,0,.10);
}

.top {
    display: flex;
    justify-content: space-between;
    gap: 20px;
    border-bottom: 3px solid #087f5b;
    padding-bottom: 20px;
}

.company h1 {
    margin: 0;
    color: #087f5b;
    font-size: 30px;
}

.company p {
    margin: 5px 0;
    color: #666;
}

.invoice-info {
    text-align: right;
}

.invoice-info h2 {
    margin: 0;
    color: #087f5b;
}

.invoice-info p {
    margin: 6px 0;
}

.section {
    margin-top: 30px;
}

.section-title {
    color: #087f5b;
    font-size: 18px;
    font-weight: bold;
    margin-bottom: 10px;
}

.customer-box {
    background: #f1faf7;
    padding: 18px;
    border-radius: 10px;
}

.customer-box p {
    margin: 6px 0;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}

th {
    background: #087f5b;
    color: white;
    padding: 13px;
    text-align: left;
}

td {
    padding: 13px;
    border-bottom: 1px solid #ddd;
}

.text-right {
    text-align: right;
}

.summary {
    width: 350px;
    margin-left: auto;
    margin-top: 20px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    padding: 9px 0;
}

.grand {
    border-top: 2px solid #087f5b;
    margin-top: 8px;
    padding-top: 15px;
    font-size: 21px;
    font-weight: bold;
    color: #087f5b;
}

.status {
    display: inline-block;
    padding: 7px 14px;
    border-radius: 20px;
    font-weight: bold;
}

.status-paid {
    background: #d1e7dd;
    color: #0f5132;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.status-partial {
    background: #cfe2ff;
    color: #084298;
}

.notes {
    background: #fafafa;
    padding: 15px;
    border-radius: 8px;
}

.footer {
    margin-top: 40px;
    padding-top: 20px;
    border-top: 1px solid #ddd;
    text-align: center;
    color: #777;
}

.buttons {
    max-width: 900px;
    margin: 20px auto;
    padding: 0 20px;
    display: flex;
    gap: 10px;
}

.btn {
    padding: 11px 18px;
    border-radius: 8px;
    text-decoration: none;
    border: none;
    cursor: pointer;
    font-weight: bold;
}

.print {
    background: #087f5b;
    color: white;
}

.back {
    background: #dee2e6;
    color: #222;
}

@media(max-width: 700px) {

    .invoice {
        padding: 20px;
    }

    .top {
        flex-direction: column;
    }

    .invoice-info {
        text-align: left;
    }

    .summary {
        width: 100%;
    }

    table {
        font-size: 13px;
    }
}

@media print {

    body {
        background: white;
    }

    .buttons {
        display: none;
    }

    .invoice {
        box-shadow: none;
        border-radius: 0;
        margin: 0;
    }

    .container {
        margin: 0;
        padding: 0;
    }

}

</style>

</head>

<body>

<div class="buttons">

    <button class="btn print" onclick="window.print()">
        🖨️ Print Invoice
    </button>

    <a href="index.php" class="btn back">
        ← Back to Billing
    </a>

</div>


<div class="container">

<div class="invoice">

    <div class="top">

        <div class="company">

            <h1>Smart Dairy</h1>

            <p>Smart Dairy Management System</p>

            <p>Milk Collection • Processing • Dispatch</p>

        </div>


        <div class="invoice-info">

            <h2>INVOICE</h2>

            <p>
                <strong>
                    <?= htmlspecialchars($bill['invoice_number']) ?>
                </strong>
            </p>

            <p>
                Date:
                <?= date(
                    'd-m-Y',
                    strtotime($bill['invoice_date'])
                ) ?>
            </p>

        </div>

    </div>


    <div class="section">

        <div class="section-title">
            Bill To
        </div>

        <div class="customer-box">

            <p>
                <strong>
                    <?= htmlspecialchars($bill['customer_name']) ?>
                </strong>
            </p>

            <p>
                Customer Code:
                <?= htmlspecialchars($bill['customer_code']) ?>
            </p>

            <p>
                Type:
                <?= htmlspecialchars($bill['customer_type']) ?>
            </p>

            <?php if (!empty($bill['phone'])): ?>

            <p>
                Phone:
                <?= htmlspecialchars($bill['phone']) ?>
            </p>

            <?php endif; ?>

            <?php if (!empty($bill['email'])): ?>

            <p>
                Email:
                <?= htmlspecialchars($bill['email']) ?>
            </p>

            <?php endif; ?>

            <?php if (!empty($bill['address'])): ?>

            <p>
                Address:
                <?= nl2br(htmlspecialchars($bill['address'])) ?>
            </p>

            <?php endif; ?>

        </div>

    </div>


    <div class="section">

        <div class="section-title">
            Invoice Details
        </div>

        <table>

            <thead>

            <tr>

                <th>Description</th>

                <th>Batch</th>

                <th>Quantity</th>

                <th>Rate</th>

                <th class="text-right">
                    Amount
                </th>

            </tr>

            </thead>

            <tbody>

            <tr>

                <td>
                    Milk / Dairy Supply
                </td>

                <td>
                    <?= htmlspecialchars(
                        $bill['batch_number'] ?? '-'
                    ) ?>
                </td>

                <td>
                    <?= number_format(
                        $bill['quantity_litres'],
                        2
                    ) ?> L
                </td>

                <td>
                    ₹<?= number_format(
                        $bill['rate_per_litre'],
                        2
                    ) ?>
                </td>

                <td class="text-right">

                    ₹<?= number_format(
                        $bill['subtotal'],
                        2
                    ) ?>

                </td>

            </tr>

            </tbody>

        </table>

    </div>


    <div class="summary">

        <div class="summary-row">

            <span>Subtotal</span>

            <strong>
                ₹<?= number_format(
                    $bill['subtotal'],
                    2
                ) ?>
            </strong>

        </div>


        <div class="summary-row">

            <span>
                Tax
                (<?= number_format(
                    $bill['tax_percent'],
                    2
                ) ?>%)
            </span>

            <strong>
                ₹<?= number_format(
                    $bill['tax_amount'],
                    2
                ) ?>
            </strong>

        </div>


        <div class="summary-row grand">

            <span>Grand Total</span>

            <span>
                ₹<?= number_format(
                    $bill['grand_total'],
                    2
                ) ?>
            </span>

        </div>

    </div>


    <div class="section">

        <div class="section-title">
            Payment Status
        </div>

        <?php

        if ($bill['payment_status'] === 'Paid') {

            echo '<span class="status status-paid">PAID</span>';

        } elseif ($bill['payment_status'] === 'Partially Paid') {

            echo '<span class="status status-partial">PARTIALLY PAID</span>';

        } else {

            echo '<span class="status status-pending">PENDING</span>';

        }

        ?>

    </div>


    <?php if (!empty($bill['notes'])): ?>

    <div class="section">

        <div class="section-title">
            Notes
        </div>

        <div class="notes">

            <?= nl2br(
                htmlspecialchars($bill['notes'])
            ) ?>

        </div>

    </div>

    <?php endif; ?>


    <div class="footer">

        <p>
            Thank you for doing business with Smart Dairy.
        </p>

        <p>
            This is a computer-generated invoice.
        </p>

    </div>

</div>

</div>

</body>
</html>