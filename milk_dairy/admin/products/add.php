<?php
session_start();
require_once '../../database/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $unit = trim($_POST['unit'] ?? 'litre');
    $quantity = floatval($_POST['quantity'] ?? 0);
    $price = floatval($_POST['price'] ?? 0);
    $status = $_POST['status'] ?? 'active';

    if ($name === '') {
        $message = "Please enter product name.";
    } elseif ($quantity < 0 || $price < 0) {
        $message = "Quantity and price cannot be negative.";
    } else {

        $stmt = $conn->prepare(
            "INSERT INTO products
            (name, unit, quantity, price, status)
            VALUES (?, ?, ?, ?, ?)"
        );

        if ($stmt) {

            $stmt->bind_param(
                "ssdds",
                $name,
                $unit,
                $quantity,
                $price,
                $status
            );

            if ($stmt->execute()) {
                header("Location: index.php?success=1");
                exit;
            } else {
                $message = "Product could not be saved.";
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

<title>Add Product - Smart Dairy</title>

<style>
*{box-sizing:border-box;font-family:Arial,sans-serif}

body{
    margin:0;
    background:#f0fdf4;
    color:#1f2937
}

.header{
    background:linear-gradient(135deg,#087f5b,#12b886);
    color:white;
    padding:25px 35px
}

.header h1{margin:0}

.header p{
    margin:7px 0 0;
    opacity:.9
}

.container{
    max-width:750px;
    margin:35px auto;
    padding:0 20px
}

.card{
    background:white;
    padding:30px;
    border-radius:16px;
    box-shadow:0 6px 25px rgba(0,0,0,.08)
}

.form-group{
    margin-bottom:20px
}

label{
    display:block;
    font-weight:bold;
    margin-bottom:8px
}

.required{color:red}

input,select{
    width:100%;
    padding:12px;
    border:1px solid #d1d5db;
    border-radius:8px;
    font-size:15px
}

input:focus,select:focus{
    outline:none;
    border-color:#087f5b
}

.alert{
    background:#fee2e2;
    color:#991b1b;
    padding:13px;
    border-radius:8px;
    margin-bottom:20px
}

.buttons{
    display:flex;
    gap:12px;
    margin-top:25px
}

button,.cancel{
    padding:12px 22px;
    border:none;
    border-radius:8px;
    font-weight:bold;
    text-decoration:none;
    cursor:pointer;
    font-size:15px
}

button{
    background:#087f5b;
    color:white
}

button:hover{
    background:#056b4c
}

.cancel{
    background:#e5e7eb;
    color:#374151
}
</style>

</head>

<body>

<div class="header">
    <h1>🥛 Add Product</h1>
    <p>Add a new dairy product</p>
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
        Product Name <span class="required">*</span>
    </label>

    <input
        type="text"
        name="name"
        placeholder="Example: Fresh Milk"
        required
    >
</div>

<div class="form-group">
    <label>Unit</label>

    <select name="unit">
        <option value="litre">Litre</option>
        <option value="kg">Kg</option>
        <option value="packet">Packet</option>
        <option value="piece">Piece</option>
    </select>
</div>

<div class="form-group">
    <label>Quantity</label>

    <input
        type="number"
        name="quantity"
        step="0.01"
        min="0"
        value="0"
        placeholder="Enter quantity"
    >
</div>

<div class="form-group">
    <label>Price</label>

    <input
        type="number"
        name="price"
        step="0.01"
        min="0"
        value="0"
        placeholder="Enter price"
    >
</div>

<div class="form-group">
    <label>Status</label>

    <select name="status">
        <option value="active">Active</option>
        <option value="inactive">Inactive</option>
    </select>
</div>

<div class="buttons">

    <button type="submit">
        💾 Save Product
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