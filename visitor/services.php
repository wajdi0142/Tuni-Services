<?php
require '../db/config.php';

try {
    // Filtres
    $search = trim($_GET['q'] ?? '');
    $type = $_GET['type'] ?? '';
    $gouvernorat = $_GET['gouvernorat'] ?? '';
    $prix_max = $_GET['prix_max'] ?? '';

    // Construire la requête
    $sql = "SELECT * FROM services WHERE status = 'validé'";
    $params = [];

    if ($search) {
        $sql .= " AND (name LIKE ? OR details LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    if ($type) {
        $sql .= " AND type = ?";
        $params[] = $type;
    }
    if ($gouvernorat) {
        $sql .= " AND gouvernorat = ?";
        $params[] = $gouvernorat;
    }
    if ($prix_max !== '') {
        $sql .= " AND prix_moyen <= ?";
        $params[] = $prix_max;
    }

    $sql .= " ORDER BY RAND() LIMIT 12";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $services = $stmt->fetchAll();

} catch (Exception $e) {
    $services = [];
    $error = "Erreur : " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Services en Tunisie - Tuni-Services</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link rel="stylesheet" href="../assets/css/style.css" />
  <style>
    .service-card {
      transition: transform 0.3s, box-shadow 0.3s;
      background: rgba(255,255,255,0.1);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(16,185,129,0.3);
    }
    .service-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 20px 40px rgba(16,185,129,0.2);
    }
    .filter-bar {
      background: rgba(0,0,0,0.7);
      backdrop-filter: blur(12px);
      border-bottom: 1px solid #10b981;
    }
  </style>
</head>
<body>
  <div class="video-overlay"></div>
  <video autoplay muted loop id="bg-video" playsinline>
    <source src="../assets/video/earth.mp4" type="video/mp4" />
  </video>

  <!-- BARRE DE RECHERCHE & FILTRES -->
  <div class="filter-bar sticky-top py-3">
    <div class="container">
      <form method="GET" class="row g-2 align-items-center">
        <div class="col-md-4">
          <div class="input-group">
            <span class="input-group-text"><i class="fas fa-search"></i></span>
            <input type="text" name="q" class="form-control" placeholder="Rechercher..." value="<?= htmlspecialchars($search) ?>" />
          </div>
        </div>
        <div class="col-md-2">
          <select name="type" class="form-select">
            <option value="">Type</option>
            <option value="fast-food" <?= $type==='fast-food'?'selected':'' ?>>Fast Food</option>
            <option value="taxi" <?= $type==='taxi'?'selected':'' ?>>Taxi</option>
            <option value="transport" <?= $type==='transport'?'selected':'' ?>>Transport</option>
          </select>
        </div>
        <div class="col-md-2">
          <select name="gouvernorat" class="form-select">
            <option value="">Gouvernorat</option>
            <option value="Tunis" <?= $gouvernorat==='Tunis'?'selected':'' ?>>Tunis</option>
            <option value="Sfax" <?= $gouvernorat==='Sfax'?'selected':'' ?>>Sfax</option>
            <option value="Sousse" <?= $gouvernorat==='Sousse'?'selected':'' ?>>Sousse</option>
          </select>
        </div>
        <div class="col-md-2">
          <input type="number" name="prix_max" class="form-control" placeholder="Prix max" value="<?= $prix_max ?>" />
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn btn-success w-100 rounded-pill">
            Filtrer
          </button>
        </div>
      </form>
    </div>
  </div>

  <div class="container my-5">
    <h2 class="text-white text-center mb-5 display-5 fw-bold">
      Services Disponibles
    </h2>

    <?php if (isset($error)): ?>
      <div class="alert alert-danger text-center"><?= $error ?></div>
    <?php elseif ($services): ?>
      <div class="row g-4">
        <?php foreach ($services as $s): ?>
          <div class="col-md-6 col-lg-4">
            <div class="card service-card h-100 text-white">
              <div class="card-body d-flex flex-column">
                <h5 class="card-title text-success">
                  <?= htmlspecialchars($s['name']) ?>
                </h5>
                <p class="card-text flex-grow-1">
                  <strong><?= htmlspecialchars($s['gouvernorat']) ?></strong> - <?= htmlspecialchars($s['ville']) ?><br>
                  Prix moyen : <strong><?= number_format($s['prix_moyen'], 2) ?> DT</strong>
                </p>
                <small class="text-muted"><?= nl2br(htmlspecialchars($s['details'])) ?></small>
                <div class="mt-3">
                  <span class="badge bg-success"><?= ucfirst($s['type']) ?></span>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="text-center py-5">
        <i class="fas fa-search fa-3x text-muted mb-3"></i>
        <p class="text-white">Aucun service trouvé.</p>
      </div>
    <?php endif; ?>

    <div class="text-center mt-5">
      <a href="../index.html" class="btn btn-outline-light btn-lg rounded-pill">
        Retour à l'accueil
      </a>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>