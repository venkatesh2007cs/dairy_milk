<?php
session_start();
require_once '../../database/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $machine_name = trim($_POST['machine_name'] ?? '');
    $machine_code = trim($_POST['machine_code'] ?? '');
    $status = $_POST['status'] ?? 'idle';

    $last_maintenance = !empty($_POST['last_maintenance'])
        ? $_POST['last_maintenance']
        : null;

    $next_maintenance = !empty($_POST['next_maintenance'])
        ? $_POST['next_maintenance']
        : null;


    if ($machine_name === '') {

        $message = "Please enter machine name.";

    } else {

        $stmt = $conn->prepare("
            INSERT INTO machines
            (
                machine_name,
                machine_code,
                status,
                last_maintenance,
                next_maintenance
            )
            VALUES (?, ?, ?, ?, ?)
        ");

        if ($stmt) {

            $stmt->bind_param(
                "sssss",
                $machine_name,
                $machine_code,
                $status,
                $last_maintenance,
                $next_maintenance
            );

            if ($stmt->execute()) {

                header("Location: index.php?success=1");
                exit;

            } else {

                if ($conn->errno == 1062) {
                    $message = "Machine code already exists.";
                } else {
                    $message = "Machine could not be saved.";
                }
            }

            $stmt->close();

        } else {

            $message = "Database error: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Add Machine - Smart Dairy</title>

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
    max-width:750px;
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
select{
    width:100%;
    padding:12px;
    border:1px solid #d1d5db;
    border-radius:8px;
    font-size:15px;
}

input:focus,
select:focus{
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
</style>

</head>

<body>

<div class="header">

    <h1>🔧 Add Machine</h1>

    <p>
        Register a dairy processing machine
    </p>

</div>

<div class="container">

<div class="card">

<?php if ($message !== ''): ?>

<div class="alert">
    <?= htmlspecialchars($message) ?>
</div>

<?php endif; ?>


<form method="POST">


<div class="form-group">

<label>
    Machine Name <span class="required">*</span>
</label>

<input
    type="text"
    name="machine_name"
    placeholder="Example: Pasteurization Machine"
    required
>

</div>


<div class="form-group">

<label>
    Machine Code
</label>

<input
    type="text"
    name="machine_code"
    placeholder="Example: MACH-001"
>

</div>


<div class="form-group">

<label>
    Status
</label>

<select name="status">

<option value="idle">
    Idle
</option>

<option value="running">
    Running
</option>

<option value="maintenance">
    Maintenance
</option>

<option value="inactive">
    Inactive
</option>

</select>

</div>


<div class="form-group">

<label>
    Last Maintenance
</label>

<input
    type="date"
    name="last_maintenance"
>

</div>


<div class="form-group">

<label>
    Next Maintenance
</label>

<input
    type="date"
    name="next_maintenance"
>

</div>


<div class="buttons">

<button type="submit">
    💾 Save Machine
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