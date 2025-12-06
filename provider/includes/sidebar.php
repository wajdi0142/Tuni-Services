<?php
// includes/sidebar.php
// Ce fichier peut être inclus dans toutes les pages du dashboard fournisseur
// Il nécessite que la session soit déjà démarrée et que $_SESSION['user_name'] existe

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/sidebar.css"> <!-- Ton futur fichier CSS -->
</head>
<body>

<!-- ======= SIDEBAR ======= -->
<div class="sidebar">
    <div class="sidebar-brand">
        <i class="fas fa-cog fa-2x text-primary"></i>
        <h2>Tuni-Services Pro</h2>
    </div>

    <ul class="nav-menu">
        <li class="nav-item">
            <a href="../provider/dashboard.php" class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : '' ?>">
                <i class="fas fa-home"></i>
                <span>Tableau de bord</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="../provider/service.php" class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'services.php') ? 'active' : '' ?>">
                <i class="fas fa-concierge-bell"></i>
                <span>Mes annonces</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="../provider/reservations.php" class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'reservations.php') ? 'active' : '' ?>">
                <i class="fas fa-calendar-check"></i>
                <span>Réservations</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="../provider/provider-avis.php" class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'reviews.php') ? 'active' : '' ?>">
                <i class="fas fa-star"></i>
                <span>Avis clients</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="../provider/settings.php" class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'settings.php') ? 'active' : '' ?>">
                <i class="fas fa-cog"></i>
                <span>Paramètres</span>
            </a>
        </li>

        <li class="nav-item mt-auto pt-5">
            <a href="../api/logout.php" class="nav-link">
                <i class="fas fa-sign-out-alt"></i>
                <span>Déconnexion</span>
            </a>
        </li>
    </ul>
</div>

</body>
</html>