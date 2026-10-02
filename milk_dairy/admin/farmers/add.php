<?php
session_start();
require_once '../../database/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../../login.php');
    exit;
}

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $farmer_code = trim($_POST['farmer_code'] ?? '');
    $name        = trim($_POST['name'] ?? '');
    $phone       = trim($_POST['phone'] ?? '');
    $address     = trim($_POST['address'] ?? '');

    if ($farmer_code === '' || $name === '' || $phone === '' || $address === '') {

        $message = 'Please fill all required fields.';
        $message_type = 'error';

    } else {

        $stmt = $conn->prepare(
            "INSERT INTO farmers (farmer_code, name, phone, address)
             VALUES (?, ?, ?, ?)"
        );

        if ($stmt) {

            $stmt->bind_param(
                "ssss",
                $farmer_code,
                $name,
                $phone,
                $address
            );

            if ($stmt->execute()) {

                $message = 'Farmer added successfully!';
                $message_type = 'success';

                $farmer_code = '';
                $name = '';
                $phone = '';
                $address = '';

            } else {

                $message = 'Unable to add farmer. Farmer ID may already exist.';
                $message_type = 'error';
            }

            $stmt->close();

        } else {

            $message = 'Database error: ' . $conn->error;
            $message_type = 'error';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Add Farmer | Smart Dairy Management System</title>

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            min-height: 100vh;
            background: #f4f7fb;
            color: #1f2937;
        }

        .header {
            background: linear-gradient(135deg, #087f5b, #0ca678);
            color: white;
            padding: 22px 35px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.12);
        }

        .header h1 {
            font-size: 25px;
        }

        .header p {
            margin-top: 5px;
            font-size: 14px;
            opacity: 0.9;
        }

        .back-btn {
            text-decoration: none;
            color: white;
            border: 1px solid rgba(255,255,255,0.5);
            padding: 10px 18px;
            border-radius: 8px;
            transition: 0.3s;
        }

        .back-btn:hover {
            background: white;
            color: #087f5b;
        }

        .container {
            max-width: 850px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .form-card {
            background: white;
            border-radius: 16px;
            padding: 35px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
        }

        .form-title {
            margin-bottom: 25px;
        }

        .form-title h2 {
            font-size: 25px;
            color: #111827;
        }

        .form-title p {
            color: #6b7280;
            margin-top: 6px;
        }

        .message {
            padding: 13px 16px;
            border-radius: 8px;
            margin-bottom: 22px;
            font-size: 14px;
        }

        .success {
            background: #dcfce7;
            color: #166534;
        }

        .error {
            background: #fee2e2;
            color: #991b1b;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 22px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group.full {
            grid-column: 1 / -1;
        }

        label {
            margin-bottom: 8px;
            font-weight: bold;
            font-size: 14px;
            color: #374151;
        }

        input,
        textarea {
            width: 100%;
            padding: 13px 14px;
            border: 1px solid #d1d5db;
            border-radius: 9px;
            font-size: 14px;
            outline: none;
            transition: 0.2s;
        }

        input:focus,
        textarea:focus {
            border-color: #087f5b;
            box-shadow: 0 0 0 3px rgba(8,127,91,0.1);
        }

        textarea {
            min-height: 110px;
            resize: vertical;
        }

        .required {
            color: #dc2626;
        }

        .buttons {
            margin-top: 30px;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }

        .btn {
            padding: 12px 22px;
            border-radius: 9px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            font-weight: bold;
            font-size: 14px;
        }

        .cancel-btn {
            background: #e5e7eb;
            color: #374151;
        }

        .save-btn {
            background: #087f5b;
            color: white;
            box-shadow: 0 5px 12px rgba(8,127,91,0.2);
        }

        .save-btn:hover {
            background: #066b4d;
        }

        @media (max-width: 650px) {

            .header {
                padding: 18px;
            }

            .header h1 {
                font-size: 20px;
            }

            .container {
                margin: 25px auto;
            }

            .form-card {
                padding: 22px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .form-group.full {
                grid-column: auto;
            }

            .buttons {
                flex-direction: column;
            }

            .btn {
                text-align: center;
                width: 100%;
            }
        }

    </style>
</head>

<body>

<div class="header">

    <div>
        <h1>🥛 Smart Dairy Management</h1>
        <p>Farmer Management Module</p>
    </div>

    <a href="index.php" class="back-btn">
        ← Farmers
    </a>

</div>


<div class="container">

    <div class="form-card">

        <div class="form-title">

            <h2>👨‍🌾 Add New Farmer</h2>

            <p>
                Register a new farmer in the dairy management system.
            </p>

        </div>


        <?php if ($message !== ''): ?>

            <div class="message <?= $message_type ?>">
                <?= htmlspecialchars($message) ?>
            </div>

        <?php endif; ?>


        <form method="POST">

            <div class="form-grid">

                <div class="form-group">

                    <label>
                        Farmer ID <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        name="farmer_code"
                        placeholder="Example: FRM001"
                        value="<?= htmlspecialchars($farmer_code ?? '') ?>"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>
                        Farmer Name <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        name="name"
                        placeholder="Enter farmer name"
                        value="<?= htmlspecialchars($name ?? '') ?>"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>
                        Phone Number <span class="required">*</span>
                    </label>

                    <input
                        type="tel"
                        name="phone"
                        placeholder="Enter phone number"
                        value="<?= htmlspecialchars($phone ?? '') ?>"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>
                        Address <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        name="address"
                        placeholder="Enter farmer address"
                        value="<?= htmlspecialchars($address ?? '') ?>"
                        required
                    >

                </div>


            </div>


            <div class="buttons">

                <a href="index.php" class="btn cancel-btn">
                    Cancel
                </a>

                <button type="submit" class="btn save-btn">
                    ✓ Save Farmer
                </button>

            </div>

        </form>

    </div>

</div>

</body>
</html>