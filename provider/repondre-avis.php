<?php
// repondre-avis.php
require '../db/config.php';
session_start();

// 1. Vérifier que c'est bien un fournisseur connecté
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'fournisseur') {
    header('Location: provider-avis.php');
    exit;
}

// 2. Vérifier que la requête est en POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: provider-avis.php');
    exit;
}

// 3. Récupérer et nettoyer les données
$avis_id = (int)($_POST['avis_id'] ?? 0);
$reponse = trim($_POST['reponse'] ?? '');

if ($avis_id <= 0 || empty($reponse)) {
    header('Location: provider-avis.php?error=1');
    exit;
}

// Optionnel : limiter la longueur de la réponse (évite les abus)
if (strlen($reponse) > 1000) {
    $reponse = substr($reponse, 0, 1000);
}

// 4. Mettre à jour la réponse (seulement si l'avis appartient au fournisseur)
try {
    $stmt = $pdo->prepare("
        UPDATE avis 
        SET reponse_fournisseur = ?, reponse_date = NOW() 
        WHERE id = ? 
        AND service_id IN (SELECT id FROM services WHERE user_id = ?)
        AND reponse_fournisseur IS NULL  -- empêche de modifier une réponse déjà donnée
    ");
    $stmt->execute([$reponse, $avis_id, $_SESSION['user_id']]);

    // Si aucune ligne modifiée → soit l'avis n'existe pas, soit il n'appartient pas au fournisseur, soit déjà répondu
    if ($stmt->rowCount() === 0) {
        header('Location: provider-avis.php?error=2');
        exit;
    }

} catch (Exception $e) {
    error_log("Erreur réponse avis : " . $e->getMessage());
    header('Location: provider-avis.php?error=3');
    exit;
}

// 5. Redirection avec succès
header('Location: provider-avis.php?repondu=1');
exit;
?>