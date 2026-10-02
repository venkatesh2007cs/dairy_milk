<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'staff') {
    header("Location: ../login.php");
    exit;
}
?>
<h1>Staff Dashboard</h1>
<p>Staff module will be developed next.</p>
<a href="../logout.php">Logout</a>
