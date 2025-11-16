<?php
// C:\xampp\htdocs\tuni-services\api\register.php
header('Content-Type: application/json');
require '../db/config.php';

$data = json_decode(file_get_contents('php://input'), true) ?? $_POST;

$email = filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL);
$password = $data['password'] ?? '';
$name = trim($data['name'] ?? '');
$phone = preg_replace('/\D/', '', $data['phone'] ?? '');

if (!$email || strlen($password) < 6 || !$name) {
    echo json_encode(['success' => false, 'message' => 'Données invalides']);
    exit;
}

$hashed = password_hash($password, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare("INSERT INTO users (email, password, name, phone) VALUES (?, ?, ?, ?)");
    $stmt->execute([$email, $hashed, $name, $phone]);
    echo json_encode(['success' => true, 'message' => 'Compte créé !']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Email déjà utilisé']);
}
?>