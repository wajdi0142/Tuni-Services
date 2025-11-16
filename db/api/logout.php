<?php
// api/logout.php
session_start();
session_destroy();
header('Location: ../provider/login.html');
exit;
?>