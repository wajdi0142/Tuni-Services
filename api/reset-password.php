<?php
// api/reset-password.php → VERSION 100% PARFAITE
session_start();
header('Content-Type: application/json');
require '../db/config.php';

$data = json_decode(file_get_contents("php://input"), true);
$token = trim($data['token'] ?? '');
$password = $data['password'] ?? '';

if (empty($token) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Données manquantes']);
    exit;
}

if (strlen($password) < 8) {
    echo json_encode(['success' => false, 'message' => '8 caractères minimum requis']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT user_id FROM password_resets WHERE token = ? AND expires_at > NOW() AND used = 0");
    $stmt->execute([$token]);
    $row = $stmt->fetch();

    if (!$row) {
        echo json_encode(['success' => false, 'message' => 'Lien invalide, expiré ou déjà utilisé']);
        exit;
    }

    $hashed = password_hash($password, PASSWORD_DEFAULT);

    $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")
        ->execute([$hashed, $row['user_id']]);

    $pdo->prepare("UPDATE password_resets SET used = 1, used_at = NOW() WHERE token = ?")
        ->execute([$token]);

    $pdo->prepare("DELETE FROM password_resets WHERE user_id = ? AND token != ?")
        ->execute([$row['user_id'], $token]);

    echo json_encode([
        'success' => true,
        'message' => 'Mot de passe changé avec succès !'
    ]);

} catch (Exception $e) {
    error_log("Reset password error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erreur serveur. Réessayez plus tard.']);
}
?>