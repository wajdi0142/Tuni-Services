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
        Déconnexion
      </a>
    </div>
  </header>

  <div class="container my-5">
    <div class="card p-4">
      <h3>Mes Services</h3>

      <?php
      try {
          $stmt = $pdo->prepare("SELECT * FROM services WHERE user_id = ? ORDER BY created_at DESC");
          $stmt->execute([$_SESSION['user_id']]);
          $services = $stmt->fetchAll();

          if ($services) {
              echo '<div class="row g-3 mt-3">';
              foreach ($services as $s) {
                  $details = nl2br(htmlspecialchars($s['details']));
                  echo '
                  <div class="col-md-6">
                    <div class="card h-100 border-success">
                      <div class="card-body">
                        <h5 class="card-title text-success">' . htmlspecialchars($s['name']) . '</h5>
                        <p class="card-text">
                          <strong>' . ucfirst($s['type']) . '</strong><br>
                          ' . htmlspecialchars($s['gouvernorat']) . ' - ' . htmlspecialchars($s['ville']) . '<br>
                          Prix moyen : ' . number_format($s['prix_moyen'], 2) . ' DT
                        </p>
                        <small class="text-muted">' . $details . '</small>
                      </div>
                    </div>
                  </div>';
              }
              echo '</div>';
          } else {
              echo '<p class="text-muted">Aucun service ajouté pour le moment.</p>';
          }
      } catch (Exception $e) {
          echo '<div class="alert alert-danger">Erreur BDD : ' . $e->getMessage() . '</div>';
      }
      ?>

      <a href="add-service/index.php" class="btn btn-success rounded-pill mt-3">
        Ajouter un service
      </a>
    </div>
  </div>

  <script>
  // Bloque le retour en arrière
  history.pushState(null, null, location.href);
  window.onpopstate = function () {
      history.go(1);
  };
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>