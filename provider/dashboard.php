<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <title>Tableau de bord - Tuni-Services</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link rel="stylesheet" href="../assets/css/style.css" />
</head>
<body>

  <div class="video-overlay"></div>
  <video autoplay muted loop id="bg-video" playsinline>
    <source src="../assets/video/earth.mp4" type="video/mp4" />
  </video>

  <header>
    <div class="container d-flex justify-content-between align-items-center">
      <div class="d-flex align-items-center gap-3">
        <img src="../assets/img/logo.png" alt="Logo" class="logo" height="48" />
        <h1 class="h4 mb-0 fw-bold text-white">Bonjour, <?= htmlspecialchars($_SESSION['user_name'] ?? 'Fournisseur') ?></h1>
      </div>
      <a href="../api/logout.php" class="btn btn-outline-light btn-sm">
        <i class="fas fa-sign-out-alt"></i> Déconnexion
      </a>
    </div>
  </header>

  <div class="container my-5">
    <div class="card p-4">
      <h3>Mes Services</h3>
      <p class="text-muted">Aucun service ajouté pour le moment.</p>
      <a href="add-service/fast-food.html" class="btn btn-success rounded-pill">
        <i class="fas fa-plus"></i> Ajouter un service
      </a>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>