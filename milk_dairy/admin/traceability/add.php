<?php
session_start();
require_once '../../database/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $batch_id = intval($_POST['batch_id'] ?? 0);
    $event_type = trim($_POST['event_type'] ?? '');
    $event_reference = trim($_POST['event_reference'] ?? '');
    $event_datetime = $_POST['event_datetime'] ?? '';
    $notes = trim($_POST['notes'] ?? '');

    if ($batch_id <= 0 || $event_type === '') {

        $message = "Please select a batch and enter event type.";

    } else {

        if ($event_datetime === '') {
            $event_datetime = date('Y-m-d H:i:s');
        }

        $stmt = $conn->prepare("
            INSERT INTO traceability_events
            (
                batch_id,
                event_type,
                event_reference,
                event_datetime,
                notes
            )
            VALUES (?, ?, ?, ?, ?)
        ");

        if ($stmt) {

            $stmt->bind_param(
                "issss",
                $batch_id,
                $event_type,
                $event_reference,
                $event_datetime,
                $notes
            );

            if ($stmt->execute()) {

                header("Location: index.php?success=1");
                exit;

            } else {

                $message = "Traceability event could not be saved.";
            }

            $stmt->close();

        } else {

            $message = "Database error: " . $conn->error;
        }
    }
}


/* Get batches */

$batches = $conn->query("
    SELECT
        id,
        batch_number,
        quantity_litres,
        status
    FROM batches
    ORDER BY id DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Add Traceability Event</title>

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
}

.header p{
    margin:7px 0 0;
    opacity:.9;
}

.container{
    max-width:800px;
    margin:35px auto;
    padding:0 20px;
}

.card{
    background:white;
    padding:30px;
    border-radius:16px;
    box-shadow:0 6px 25px rgba(0,0,0,.08);
}

.form-group{
    margin-bottom:20px;
}

label{
    display:block;
    font-weight:bold;
    margin-bottom:8px;
}

.required{
    color:red;
}

input,
select,
textarea{
    width:100%;
    padding:12px;
    border:1px solid #d1d5db;
    border-radius:8px;
    font-size:15px;
}

textarea{
    min-height:120px;
    resize:vertical;
}

input:focus,
select:focus,
textarea:focus{
    outline:none;
    border-color:#087f5b;
}

.alert{
    background:#fee2e2;
    color:#991b1b;
    padding:13px;
    border-radius:8px;
    margin-bottom:20px;
}

.buttons{
    display:flex;
    gap:12px;
    margin-top:25px;
}

button,
.cancel{
    padding:12px 22px;
    border:none;
    border-radius:8px;
    font-weight:bold;
    text-decoration:none;
    cursor:pointer;
    font-size:15px;
}

button{
    background:#087f5b;
    color:white;
}

button:hover{
    background:#056b4c;
}

.cancel{
    background:#e5e7eb;
    color:#374151;
}

.info{
    background:#ecfdf5;
    border-left:4px solid #087f5b;
    padding:12px;
    margin-bottom:22px;
    border-radius:5px;
    color:#065f46;
}
</style>

</head>

<body>

<div class="header">

    <h1>🔗 Add Traceability Event</h1>

    <p>
        Record a batch movement or processing event
    </p>

</div>

<div class="container">

<div class="card">

<?php if ($message !== ''): ?>

<div class="alert">
    <?= htmlspecialchars($message) ?>
</div>

<?php endif; ?>


<div class="info">

    Example events:
    Collection, Quality PASS, Chilling,
    Processing, Storage, Dispatch, Company Received.

</div>


<form method="POST">


<div class="form-group">

<label>
    Batch <span class="required">*</span>
</label>

<select name="batch_id" required>

<option value="">
    -- Select Batch --
</option>

<?php if ($batches && $batches->num_rows > 0): ?>

<?php while($batch = $batches->fetch_assoc()): ?>

<option value="<?= $batch['id'] ?>">

    <?= htmlspecialchars($batch['batch_number']) ?>

    -
    <?= htmlspecialchars($batch['quantity_litres']) ?> L

    -
    <?= htmlspecialchars($batch['status']) ?>

</option>

<?php endwhile; ?>

<?php endif; ?>

</select>

</div>


<div class="form-group">

<label>
    Event Type <span class="required">*</span>
</label>

<select name="event_type" required>

<option value="">
    -- Select Event --
</option>

<option value="Collection">
    Collection
</option>

<option value="Quality Testing">
    Quality Testing
</option>

<option value="Quality PASS">
    Quality PASS
</option>

<option value="Quality HOLD">
    Quality HOLD
</option>

<option value="Quality REJECT">
    Quality REJECT
</option>

<option value="Chilling">
    Chilling
</option>

<option value="Processing">
    Processing
</option>

<option value="Storage">
    Storage
</option>

<option value="Dispatch">
    Dispatch
</option>

<option value="Company Received">
    Company Received
</option>

</select>

</div>


<div class="form-group">

<label>
    Event Reference
</label>

<input
    type="text"
    name="event_reference"
    placeholder="Example: COLL-001 / DISP-001"
>

</div>


<div class="form-group">

<label>
    Event Date & Time
</label>

<input
    type="datetime-local"
    name="event_datetime"
>

</div>


<div class="form-group">

<label>
    Notes
</label>

<textarea
    name="notes"
    placeholder="Enter additional details..."
></textarea>

</div>


<div class="buttons">

<button type="submit">
    💾 Save Event
</button>

<a href="index.php" class="cancel">
    Cancel
</a>

</div>

</form>

</div>

</div>

</body>
</html>