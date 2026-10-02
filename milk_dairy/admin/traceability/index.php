<?php
session_start();
require_once '../../database/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$sql = "SELECT
            te.id,
            te.batch_id,
            b.batch_number,
            te.event_type,
            te.event_reference,
            te.event_datetime,
            te.notes
        FROM traceability_events te
        INNER JOIN batches b ON te.batch_id = b.id
        ORDER BY te.id DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Traceability - Smart Dairy</title>

<style>
*{
    box-sizing:border-box;
    font-family:Arial,sans-serif;
}

body{
    margin:0;
    background:#f0fdf4;
    color:#1f2937;
}

.header{
    background:linear-gradient(135deg,#087f5b,#12b886);
    color:white;
    padding:25px 35px;
}

.header h1{
    margin:0;
    font-size:28px;
}

.header p{
    margin:7px 0 0;
    opacity:.9;
}

.container{
    padding:30px;
}

.flow{
    background:white;
    padding:20px;
    border-radius:14px;
    margin-bottom:25px;
    box-shadow:0 5px 20px rgba(0,0,0,.07);
}

.flow-title{
    color:#087f5b;
    font-size:18px;
    font-weight:bold;
    margin-bottom:15px;
}

.flow-items{
    display:flex;
    align-items:center;
    gap:8px;
    flex-wrap:wrap;
}

.flow-item{
    background:#ecfdf5;
    color:#065f46;
    padding:9px 13px;
    border-radius:20px;
    font-weight:bold;
}

.arrow{
    color:#087f5b;
    font-weight:bold;
}

.topbar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:20px;
}

.topbar h2{
    margin:0;
    color:#087f5b;
}

.btn{
    background:#087f5b;
    color:white;
    text-decoration:none;
    padding:11px 18px;
    border-radius:8px;
    font-weight:bold;
}

.btn:hover{
    background:#056b4c;
}

.card{
    background:white;
    padding:20px;
    border-radius:15px;
    box-shadow:0 5px 20px rgba(0,0,0,.08);
    overflow-x:auto;
}

table{
    width:100%;
    border-collapse:collapse;
    min-width:950px;
}

th{
    background:#087f5b;
    color:white;
    padding:13px;
    text-align:left;
}

td{
    padding:13px;
    border-bottom:1px solid #e5e7eb;
}

tr:hover{
    background:#f0fdf4;
}

.event{
    display:inline-block;
    background:#e8f5e9;
    color:#087f5b;
    padding:6px 10px;
    border-radius:15px;
    font-weight:bold;
}

.empty{
    text-align:center;
    padding:35px;
    color:#6b7280;
}

@media(max-width:700px){
    .container{
        padding:15px;
    }

    .topbar{
        flex-direction:column;
        align-items:flex-start;
        gap:15px;
    }
}
</style>
</head>

<body>

<div class="header">
    <h1>🔗 Traceability</h1>
    <p>Track every batch from collection to company delivery</p>
</div>

<div class="container">

<div class="flow">

    <div class="flow-title">
        🥛 Dairy Traceability Flow
    </div>

    <div class="flow-items">

        <span class="flow-item">👨‍🌾 Farmer</span>
        <span class="arrow">→</span>

        <span class="flow-item">🥛 Collection</span>
        <span class="arrow">→</span>

        <span class="flow-item">🧪 Quality</span>
        <span class="arrow">→</span>

        <span class="flow-item">📦 Batch</span>
        <span class="arrow">→</span>

        <span class="flow-item">❄️ Chilling</span>
        <span class="arrow">→</span>

        <span class="flow-item">⚙️ Processing</span>
        <span class="arrow">→</span>

        <span class="flow-item">🏭 Storage</span>
        <span class="arrow">→</span>

        <span class="flow-item">🚚 Dispatch</span>
        <span class="arrow">→</span>

        <span class="flow-item">🏢 Company Received</span>

    </div>

</div>


<div class="topbar">

    <h2>Traceability Events</h2>

    <a href="add.php" class="btn">
        + Add Event
    </a>

</div>


<div class="card">

<?php if ($result && $result->num_rows > 0): ?>

<table>

<tr>
    <th>ID</th>
    <th>Batch</th>
    <th>Event Type</th>
    <th>Reference</th>
    <th>Date & Time</th>
    <th>Notes</th>
</tr>

<?php while($row = $result->fetch_assoc()): ?>

<tr>

<td>
    <?= htmlspecialchars($row['id']) ?>
</td>

<td>
    <strong>
        <?= htmlspecialchars($row['batch_number']) ?>
    </strong>
</td>

<td>
    <span class="event">
        <?= htmlspecialchars($row['event_type']) ?>
    </span>
</td>

<td>
    <?= $row['event_reference']
        ? htmlspecialchars($row['event_reference'])
        : '-' ?>
</td>

<td>
    <?= $row['event_datetime']
        ? htmlspecialchars($row['event_datetime'])
        : '-' ?>
</td>

<td>
    <?= $row['notes']
        ? htmlspecialchars($row['notes'])
        : '-' ?>
</td>

</tr>

<?php endwhile; ?>

</table>

<?php else: ?>

<div class="empty">

    <h3>No traceability events found</h3>

    <p>
        Add an event to start tracking a batch.
    </p>

</div>

<?php endif; ?>

</div>

</div>

</body>
</html>