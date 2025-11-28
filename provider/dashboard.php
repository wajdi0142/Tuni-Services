<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}
require '../db/config.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <title>Tableau de bord - Tuni-Services</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
  <link rel="stylesheet" href="../assets/css/style.css" />
</head>
<body>

  <div class="d-flex">
    <!-- Sidebar améliorée -->
    <div class="sidebar col-lg-3 col-xl-2 p-0">
      <div class="text-center mb-5">
        <img src="../assets/img/logo.png" height="60" class="mb-3" />
        <h5>Bienvenue</h5>
      </div>
      <nav>
        <a href="#" class="active"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a>
        <a href="#"><i class="fas fa-bullhorn"></i> Mes annonces</a>
        <a href="#"><i class="fas fa-calendar"></i> Réservations</a>
        <a href="#"><i class="fas fa-star"></i> Avis clients</a>
        <a href="#"><i class="fas fa-cog"></i> Paramètres</a>
        <hr class="mx-3 opacity-20">
        <a href="../api/logout.php" class="text-danger"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
      </nav>
    </div>

    <!-- Contenu -->
    <div class="flex-grow-1 p-4 p-lg-5">
      <h2 class="fw-bold mb-5">Tableau de bord</h2>

      <div class="row g-4 mb-5">
        <div class="col-md-4">
          <div class="card card-premium">
            <i class="fas fa-eye fa-2x text-primary mb-3"></i>
            <div class="stat-number">1 247</div>
            <p class="text-muted">Vues ce mois-ci</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card card-premium">
            <i class="fas fa-phone fa-2x text-primary mb-3"></i>
            <div class="stat-number">89</div>
            <p class="text-muted">Appels reçus</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card card-premium">
            <i class="fas fa-star fa-2x text-primary mb-3"></i>
            <div class="stat-number">4.8</div>
            <p class="text-muted">Note moyenne</p>
          </div>
        </div>
      </div>

      <div class="card card-premium p-4">
        <h4 class="mb-4">Dernières réservations</h4>
        <div class="text-center py-5 text-muted">
          <i class="fas fa-inbox fa-4x mb-3 opacity-50"></i>
          <p>Aucune réservation pour le moment</p>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>