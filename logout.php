<?php
session_start();
session_unset();
session_destroy();
header("Location: /My_Project/auth.php");
exit;
?>