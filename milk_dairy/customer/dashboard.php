<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'customer') {
    header("Location: ../login.php");
    exit;
}
?>
<h1>Customer Dashboard</h1>
<p>Customer module will be developed next.</p>
<a href="../logout.php">Logout</a>
