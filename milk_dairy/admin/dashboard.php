<?php
session_start();
require_once '../database/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

/* =========================
   DASHBOARD DATA
========================= */

// Today's collection
$todayCollection = 0;
$result = $conn->query("
    SELECT COALESCE(SUM(quantity_litres),0) AS total
    FROM milk_collections
    WHERE collection_date = CURDATE()
");
if ($result) {
    $todayCollection = $result->fetch_assoc()['total'];
}

// Total farmers
$totalFarmers = 0;
$result = $conn->query("SELECT COUNT(*) AS total FROM farmers");
if ($result) {
    $totalFarmers = $result->fetch_assoc()['total'];
}

// Total customers
$totalCustomers = 0;
$result = $conn->query("SELECT COUNT(*) AS total FROM customers");
if ($result) {
    $totalCustomers = $result->fetch_assoc()['total'];
}

// Milk stock from batches
$totalStock = 0;
$result = $conn->query("
    SELECT COALESCE(SUM(quantity_litres),0) AS total
    FROM batches
    WHERE status NOT IN ('dispatched','rejected')
");
if ($result) {
    $totalStock = $result->fetch_assoc()['total'];
}

// Pending payments
$pendingPayments = 0;
$result = $conn->query("
    SELECT COALESCE(SUM(amount),0) AS total
    FROM farmer_payments
    WHERE payment_status = 'pending'
");
if ($result) {
    $pendingPayments = $result->fetch_assoc()['total'];
}

// Pending dispatches
$pendingDispatch = 0;
$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM dispatches
    WHERE delivery_status IN ('pending','in_transit')
");
if ($result) {
    $pendingDispatch = $result->fetch_assoc()['total'];
}

// Recent milk collections
$recentCollections = $conn->query("
    SELECT 
        mc.collection_date,
        mc.shift,
        mc.quantity_litres,
        f.name AS farmer_name
    FROM milk_collections mc
    INNER JOIN farmers f ON f.id = mc.farmer_id
    ORDER BY mc.id DESC
    LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Smart Dairy Management System</title>

<link rel="stylesheet" href="../css/style.css">

<style>

/* =========================
   GLOBAL
========================= */

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: "Segoe UI", Arial, sans-serif;
    background: #f4f7f6;
    color: #1f2937;
}

a {
    text-decoration: none;
}

/* =========================
   DASHBOARD
========================= */

.dashboard {
    display: flex;
    min-height: 100vh;
}

/* =========================
   SIDEBAR
========================= */

.sidebar {
    width: 255px;
    background: linear-gradient(180deg, #087f5b, #075e45);
    color: white;
    padding: 22px 14px;
    position: fixed;
    top: 0;
    left: 0;
    bottom: 0;
    overflow-y: auto;
    box-shadow: 4px 0 15px rgba(0,0,0,0.08);
}

.logo {
    text-align: center;
    padding: 10px 5px 25px;
    border-bottom: 1px solid rgba(255,255,255,0.18);
    margin-bottom: 15px;
}

.logo-icon {
    font-size: 40px;
}

.logo h2 {
    margin: 6px 0 2px;
    font-size: 21px;
}

.logo span {
    font-size: 11px;
    opacity: .75;
}

.sidebar a {
    display: flex;
    align-items: center;
    gap: 11px;
    color: #eafff7;
    padding: 12px 13px;
    margin: 4px 0;
    border-radius: 10px;
    font-size: 14px;
    transition: .2s;
}

.sidebar a:hover,
.sidebar a.active {
    background: rgba(255,255,255,0.15);
    transform: translateX(3px);
}

.sidebar .logout {
    margin-top: 18px;
    background: rgba(255,255,255,0.12);
}

/* =========================
   MAIN
========================= */

.main {
    margin-left: 255px;
    width: calc(100% - 255px);
    padding: 25px 30px;
}

/* =========================
   TOPBAR
========================= */

.topbar {
    background: white;
    border-radius: 16px;
    padding: 18px 22px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 5px 20px rgba(0,0,0,.05);
    margin-bottom: 25px;
}

.welcome h1 {
    margin: 0;
    font-size: 26px;
}

.welcome p {
    margin: 5px 0 0;
    color: #6b7280;
    font-size: 14px;
}

.logout-btn {
    background: #dc3545;
    color: white;
    padding: 10px 17px;
    border-radius: 9px;
    font-weight: 600;
    font-size: 14px;
    transition: .2s;
}

.logout-btn:hover {
    background: #bb2d3b;
}

/* =========================
   STAT CARDS
========================= */

.stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 18px;
    margin-bottom: 20px;
}

.stat-card {
    background: white;
    padding: 20px;
    border-radius: 16px;
    box-shadow: 0 5px 20px rgba(0,0,0,.05);
    position: relative;
    overflow: hidden;
    transition: .25s;
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 25px rgba(0,0,0,.09);
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    background: #e9f8f2;
    margin-bottom: 14px;
}

.stat-card h3 {
    margin: 0;
    font-size: 25px;
}

.stat-card p {
    margin: 5px 0 0;
    color: #6b7280;
    font-size: 13px;
}

/* =========================
   QUICK ACTIONS
========================= */

.section-title {
    font-size: 19px;
    margin: 25px 0 13px;
}

.quick-actions {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
}

.quick-action {
    background: white;
    padding: 17px;
    border-radius: 13px;
    color: #1f2937;
    box-shadow: 0 4px 15px rgba(0,0,0,.05);
    transition: .2s;
    border: 1px solid #edf2ef;
}

.quick-action:hover {
    transform: translateY(-3px);
    border-color: #0b8f67;
}

.quick-action span {
    font-size: 25px;
}

.quick-action strong {
    display: block;
    margin-top: 9px;
    font-size: 14px;
}

.quick-action small {
    color: #6b7280;
}

/* =========================
   LOWER GRID
========================= */

.lower-grid {
    display: grid;
    grid-template-columns: 1.5fr 1fr;
    gap: 20px;
    margin-top: 20px;
}

.panel {
    background: white;
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 5px 20px rgba(0,0,0,.05);
}

.panel-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.panel-header h2 {
    margin: 0;
    font-size: 18px;
}

/* =========================
   TABLE
========================= */

.table-wrap {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th {
    text-align: left;
    background: #f5f8f7;
    padding: 11px;
    font-size: 12px;
    color: #53605b;
}

td {
    padding: 12px 11px;
    border-bottom: 1px solid #edf0ee;
    font-size: 13px;
}

.badge {
    padding: 5px 9px;
    border-radius: 20px;
    background: #e7f7ef;
    color: #087f5b;
    font-size: 11px;
    font-weight: 600;
}

/* =========================
   TRACEABILITY
========================= */

.trace-flow {
    display: flex;
    flex-direction: column;
    gap: 9px;
}

.trace-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 12px;
    background: #f7faf9;
    border-radius: 9px;
    font-size: 13px;
}

.trace-number {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: #087f5b;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: bold;
}

/* =========================
   ALERT CARDS
========================= */

.alert-box {
    margin-top: 18px;
    padding: 14px;
    border-radius: 10px;
    background: #fff8e6;
    border-left: 4px solid #f0ad00;
}

.alert-box strong {
    font-size: 14px;
}

.alert-box p {
    margin: 5px 0 0;
    font-size: 12px;
    color: #6b7280;
}

/* =========================
   RESPONSIVE
========================= */

@media(max-width: 1100px) {

    .stats {
        grid-template-columns: repeat(2, 1fr);
    }

    .quick-actions {
        grid-template-columns: repeat(2, 1fr);
    }

    .lower-grid {
        grid-template-columns: 1fr;
    }
}

@media(max-width: 750px) {

    .sidebar {
        width: 210px;
    }

    .main {
        margin-left: 210px;
        width: calc(100% - 210px);
        padding: 15px;
    }

    .topbar {
        padding: 15px;
    }

    .welcome h1 {
        font-size: 21px;
    }
}

</style>
</head>

<body>

<div class="dashboard">

<!-- ================= SIDEBAR ================= -->

<aside class="sidebar">

    <div class="logo">
        <div class="logo-icon">🥛</div>
        <h2>Smart Dairy</h2>
        <span>Management System</span>
    </div>

    <a href="dashboard.php" class="active">🏠 Dashboard</a>

    <a href="farmers/">👨‍🌾 Farmers</a>

    <a href="milk_collection/">🥛 Milk Collection</a>

    <a href="quality_testing/">🧪 Quality Testing</a>

    <a href="chilling/">❄️ Chilling Tanks</a>

    <a href="batches/">📦 Batches</a>

    <a href="processing/">⚙️ Processing</a>

    <a href="customers/">🏢 Customers</a>

    <a href="dispatch/">🚚 Dispatch</a>

    <a href="vehicles/">🚛 Transport</a>

    <a href="payments/">💰 Payments</a>

    <a href="billing/">🧾 Billing</a>

    <a href="reports/">📊 Reports</a>

    <a href="products/">📦 Stock / Products</a>

    <a href="machines/">🔧 Maintenance</a>

    <a href="traceability/">🔍 Traceability</a>

    <a href="../logout.php" class="logout">🚪 Logout</a>

</aside>


<!-- ================= MAIN ================= -->

<main class="main">

<!-- TOPBAR -->

<div class="topbar">

    <div class="welcome">

        <h1>Dashboard</h1>

        <p>
            Welcome back,
            <strong><?= htmlspecialchars($_SESSION['user']['name']) ?></strong>
            👋
        </p>

    </div>

    <a href="../logout.php" class="logout-btn">
        🚪 Logout
    </a>

</div>


<!-- ================= STATISTICS ================= -->

<div class="stats">

    <div class="stat-card">

        <div class="stat-icon">🥛</div>

        <h3>
            <?= number_format((float)$todayCollection, 2) ?> L
        </h3>

        <p>Today's Milk Collection</p>

    </div>


    <div class="stat-card">

        <div class="stat-icon">📦</div>

        <h3>
            <?= number_format((float)$totalStock, 2) ?> L
        </h3>

        <p>Available Milk Stock</p>

    </div>


    <div class="stat-card">

        <div class="stat-icon">👨‍🌾</div>

        <h3>
            <?= number_format((int)$totalFarmers) ?>
        </h3>

        <p>Total Farmers</p>

    </div>


    <div class="stat-card">

        <div class="stat-icon">🏢</div>

        <h3>
            <?= number_format((int)$totalCustomers) ?>
        </h3>

        <p>Total Customers</p>

    </div>

</div>


<!-- SECONDARY STATS -->

<div class="stats">

    <div class="stat-card">

        <div class="stat-icon">💰</div>

        <h3>
            ₹<?= number_format((float)$pendingPayments, 2) ?>
        </h3>

        <p>Pending Farmer Payments</p>

    </div>


    <div class="stat-card">

        <div class="stat-icon">🚚</div>

        <h3>
            <?= number_format((int)$pendingDispatch) ?>
        </h3>

        <p>Pending / In-Transit Dispatch</p>

    </div>

</div>


<!-- QUICK ACTIONS -->

<h2 class="section-title">⚡ Quick Actions</h2>

<div class="quick-actions">

    <a href="farmers/add.php" class="quick-action">
        <span>👨‍🌾</span>
        <strong>Add Farmer</strong>
        <small>Register new farmer</small>
    </a>

    <a href="milk_collection/add.php" class="quick-action">
        <span>🥛</span>
        <strong>Milk Collection</strong>
        <small>Record today's milk</small>
    </a>

    <a href="quality_testing/add.php" class="quick-action">
        <span>🧪</span>
        <strong>Quality Test</strong>
        <small>Check milk quality</small>
    </a>

    <a href="dispatch/add.php" class="quick-action">
        <span>🚚</span>
        <strong>Create Dispatch</strong>
        <small>Send milk to customer</small>
    </a>

</div>


<!-- LOWER CONTENT -->

<div class="lower-grid">


<!-- RECENT COLLECTIONS -->

<div class="panel">

    <div class="panel-header">

        <h2>🥛 Recent Milk Collections</h2>

        <a href="milk_collection/" style="color:#087f5b;font-size:13px;">
            View All →
        </a>

    </div>

    <div class="table-wrap">

        <table>

            <thead>

                <tr>
                    <th>Farmer</th>
                    <th>Date</th>
                    <th>Shift</th>
                    <th>Quantity</th>
                </tr>

            </thead>

            <tbody>

            <?php if ($recentCollections && $recentCollections->num_rows > 0): ?>

                <?php while ($row = $recentCollections->fetch_assoc()): ?>

                    <tr>

                        <td>
                            <strong>
                                <?= htmlspecialchars($row['farmer_name']) ?>
                            </strong>
                        </td>

                        <td>
                            <?= htmlspecialchars($row['collection_date']) ?>
                        </td>

                        <td>
                            <span class="badge">
                                <?= ucfirst(htmlspecialchars($row['shift'])) ?>
                            </span>
                        </td>

                        <td>
                            <?= number_format((float)$row['quantity_litres'], 2) ?> L
                        </td>

                    </tr>

                <?php endwhile; ?>

            <?php else: ?>

                <tr>

                    <td colspan="4" style="text-align:center;color:#777;">
                        No milk collection records found.
                    </td>

                </tr>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>


<!-- TRACEABILITY -->

<div class="panel">

    <div class="panel-header">

        <h2>🔍 Milk Traceability</h2>

    </div>


    <div class="trace-flow">

        <div class="trace-item">
            <span class="trace-number">1</span>
            👨‍🌾 Farmer
        </div>

        <div class="trace-item">
            <span class="trace-number">2</span>
            🥛 Collection
        </div>

        <div class="trace-item">
            <span class="trace-number">3</span>
            🧪 Quality Testing
        </div>

        <div class="trace-item">
            <span class="trace-number">4</span>
            📦 Batch
        </div>

        <div class="trace-item">
            <span class="trace-number">5</span>
            ❄️ Chilling
        </div>

        <div class="trace-item">
            <span class="trace-number">6</span>
            ⚙️ Processing
        </div>

        <div class="trace-item">
            <span class="trace-number">7</span>
            🚚 Dispatch
        </div>

        <div class="trace-item">
            <span class="trace-number">8</span>
            🏢 Company Received
        </div>

    </div>


    <div class="alert-box">

        <strong>🛡️ Safety & Traceability</strong>

        <p>
            Every milk batch can be tracked from farmer
            collection to customer delivery.
        </p>

    </div>

</div>


</div>

</main>

</div>

</body>
</html>