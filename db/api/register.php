<?php
// api/register.php
header('Content-Type: application/json');
require '../db/config.php';

// Accepte JSON (fetch) ou POST (formulaire)
$data = json_decode(file_get_contents('php://input'), true);
if (!$data || !isset($data['email'])) {
    $data = $_POST;
}

$email = filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL);
$password = $data['password'] ?? '';
$name = htmlspecialchars(trim($data['name'] ?? ''));
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
    // Affiche l'erreur exacte (uniquement en dev)
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur serveur : ' . $e->getMessage()]);
}
?>