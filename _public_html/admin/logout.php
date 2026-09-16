<?php
session_start();
unset($_SESSION['auth']);
unset($_SESSION['admin_auth']);
session_destroy();
header("Location: index.php");
exit;
?>