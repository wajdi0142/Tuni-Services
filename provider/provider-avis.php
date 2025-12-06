<?php
require '../db/config.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'fournisseur') {
    header('Location: ../login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Récupérer les avis
$stmt = $pdo->prepare("
    SELECT a.*, s.name AS service_name 
    FROM avis a
    JOIN services s ON a.service_id = s.id
    WHERE s.user_id = ? 
    ORDER BY a.created_at DESC
");
$stmt->execute([$user_id]);
$avis = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Stats
$moyenne_stmt = $pdo->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as total FROM avis a JOIN services s ON a.service_id = s.id WHERE s.user_id = ?");
$moyenne_stmt->execute([$user_id]);
$stats = $moyenne_stmt->fetch();
$moyenne = round($stats['avg_rating'] ?? 0, 1);
$total   = $stats['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mes Avis Clients - Tuni-Services Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        body { background:#f8f9ff; min-height:100vh; }
        .sidebar { position:fixed; top:0; left:0; width:280px; height:100vh; background:linear-gradient(180deg,#1a1a2e,#16213e); color:#cfd8dc; padding-top:20px; z-index:1000; overflow-y:auto; }
        .sidebar a { color:#cfd8dc; padding:15px 25px; display:flex; align-items:center; gap:15px; text-decoration:none; transition:0.3s; }
        .sidebar a:hover, .sidebar a.active { background:rgba(255,215,0,0.2); color:#ffd700; border-left:4px solid #ffd700; }
        .main-content { margin-left:280px; padding:30px; }
        .stats-card { background:white; border-radius:24px; padding:40px; text-align:center; box-shadow:0 10px 40px rgba(0,0,0,0.08); }
        .avis-card { background:white; border-radius:20px; padding:20px; box-shadow:0 8px 25px rgba(0,0,0,0.08); margin-bottom:20px; }
        .avis-card:hover { transform:translateY(-5px); box-shadow:0 15px 35px rgba(0,0,0,0.12); }
        .reponse { background:#e3f2fd; border-left:4px solid #2196f3; padding:12px; margin-top:15px; border-radius:0 8px 8px 0; }
    </style>
</head>
<body>
<?php include '../provider/includes/sidebar.php'; ?>
<!-- CONTENU -->
<div class="main-content">
    <h2 class="text-center mb-5 display-5 fw-bold">Mes Avis Clients</h2>

    <div class="stats-card mb-5">
        <h1 class="display-3 fw-bold text-success"><?= $moyenne ?>/5</h1>
        <div class="my-3">
            <?php for($i=1;$i<=5;$i++): ?>
                <i class="fas fa-star <?= $i <= round($moyenne) ? 'text-warning' : 'text-muted' ?> fa-3x"></i>
            <?php endfor; ?>
        </div>
        <p class="text-muted fs-4">sur <?= $total ?> avis reçus</p>
    </div>

    <div class="row">
        <?php if (empty($avis)): ?>
            <div class="text-center py-5">
                <i class="fas fa-comment-slash fa-5x text-muted mb-4"></i>
                <h3>Aucun avis pour le moment</h3>
            </div>
        <?php else: foreach ($avis as $a): ?>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="avis-card">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <img src="https://i.pravatar.cc/150?u=<?= md5($a['client_name']) ?>" width="50" class="rounded-circle border border-primary">
                        <div>
                            <h6 class="fw-bold mb-0"><?= htmlspecialchars($a['client_name']) ?></h6>
                            <small class="text-muted"><?= htmlspecialchars($a['service_name']) ?></small>
                        </div>
                    </div>

                    <div class="mb-2">
                        <?php for($i=1;$i<=5;$i++): ?>
                            <i class="fas fa-star <?= $i<=$a['rating'] ? 'text-warning' : 'text-muted' ?>"></i>
                        <?php endfor; ?>
                        <small class="text-muted ms-2"><?= date('d/m/Y à H:i', strtotime($a['created_at'])) ?></small>
                    </div>

                    <p class="text-muted"><?= nl2br(htmlspecialchars($a['commentaire'])) ?></p>

                    <!-- Réponse du fournisseur -->
                    <?php if (!empty($a['reponse_fournisseur'])): ?>
                        <div class="reponse">
                            <strong><i class="fas fa-reply"></i> Votre réponse :</strong><br>
                            <?= nl2br(htmlspecialchars($a['reponse_fournisseur'])) ?>
                        </div>
                    <?php else: ?>
                        <form action="repondre-avis.php" method="POST" class="mt-3">
                            <input type="hidden" name="avis_id" value="<?= $a['id'] ?>">
                            <textarea name="reponse" class="form-control form-control-sm" rows="2" placeholder="Répondre publiquement à cet avis..." required></textarea>
                            <button type="submit" class="btn btn-primary btn-sm mt-2">Envoyer la réponse</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>