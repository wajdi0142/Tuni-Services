<?php
require 'db/config.php';

$email = 'admin@tuniservices.com';
$password = 'admin123';

$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password'])) {
    echo "MOT DE PASSE OK<br>";
    echo "Rôle dans la BDD : '" . $user['role'] . "'<br>";
    echo "Rôle après trim/strtolower : '" . strtolower(trim($user['role'])) . "'<br>";
} else {
    echo "MOT DE PASSE OU EMAIL INCORRECT";
}
?>