<?php
session_start();
require_once 'database/db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {

        $message = 'Please enter email and password.';

    } else {

        // Get user without status condition
        $stmt = $conn->prepare(
            "SELECT id, name, email, password, role
             FROM users
             WHERE email = ?
             LIMIT 1"
        );

        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {

            unset($user['password']);

            $_SESSION['user'] = $user;

            if ($user['role'] === 'admin') {

                header("Location: admin/dashboard.php");

            } elseif ($user['role'] === 'staff') {

                header("Location: staff/dashboard.php");

            } else {

                header("Location: customer/dashboard.php");
            }

            exit;
        }

        $message = 'Invalid email or password.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - Smart Dairy</title>

    <link rel="stylesheet" href="css/style.css">
</head>

<body>

<div class="login-page">

    <form class="login-card" method="post">

        <div class="icon">🥛</div>

        <h2>Welcome Back</h2>

        <p>Smart Dairy Management System</p>

        <?php if ($message): ?>

            <div class="alert">
                <?= htmlspecialchars($message) ?>
            </div>

        <?php endif; ?>

        <label>Email</label>

        <input
            type="email"
            name="email"
            placeholder="Enter email"
            required
        >

        <label>Password</label>

        <input
            type="password"
            name="password"
            placeholder="Enter password"
            required
        >

        <button type="submit" class="btn full">
            Login
        </button>

    </form>

</div>

</body>
</html>