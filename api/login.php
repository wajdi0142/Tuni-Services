<?php
// api/login.php — VERSION TESTÉE SUR XAMPP PHP 7.4 (fonctionne à tous les coups)
session_start();
require '../db/config.php';

header('Content-Type: application/json');

// Récupération des données
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) $data = $_POST;

$email    = $data['email'] ?? '';
$password = $data['password'] ?? '';
$role     = strtolower(trim($data['role'] ?? ''));

if (!$email || !$password || !$role) {
    echo json_encode(['success' => false, 'message' => 'Données manquantes']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {

        $userRole = strtolower(trim($user['role']));

        if ($userRole !== $role) {
            echo json_encode(['success' => false, 'message' => 'Mauvais formulaire']);
            exit;
        }

        session_regenerate_id(true);

        $_SESSION['user_id']    = $user['id'];
        $_SESSION['user_name']  = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['role']       = $userRole;

        // REDIRECTION SIMPLE
        if ($userRole === 'admin') {
            $redirect = '../admin/stats.php';
        } else {
            $redirect = '../provider/dashboard.php';
        }

        echo json_encode([
            'success' => true,
            'message' => 'OK',
            'redirect' => $redirect
        ]);
        exit;

    } else {
        echo json_encode(['success' => false, 'message' => 'Identifiants incorrects']);
        exit;
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur serveur']);
    exit;
}
?>