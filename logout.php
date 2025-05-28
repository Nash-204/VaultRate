<?php
require_once 'includes/auth.php';

$auth = new Auth();
$auth->logout();

header("Location: pages/login.php");
exit();
?>