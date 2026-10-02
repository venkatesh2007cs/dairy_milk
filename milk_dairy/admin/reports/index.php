<?php
session_start();
require_once '../../database/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

/* Farmers */
$farmers = 0;
$result = $conn->query("SELECT COUNT(*) AS total FROM farmers");
if ($result) {
    $farmers = $result->fetch_assoc()['total'];
}

/* Customers */
$customers = 0;
$result = $conn->query("SELECT COUNT(*) AS total FROM customers");
if ($result) {
    $customers = $result->fetch_assoc()['total'];
}

/* Total milk */
$total_milk = 0;
$result = $conn->query("
    SELECT COALESCE(SUM(quantity_litres),0) AS total
    FROM milk_collections
");
if ($result) {
    $total_milk = $result->fetch_assoc()['total'];
}

/* Batches */
$batches = 0;
$result = $conn->query("SELECT COUNT(*) AS total FROM batches");
if ($result) {
    $batches = $result->fetch_assoc()['total'];
}

/* Dispatch */
$dispatches = 0;
$result = $conn->query("SELECT COUNT(*) AS total FROM dispatches");
if ($result) {
    $dispatches = $result->fetch_assoc()['total'];
}

/* Payments */
$total_payments = 0;
$result = $conn->query("
    SELECT COALESCE(SUM(amount),0) AS total
    FROM farmer_payments
");
if ($result) {
    $total_payments = $result->fetch_assoc()['total'];
}

/* Products */
$product_stock = 0;
$result = $conn->query("
    SELECT COALESCE(SUM(quantity),0) AS total
    FROM products
    WHERE status='active'
");
if ($result) {
    $product_stock = $result->fetch_assoc()['total'];
}

/* Machines */
$machines = 0;
$result = $conn->query("SELECT COUNT(*) AS total FROM machines");
if ($result) {
    $machines = $result->fetch_assoc()['total'];
}

/* Payment pending */
$pending_payments = 0;
$result = $conn->query("
    SELECT COALESCE(SUM(amount),0) AS total
    FROM farmer_payments
    WHERE payment_status='pending'
");
if ($result) {
    $pending_payments = $result->fetch_assoc()['total'];
}

/* Dispatch pending */
$pending_dispatches = 0;
$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM dispatches
    WHERE delivery_status='pending'
");
if ($result) {
    $pending_dispatches = $result->fetch_assoc()['total'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Reports - Smart Dairy</title>

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
    padding: 28px 35px;
}

.header h1 {
    margin: 0;
    font-size: 30px;
}

.header p {
    margin: 8px 0 0;
    opacity: .9;
}

.container {
    padding: 30px;
}

.title {
    color: #087f5b;
    margin-bottom: 20px;
}

/* Cards */

.grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 30px;
}

.card {
    background: white;
    padding: 22px;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,.07);
}

.card-icon {
    font-size: 30px;
}

.card-title {
    color: #6b7280;
    margin-top: 10px;
}

.card-value {
    font-size: 28px;
    font-weight: bold;
    color: #087f5b;
    margin-top: 8px;
}

/* Alerts */

.alert-title {
    color: #087f5b;
    margin-top: 10px;
}

.alert-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

.alert {
    background: white;
    padding: 22px;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,.07);
}

.alert.pending {
    border-left: 5px solid #f59e0b;
}

.alert.info {
    border-left: 5px solid #2563eb;
}

.alert h3 {
    margin-top: 0;
}

.alert-number {
    font-size: 25px;
    font-weight: bold;
    color: #d97706;
}

.links {
    margin-top: 30px;
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.btn {
    text-decoration: none;
    background: #087f5b;
    color: white;
    padding: 11px 18px;
    border-radius: 8px;
    font-weight: bold;
}

.btn:hover {
    background: #056b4c;
}

@media(max-width:1000px) {
    .grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media(max-width:600px) {
    .container {
        padding: 15px;
    }

    .grid,
    .alert-grid {
        grid-template-columns: 1fr;
    }
}

</style>

</head>

<body>

<div class="header">

    <h1>📊 Reports & Dashboard</h1>

    <p>
        Smart Dairy Management System - Overall Reports
    </p>

</div>

<div class="container">

<h2 class="title">
    System Overview
</h2>

<div class="grid">

    <div class="card">
        <div class="card-icon">👨‍🌾</div>
        <div class="card-title">Total Farmers</div>
        <div class="card-value">
            <?= number_format($farmers) ?>
        </div>
    </div>


    <div class="card">
        <div class="card-icon">🏢</div>
        <div class="card-title">Customers / Companies</div>
        <div class="card-value">
            <?= number_format($customers) ?>
        </div>
    </div>


    <div class="card">
        <div class="card-icon">🥛</div>
        <div class="card-title">Total Milk Collected</div>
        <div class="card-value">
            <?= number_format($total_milk, 2) ?> L
        </div>
    </div>


    <div class="card">
        <div class="card-icon">📦</div>
        <div class="card-title">Total Batches</div>
        <div class="card-value">
            <?= number_format($batches) ?>
        </div>
    </div>


    <div class="card">
        <div class="card-icon">🚚</div>
        <div class="card-title">Total Dispatches</div>
        <div class="card-value">
            <?= number_format($dispatches) ?>
        </div>
    </div>


    <div class="card">
        <div class="card-icon">💰</div>
        <div class="card-title">Farmer Payments</div>
        <div class="card-value">
            ₹<?= number_format($total_payments, 2) ?>
        </div>
    </div>


    <div class="card">
        <div class="card-icon">🥛</div>
        <div class="card-title">Product Stock</div>
        <div class="card-value">
            <?= number_format($product_stock, 2) ?>
        </div>
    </div>


    <div class="card">
        <div class="card-icon">🔧</div>
        <div class="card-title">Machines</div>
        <div class="card-value">
            <?= number_format($machines) ?>
        </div>
    </div>

</div>


<h2 class="title">
    ⚠️ Alerts
</h2>

<div class="alert-grid">

    <div class="alert pending">

        <h3>💰 Pending Farmer Payments</h3>

        <div class="alert-number">
            ₹<?= number_format($pending_payments, 2) ?>
        </div>

        <p>
            Amount currently marked as pending.
        </p>

    </div>


    <div class="alert info">

        <h3>🚚 Pending Dispatches</h3>

        <div class="alert-number">
            <?= number_format($pending_dispatches) ?>
        </div>

        <p>
            Dispatch records waiting for delivery processing.
        </p>

    </div>

</div>


<div class="links">

    <a href="../dashboard.php" class="btn">
        🏠 Dashboard
    </a>

    <a href="../farmers/" class="btn">
        👨‍🌾 Farmers
    </a>

    <a href="../milk_collection/" class="btn">
        🥛 Milk Collection
    </a>

    <a href="../dispatch/" class="btn">
        🚚 Dispatch
    </a>

    <a href="../payments/" class="btn">
        💰 Payments
    </a>

    <a href="../traceability/" class="btn">
        🔗 Traceability
    </a>

</div>

</div>

</body>
</html>