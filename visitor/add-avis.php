<?php
// add-avis.php
require '../db/config.php';
session_start();

// === PROTECTION CSRF (OBLIGATOIRE) ===
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    header('Location: service-details.php?id=' . (int)($_POST['service_id'] ?? 0) . '&error=1');
    exit;
}

// === Récupération sécurisée des données ===
$service_id = (int)($_POST['service_id'] ?? 0);
$client_name = trim($_POST['client_name'] ?? '');
$rating = (int)($_POST['rating'] ?? 0);
$commentaire = trim($_POST['commentaire'] ?? '');
$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

if ($service_id <= 0 || $rating < 1 || $rating > 5 || empty($client_name) || empty($commentaire)) {
    header("Location: service-details.php?id=$service_id&error=1");
    exit;
}

if (strlen($client_name) > 80 || strlen($commentaire) < 10 || strlen($commentaire) > 1000) {
    header("Location: service-details.php?id=$service_id&error=1");
    exit;
}

// Nettoyage XSS
$commentaire = htmlspecialchars($commentaire, ENT_QUOTES, 'UTF-8');

// Anti-spam : max 3 avis par jour / IP / service
$today = date('Y-m-d');
$stmt = $pdo->prepare("SELECT COUNT(*) FROM avis WHERE ip_address = ? AND service_id = ? AND DATE(created_at) = ?");
$stmt->execute([$ip, $service_id, $today]);
if ($stmt->fetchColumn() >= 3) {
    header("Location: service-details.php?id=$service_id&error=1");
    exit;
}

// Insertion
try {
    $stmt = $pdo->prepare("
        INSERT INTO avis (service_id, client_name, rating, commentaire, verified, ip_address, created_at) 
        VALUES (?, ?, ?, ?, 0, ?, NOW())
    ");
    $stmt->execute([$service_id, $client_name, $rating, $commentaire, $ip]);

    // Redirection avec succès
    header("Location: service-details.php?id=$service_id&success=1");
    exit;

} catch (Exception $e) {
    error_log("Erreur ajout avis : " . $e->getMessage());
    header("Location: service-details.php?id=$service_id&error=1");
    exit;
}