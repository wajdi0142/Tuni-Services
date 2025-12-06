<?php
require 'db/config.php';
$hash = password_hash('admin123', PASSWORD_DEFAULT);
echo "Hash pour admin123 :<br><br>";
echo $hash;
?>