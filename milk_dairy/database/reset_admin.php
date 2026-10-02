<?php
require_once "db.php";

$new_password = "Admin@123";
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = 1");
$stmt->bind_param("s", $hashed_password);

if ($stmt->execute()) {
    echo "Admin password reset successfully!";
} else {
    echo "Error: " . $stmt->error;
}
?>