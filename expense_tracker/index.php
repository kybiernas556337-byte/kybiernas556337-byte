<?php
// index.php - Entry point, redirect based on login state

session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
} else {
    header("Location: login.php");
}
exit();
?>
