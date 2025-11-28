<?php
session_start();
if (!isset($_SESSION['user_id'])) exit;

require '../../db/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $gouvernorat = $_POST['gouvernorat'] ?? '';
    $ville = trim($_POST['ville'] ?? '');
    $prix = floatval($_POST['prix'] ?? 0);
    $details = trim($_POST['details'] ?? '');

    // Upload photo
    $photo = '';
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
        $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $photo = 'uploads/' . uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['photo']['tmp_name'], '../../' . $photo);
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO services (user_id, type, name, gouvernorat, ville, prix_moyen, details, photo, status) 
                               VALUES (?, 'custom', ?, ?, ?, ?, ?, ?, 'brouillon')");
        $stmt->execute([$_SESSION['user_id'], $name, $gouvernorat, $ville, $prix, $details, $photo]);
        header('Location: ../dashboard.php?success=1');
    } catch (Exception $e) {
        echo "Erreur : " . $e->getMessage();
    }
}
?>
<script>
// Bloque le retour en arrière
history.pushState(null, null, location.href);
window.onpopstate = function () {
    history.go(1);
};
</script>