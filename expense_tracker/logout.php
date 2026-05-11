<?php
// logout.php

session_start();
session_destroy();

// Clear remember me cookie
setcookie('remember_email', '', time() - 3600, '/');

header("Location: login.php");
exit();
?>
