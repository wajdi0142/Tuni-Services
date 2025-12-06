<?php
require '../db/config.php';

$search      = trim($_GET['q'] ?? '');
$type        = $_GET['type'] ?? '';
$gouvernorat = $_GET['gouvernorat'] ?? '';
$prix_max    = $_GET['prix_max'] ?? '';

try {
    $sql = "SELECT s.*, u.name AS provider_name 
            FROM services s 
            JOIN users u ON s.user_id = u.id 
            WHERE s.status = 'validé'";

    $params = [];

    if ($search !== '') {
        $sql .= " AND (s.name LIKE ? OR s.details LIKE ? OR s.menu LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    if ($type !== '') {
        $sql .= " AND s.type = ?";
        $params[] = $type;
    }
    if ($gouvernorat !== '') {
        $sql .= " AND s.gouvernorat = ?";
        $params[] = $gouvernorat;
    }
    if ($prix_max !== '') {
        $sql .= " AND s.prix_moyen <= ?";
        $params[] = (float)$prix_max;
    }

    $sql .= " ORDER BY s.created_at DESC LIMIT 30";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $services = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Services en Tunisie - Tuni-Services</title>

    <!-- Bootstrap 5 + Font Awesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

    <!-- Ton CSS ultra-pro (le meilleur qu’on a fait) -->
    <link rel="stylesheet" href="../assets/css/visitor-services.css">

    <style>
        body { background: #f8f9fa; }
        .page-title { font-size: 2.8rem; font-weight: 800; background: linear-gradient(90deg, #0d6efd, #6610f2); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    </style>
</head>
<body>

<!-- Header -->
<header class="py-4 bg-white shadow-sm mb-5">
    <div class="container d-flex justify-content-between align-items-center">
        <a href="../index.html" class="d-flex align-items-center gap-3 text-decoration-none">
            <img src="../assets/img/logo.png" height="52" alt="Tuni-Services">
            <h1 class="h3 mb-0 fw-bold text-primary">Tuni-Services</h1>
        </a>
        <a href="../index.html" class="btn btn-outline-primary rounded-pill px-4">Accueil</a>
    </div>
</header>

<div class="container">

    <!-- Titre + Filtres -->
    <div class="text-center mb-5">
        <h1 class="page-title mb-4">Tous les Services en Tunisie</h1>
        <p class="text-muted fs-5">Trouvez le meilleur prestataire près de chez vous</p>
    </div>

    <!-- Barre de filtres -->
    <div class="bg-white rounded-4 shadow-sm p-4 mb-5">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4 col-lg-3">
                <input type="text" name="q" class="form-control form-control-lg" placeholder="Rechercher..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-3 col-lg-2">
                <select name="type" class="form-select form-select-lg">
                    <option value="">Type de service</option>
                    <option value="fast-food"    <?php if($type==='fast-food') echo 'selected'; ?>>Fast Food</option>
                    <option value="taxi"         <?php if($type==='taxi') echo 'selected'; ?>>Taxi</option>
                    <option value="plombier"     <?php if($type==='plombier') echo 'selected'; ?>>Plombier</option>
                    <option value="electricien"  <?php if($type==='electricien') echo 'selected'; ?>>Électricien</option>
                    <option value="coiffure"     <?php if($type==='coiffure') echo 'selected'; ?>>Coiffure</option>
                    <!-- Ajoute les autres types ici -->
                </select>
            </div>
            <div class="col-md-3 col-lg-2">
                <select name="gouvernorat" class="form-select form-select-lg">
                    <option value="">Gouvernorat</option>
                    <option value="Tunis"     <?php if($gouvernorat==='Tunis') echo 'selected'; ?>>Tunis</option>
                    <option value="Ariana"    <?php if($gouvernorat==='Ariana') echo 'selected'; ?>>Ariana</option>
                    <option value="Sfax"      <?php if($gouvernorat==='Sfax') echo 'selected'; ?>>Sfax</option>
                    <!-- etc. -->
                </select>
            </div>
            <div class="col-md-2">
                <input type="number" name="prix_max" class="form-control form-control-lg" placeholder="Prix max" value="<?= htmlspecialchars($prix_max) ?>">
            </div>
            <div class="col-12 col-md-2 col-lg-1">
                <button type="submit" class="btn btn-primary btn-lg w-100 rounded-pill">Filtrer</button>
            </div>
        </form>
    </div>

    <!-- Liste des cartes -->
    <?php if (!empty($services)): ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 g-lg-5">
            <?php foreach ($services as $s): ?>
                <div class="col">
                    <div class="service-premium-card position-relative">

                        <!-- Image -->
                        <?php if (!empty($s['image']) && file_exists("../uploads/services/".$s['image'])): ?>
                            <img src="../uploads/services/<?= htmlspecialchars($s['image']) ?>" alt="<?= htmlspecialchars($s['name']) ?>">
                        <?php else: ?>
                            <div class="no-image">
                                <i class="fas fa-store"></i>
                            </div>
                        <?php endif; ?>

                        <!-- Badges -->
                        <span class="badge-type"><?= ucfirst(str_replace('-', ' ', $s['type'])) ?></span>
                        <span class="badge-price"><?= number_format($s['prix_moyen'], 0) ?> DT</span>

                        <!-- Contenu -->
                        <div class="card-body">
                            <h5 class="fw-bold text-primary mb-2"><?= htmlspecialchars($s['name']) ?></h5>

                            <p class="info-line mb-2">
                                <i class="fas fa-user-tie text-primary me-2"></i>
                                <strong><?= htmlspecialchars($s['provider_name'] ?? 'Prestataire') ?></strong>
                            </p>

                            <p class="info-line mb-3">
                                <i class="fas fa-map-marker-alt text-danger me-2"></i>
                                <?= htmlspecialchars($s['gouvernorat']) ?> – <?= htmlspecialchars($s['ville']) ?>
                            </p>

                            <div class="description">
                                <?= nl2br(htmlspecialchars($s['details'] ?? 'Aucune description disponible.')) ?>
                            </div>

                            <div class="card-actions">
                                <a href="https://wa.me/216XXXXXXXX?text=Bonjour,%20je%20suis%20intéressé%20par%20<?= urlencode($s['name']) ?>%20à%20<?= urlencode($s['ville']) ?>"
                                   target="_blank" class="btn btn-whatsapp">
                                    <i class="fab fa-whatsapp me-2"></i>Contact
                                </a>
                                <a href="service-details.php?id=<?= $s['id'] ?>" class="btn btn-details">
                                    <i class="fas fa-arrow-right fa-lg"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-5 my-5">
            <i class="fas fa-search fa-5x text-muted mb-4"></i>
            <h3 class="text-muted">Aucun service trouvé</h3>
            <p class="text-muted">Essayez avec d'autres filtres</p>
        </div>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>