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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
  <link rel="stylesheet" href="../assets/css/style.css" />
</head>
<body>

  <header>
    <div class="container d-flex justify-content-between align-items-center">
      <div class="d-flex align-items-center gap-3">
        <img src="../assets/img/logo.png" alt="Logo" class="logo" height="48" />
        <h1 class="h4 mb-0 fw-bold">Tuni-Services</h1>
      </div>
      <a href="../index.html" class="btn btn-outline-primary btn-sm">
        Accueil
      </a>
    </div>
  </header>

  <div class="filter-bar">
    <div class="container">
      <form class="row g-3 align-items-center" method="GET">
        <div class="col-md-3">
          <input type="text" name="q" class="form-control" placeholder="Rechercher..." value="<?= htmlspecialchars($search) ?>" />
        </div>
        <div class="col-md-2">
          <select name="type" class="form-select">
            <option value="">Type</option>
            <option value="fast-food" <?= $type==='fast-food'?'selected':'' ?>>Fast Food</option>
            <option value="taxi" <?= $type==='taxi'?'selected':'' ?>>Taxi</option>
            <!-- Ajoute les autres types -->
          </select>
        </div>
        <div class="col-md-2">
          <select name="gouvernorat" class="form-select">
            <option value="">Gouvernorat</option>
            <option value="Tunis" <?= $gouvernorat==='Tunis'?'selected':'' ?>>Tunis</option>
            <!-- Ajoute les autres -->
          </select>
        </div>
        <div class="col-md-2">
          <input type="number" name="prix_max" class="form-control" placeholder="Prix max" value="<?= htmlspecialchars($prix_max) ?>" />
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn btn-primary w-100 rounded-pill">
            Filtrer
          </button>
        </div>
      </form>
    </div>
  </div>

  <div class="container my-5">
    <h2 class="text-center mb-5 display-5 fw-bold">
      Services Disponibles
    </h2>

    <?php if (isset($error)): ?>
      <div class="alert alert-danger text-center"><?= htmlspecialchars($error) ?></div>
    <?php elseif ($services): ?>
      <div class="row g-4">
        <?php foreach ($services as $s): ?>
          <div class="col-md-6 col-lg-4">
            <div class="service-card card h-100">
              <div class="card-body d-flex flex-column">
                <h5 class="card-title text-primary">
                  <?= htmlspecialchars($s['name']) ?>
                </h5>
                <p class="card-text flex-grow-1">
                  <strong><?= htmlspecialchars($s['gouvernorat']) ?></strong> - <?= htmlspecialchars($s['ville']) ?><br>
                  Prix moyen : <strong><?= number_format($s['prix_moyen'], 2) ?> DT</strong>
                </p>
                <small class="text-muted"><?= nl2br(htmlspecialchars($s['details'])) ?></small>
                <div class="mt-3">
                  <span class="badge bg-primary"><?= ucfirst($s['type']) ?></span>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="text-center py-5">
        <i class="fas fa-search fa-3x text-muted mb-3"></i>
        <p>Aucun service trouvé.</p>
      </div>
    <?php endif; ?>

    <div class="text-center mt-5">
      <a href="../index.html" class="btn btn-outline-primary btn-lg rounded-pill">
        Retour à l'accueil
      </a>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>