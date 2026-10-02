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

    $customer_code = trim($_POST['customer_code'] ?? '');
    $name          = trim($_POST['name'] ?? '');
    $customer_type = $_POST['customer_type'] ?? 'company';
    $phone         = trim($_POST['phone'] ?? '');
    $email         = trim($_POST['email'] ?? '');
    $address       = trim($_POST['address'] ?? '');
    $status        = $_POST['status'] ?? 'active';

    if ($customer_code === '' || $name === '') {

        $message = 'Customer Code and Name are required.';
        $message_type = 'error';

    } else {

        $stmt = $conn->prepare("
            INSERT INTO customers
            (customer_code, name, customer_type, phone, email, address, status)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        if ($stmt) {

            $stmt->bind_param(
                "sssssss",
                $customer_code,
                $name,
                $customer_type,
                $phone,
                $email,
                $address,
                $status
            );

            if ($stmt->execute()) {

                header("Location: index.php?success=1");
                exit;

            } else {

                if ($stmt->errno == 1062) {
                    $message = 'Customer Code already exists.';
                } else {
                    $message = 'Database Error: ' . $stmt->error;
                }

                $message_type = 'error';
            }

            $stmt->close();

        } else {

            $message = 'Database Error: ' . $conn->error;
            $message_type = 'error';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Add Customer - Smart Dairy</title>

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f4f8f6;
    color: #263238;
}

.header {
    background: linear-gradient(135deg, #087f5b, #12b886);
    color: white;
    padding: 24px 30px;
}

.header h1 {
    margin: 0;
    font-size: 28px;
}

.header p {
    margin: 7px 0 0;
    opacity: .9;
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
    box-shadow: 0 6px 22px rgba(0,0,0,.08);
}

.card h2 {
    margin-top: 0;
    color: #087f5b;
    margin-bottom: 25px;
}

.form-group {
    margin-bottom: 18px;
}

label {
    display: block;
    margin-bottom: 7px;
    font-weight: bold;
}

.required {
    color: #d00000;
}

input,
select,
textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #ccd8d3;
    border-radius: 8px;
    font-size: 15px;
    background: white;
}

textarea {
    min-height: 100px;
    resize: vertical;
}

input:focus,
select:focus,
textarea:focus {
    outline: none;
    border-color: #087f5b;
}

.buttons {
    display: flex;
    gap: 12px;
    margin-top: 25px;
}

button,
.back {
    padding: 12px 20px;
    border-radius: 8px;
    font-size: 15px;
    font-weight: bold;
    cursor: pointer;
    text-decoration: none;
}

button {
    border: none;
    background: #087f5b;
    color: white;
}

button:hover {
    background: #066b4d;
}

.back {
    background: #e9ecef;
    color: #333;
}

.message {
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.error {
    background: #ffe3e3;
    color: #c92a2a;
}

</style>

</head>

<body>

<div class="header">

    <h1>🏢 Add Customer</h1>

    <p>
        Register a new company or retail customer
    </p>

</div>

<div class="container">

<div class="card">

<h2>Customer Details</h2>

<?php if ($message !== ''): ?>

<div class="message <?= htmlspecialchars($message_type) ?>">
    <?= htmlspecialchars($message) ?>
</div>

<?php endif; ?>

<form method="POST">

    <div class="form-group">

        <label>
            Customer Code <span class="required">*</span>
        </label>

        <input
            type="text"
            name="customer_code"
            placeholder="Example: CUST-001"
            required
        >

    </div>


    <div class="form-group">

        <label>
            Customer / Company Name
            <span class="required">*</span>
        </label>

        <input
            type="text"
            name="name"
            placeholder="Example: ABC Dairy Pvt Ltd"
            required
        >

    </div>


    <div class="form-group">

        <label>Customer Type</label>

        <select name="customer_type">

            <option value="company">
                Company
            </option>

            <option value="retail">
                Retail
            </option>

        </select>

    </div>


    <div class="form-group">

        <label>Phone</label>

        <input
            type="text"
            name="phone"
            placeholder="Enter phone number"
        >

    </div>


    <div class="form-group">

        <label>Email</label>

        <input
            type="email"
            name="email"
            placeholder="example@gmail.com"
        >

    </div>


    <div class="form-group">

        <label>Address</label>

        <textarea
            name="address"
            placeholder="Enter customer address"
        ></textarea>

    </div>


    <div class="form-group">

        <label>Status</label>

        <select name="status">

            <option value="active">
                Active
            </option>

            <option value="inactive">
                Inactive
            </option>

        </select>

    </div>


    <div class="buttons">

        <button type="submit">
            💾 Save Customer
        </button>

        <a href="index.php" class="back">
            ← Back
        </a>

    </div>

</form>

</div>

</div>

</body>
</html>