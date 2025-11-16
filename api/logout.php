<?php
session_start();

// Détruit TOUTE la session
session_unset();
session_destroy();
session_write_close();
setcookie(session_name(), '', 0, '/');

// Empêche le cache
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

// Redirige vers login
header('Location: ../provider/login.html');
exit;
?>