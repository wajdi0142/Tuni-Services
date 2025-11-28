<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.html');
    exit;
}
require '../../db/config.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $gouvernorat = $_POST['gouvernorat'] ?? '';
    $ville = trim($_POST['ville'] ?? '');
    $prix = floatval($_POST['prix'] ?? 0);
    $menu1 = trim($_POST['menu1'] ?? '');
    $menu2 = trim($_POST['menu2'] ?? '');
    $menu3 = trim($_POST['menu3'] ?? '');
    $menu4 = trim($_POST['menu4'] ?? '');
    $menu5 = trim($_POST['menu5'] ?? '');
    $menu6 = trim($_POST['menu6'] ?? '');

    if ($name && $gouvernorat && $ville && $prix > 0) {
        $details = implode("\n", array_filter([$menu1, $menu2, $menu3, $menu4, $menu5, $menu6]));

        try {
            $stmt = $pdo->prepare("INSERT INTO services (user_id, type, name, gouvernorat, ville, prix_moyen, details) VALUES (?, 'fast-food', ?, ?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $name, $gouvernorat, $ville, $prix, $details]);
            $message = '<div class="alert alert-success">Service ajouté avec succès !</div>';
        } catch (Exception $e) {
            $message = '<div class="alert alert-danger">Erreur : ' . $e->getMessage() . '</div>';
        }
    } else {
        $message = '<div class="alert alert-warning">Veuillez remplir tous les champs obligatoires.</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <title>Ajouter Fast Food - Tuni-Services</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
  <link rel="stylesheet" href="../../assets/css/style.css" />
</head>
<body>

  <header>
    <div class="container d-flex justify-content-between align-items-center py-3">
      <div class="d-flex align-items-center gap-3">
        <img src="../../assets/img/logo.png" alt="Logo" class="logo" height="48" />
        <h1 class="h5 mb-0 fw-bold">Ajouter Fast Food</h1>
      </div>
      <a href="../dashboard.php" class="btn btn-outline-primary btn-sm">
        Retour
      </a>
    </div>
  </header>

  <div class="container my-5">
    <div class="card card-premium shadow-lg p-5" style="max-width: 900px; margin: 0 auto;">
      <?= $message ?>
      <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
          <label class="form-label">Nom du restaurant *</label>
          <input type="text" name="name" class="form-control" required />
        </div>

        <div class="row mb-3">
          <div class="col-md-6">
            <label class="form-label">Gouvernorat *</label>
            <select name="gouvernorat" class="form-select" required>
              <option value="">Choisir...</option>
              <option>Tunis</option>
              <option>Ariana</option>
              <option>Ben Arous</option>
              <option>Sfax</option>
              <option>Sousse</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Ville *</label>
            <input type="text" name="ville" class="form-control" required />
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">Prix moyen (DT) *</label>
          <input type="number" name="prix" step="0.1" class="form-control" required />
        </div>

        <div class="mb-3">
          <label class="form-label">Menu (6 plats max)</label>
          <input type="text" name="menu1" class="form-control mb-2" placeholder="Burger + Frites" />
          <input type="text" name="menu2" class="form-control mb-2" placeholder="Pizza Margherita" />
          <input type="text" name="menu3" class="form-control mb-2" placeholder="Salade César" />
          <input type="text" name="menu4" class="form-control mb-2" placeholder="Tacos" />
          <input type="text" name="menu5" class="form-control mb-2" placeholder="Milkshake" />
          <input type="text" name="menu6" class="form-control mb-2" placeholder="Glace" />
        </div>

        <div class="d-grid">
          <button type="submit" class="btn btn-primary btn-lg rounded-pill">
            <i class="fas fa-plus"></i> Ajouter le service
          </button>
        </div>
      </form>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>