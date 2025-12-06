<?php
require '../db/config.php';
session_start();

// Génération du token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$id = $_GET['id'] ?? 0;
if (!is_numeric($id) || $id <= 0) {
    die('<h2 class="text-center text-danger mt-5">Service introuvable</h2>');
}

// Récupérer le service
$stmt = $pdo->prepare("
    SELECT s.*, u.name AS provider_name, u.phone 
    FROM services s 
    JOIN users u ON s.user_id = u.id 
    WHERE s.id = ? AND s.status = 'validé'
");
$stmt->execute([$id]);
$service = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$service) {
    die('<h2 class="text-center text-muted mt-5">Service non disponible</h2>');
}

$whatsapp = $service['phone'] ?? '21655555555';

// RÉCUPÉRER LES AVIS + RÉPONSES DU FOURNISSEUR
$avis_stmt = $pdo->prepare("
    SELECT client_name, rating, commentaire, reponse_fournisseur, verified, created_at 
    FROM avis 
    WHERE service_id = ? AND rating > 0 
    ORDER BY created_at DESC
");
$avis_stmt->execute([$id]);
$avis = $avis_stmt->fetchAll(PDO::FETCH_ASSOC);

$moyenne = $avis ? array_sum(array_column($avis, 'rating')) / count($avis) : 0;
$total_avis = count($avis);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($service['name']) ?> - Tuni-Services</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/visitor-services.css">
    <style>
        body { background:#f8f9ff; font-family:system-ui,-apple-system,sans-serif; }
        .hero-img { height:480px; object-fit:cover; border-radius:24px; box-shadow:0 20px 50px rgba(0,0,0,0.22); transition:0.4s; }
        .hero-img:hover { transform:scale(1.02); }
        .info-card { background:white; border-radius:24px; padding:2rem; box-shadow:0 15px 40px rgba(0,0,0,0.12); height:100%; }
        .price-big { font-size:3.8rem; font-weight:900; background:linear-gradient(135deg,#198754,#20c997); -webkit-background-clip:text; -webkit-text-fill-color:transparent; }
        .badge-type { font-size:1rem; padding:10px 24px; border-radius:50px; background:rgba(13,110,253,0.95); }
        .btn-wa-big { background:#25d366; color:white; font-weight:700; padding:16px 32px; border-radius:50px; font-size:1.15rem; box-shadow:0 10px 30px rgba(37,211,102,0.4); }
        .btn-wa-big:hover { background:#128c7e; transform:translateY(-4px); }

        /* Avis */
        .avis-card { background:white; border-radius:18px; padding:1.5rem; box-shadow:0 8px 25px rgba(0,0,0,0.08); transition:0.3s; border-left:5px solid #0d6efd; }
        .avis-card:hover { transform:translateY(-6px); box-shadow:0 15px 35px rgba(0,0,0,0.12); }
        .avis-photo { width:52px; height:52px; border-radius:50%; object-fit:cover; border:3px solid #e3f2fd; }
        .verified-badge { background:#0d6efd; color:white; font-size:0.7rem; padding:4px 10px; border-radius:20px; }

        /* Réponse du fournisseur */
        .reponse-pro {
            background: #f0f8ff;
            border-left: 5px solid #2196f3;
            padding: 12px 16px;
            border-radius: 0 12px 12px 0;
            margin-top: 15px;
            font-size: 0.92rem;
        }

        .map-container { height:420px; border-radius:24px; overflow:hidden; box-shadow:0 15px 40px rgba(0,0,0,0.12); }

        .alert-success { background: linear-gradient(135deg, #d4edda, #a3e4b8) !important; border: none; font-weight: 600; color: #155724; }
        .alert-danger { background: linear-gradient(135deg, #f8d7da, #f1b0b7) !important; border: none; font-weight: 600; color: #721c24; }

        @media (max-width:992px) { .hero-img { height:360px; } .price-big { font-size:3.2rem; } }
        @media (max-width:576px) { .hero-img { height:280px; border-radius:18px; } .price-big { font-size:2.8rem; } }
    </style>
</head>
<body>

<!-- Header + Notifications -->
<header class="bg-white shadow-sm py-4 sticky-top">
    <div class="container d-flex justify-content-between align-items-center">
        <a href="services.php" class="d-flex align-items-center gap-3 text-decoration-none">
            <img src="../assets/img/logo.png" height="48" alt="Logo">
            <h1 class="h4 mb-0 fw-bold text-primary">Tuni-Services</h1>
        </a>
        <a href="services.php" class="btn btn-outline-primary rounded-pill">Retour</a>
    </div>
</header>

<?php if (isset($_GET['success'])): ?>
<div class="position-fixed top-0 end-0 p-3" style="z-index: 1100;">
    <div class="alert alert-success alert-dismissible fade show shadow-lg rounded-4 border-0" role="alert">
        Merci ! Votre avis a été publié avec succès.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
</div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
<div class="position-fixed top-0 end-0 p-3" style="z-index: 1100;">
    <div class="alert alert-danger alert-dismissible fade show shadow-lg rounded-4 border-0" role="alert">
        Une erreur est survenue. Veuillez réessayer.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
</div>
<?php endif; ?>

<div class="container my-5">
    <!-- Photo + Infos (inchangé) -->
    <div class="row g-5">
        <div class="col-lg-8">
            <?php if (!empty($service['image']) && file_exists("../uploads/services/".$service['image'])): ?>
                <img src="../uploads/services/<?= htmlspecialchars($service['image']) ?>" class="w-100 hero-img" alt="<?= $service['name'] ?>">
            <?php else: ?>
                <div class="hero-img d-flex align-items-center justify-content-center text-white" style="background:linear-gradient(135deg,#667eea,#764ba2)">
                    <i class="fas fa-store fa-7x opacity-70"></i>
                </div>
            <?php endif; ?>
        </div>
        <div class="col-lg-4">
            <div class="info-card d-flex flex-column">
                <span class="badge-type d-inline-block mb-3"><?= ucfirst(str_replace('-', ' ', $service['type'])) ?></span>
                <h1 class="h3 fw-bold mb-3"><?= htmlspecialchars($service['name']) ?></h1>
                <div class="d-flex align-items-center mb-3">
                    <?php for($i=1;$i<=5;$i++): ?>
                        <i class="fas fa-star <?= $i <= round($moyenne) ? 'text-warning' : 'text-muted' ?> fa-lg"></i>
                    <?php endfor; ?>
                    <span class="ms-2 fw-bold"><?= number_format($moyenne,1) ?></span>
                    <span class="ms-1 text-muted">(<?= $total_avis ?> avis)</span>
                </div>
                <div class="price-big mb-4"><?= number_format($service['prix_moyen'],0) ?> <small style="font-size:1.8rem">DT</small></div>
                <div class="mb-4">
                    <p class="mb-2"><i class="fas fa-user-tie text-primary me-2"></i> <strong><?= htmlspecialchars($service['provider_name']) ?></strong></p>
                    <p><i class="fas fa-map-marker-alt text-danger me-2"></i> <?= $service['gouvernorat'] ?> – <?= $service['ville'] ?></p>
                </div>
                <div class="mt-auto">
                    <a href="https://wa.me/<?= $whatsapp ?>?text=Bonjour <?= urlencode($service['provider_name']) ?>, je suis intéressé par <?= urlencode($service['name']) ?>"
                       target="_blank" class="btn btn-wa-big w-100 d-flex align-items-center justify-content-center gap-3 mb-3">
                        Contacter sur WhatsApp
                    </a>
                    <a href="tel:<?= $whatsapp ?>" class="btn btn-outline-success w-100 rounded-pill py-3">
                        Appeler
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Description (inchangé) -->
    <div class="row mt-4">
        <div class="col-lg-8">
            <div class="bg-white rounded-4 shadow p-4 p-md-5">
                <h3 class="fw-bold mb-4">Description</h3>
                <p class="text-muted lh-lg"><?= nl2br(htmlspecialchars($service['details'])) ?></p>
                <?php if (!empty($service['menu'])): ?>
                    <hr class="my-5">
                    <h4 class="fw-bold mb-4">Menu / Prestations</h4>
                    <p class="text-muted lh-lg"><?= nl2br(htmlspecialchars($service['menu'])) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- SECTION AVIS CLIENTS AVEC RÉPONSE -->
    <?php if ($total_avis > 0): ?>
    <div class="mt-5">
        <h2 class="text-center fw-bold mb-5 display-5">Avis clients (<?= $total_avis ?>)</h2>
        <div class="row g-4">
            <?php foreach ($avis as $a): ?>
            <div class="col-md-6 col-lg-4">
                <div class="avis-card">
                    <div class="d-flex align-items-start gap-3">
                        <img src="https://i.pravatar.cc/150?u=<?= md5($a['client_name']) ?>" class="avis-photo" alt="<?= htmlspecialchars($a['client_name']) ?>">
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-bold mb-0"><?= htmlspecialchars($a['client_name']) ?></h6>
                                <?php if($a['verified']): ?>
                                    <span class="verified-badge">Vérifié</span>
                                <?php endif; ?>
                            </div>
                            <div class="mb-2">
                                <?php for($i=1;$i<=5;$i++): ?>
                                    <i class="fas fa-star <?= $i<=$a['rating'] ? 'text-warning' : 'text-muted' ?>"></i>
                                <?php endfor; ?>
                                <small class="text-muted ms-2"><?= date('d/m/Y', strtotime($a['created_at'])) ?></small>
                            </div>
                            <p class="text-muted small lh-lg"><?= nl2br(htmlspecialchars($a['commentaire'])) ?></p>

                            <!-- RÉPONSE DU FOURNISSEUR -->
                            <?php if (!empty($a['reponse_fournisseur'])): ?>
                            <div class="reponse-pro">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <i class="fas fa-reply text-primary"></i>
                                    <strong class="text-primary"><?= htmlspecialchars($service['provider_name']) ?> a répondu :</strong>
                                </div>
                                <p class="mb-0 small text-muted"><?= nl2br(htmlspecialchars($a['reponse_fournisseur'])) ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php else: ?>
    <!-- Aucun avis -->
    <div class="text-center py-5 my-5">
        <div class="bg-white rounded-4 shadow-sm p-5 mx-auto" style="max-width: 560px;">
            <div class="mb-4"><i class="fas fa-comment-dots fa-4x text-muted opacity-75"></i></div>
            <h3 class="fw-bold text-dark mb-3">Aucun avis pour le moment</h3>
            <p class="text-muted fs-5 mb-4">Soyez le premier à laisser un avis !</p>
            <button type="button" class="btn btn-primary btn-lg rounded-pill px-5 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAvis">
                Laisser un avis
            </button>
        </div>
    </div>
    <?php endif; ?>

    <!-- Carte + Modal (inchangés) -->
    <div class="mt-5">
        <h3 class="text-center fw-bold mb-4">Localisation</h3>
        <div class="map-container">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d5000!2d10.1817!3d36.839!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2z<?= urlencode($service['ville'].', '.$service['gouvernorat']) ?>!5e0!3m2!1sfr!2stn!4v123" 
                    width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
        </div>
    </div>
</div>

<!-- Modal avis (inchangé) -->
<div class="modal fade" id="modalAvis" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content rounded-4 shadow-lg border-0">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold fs-3">Votre avis compte !</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body pt-3">
        <form action="add-avis.php" method="POST">
          <input type="hidden" name="service_id" value="<?= $id ?>">
          <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
          <div class="mb-4 text-center">
            <label class="form-label fw-bold">Votre note</label>
            <div class="star-rating fs-1">
                <i class="far fa-star" data-rating="1"></i>
                <i class="far fa-star" data-rating="2"></i>
                <i class="far fa-star" data-rating="3"></i>
                <i class="far fa-star" data-rating="4"></i>
                <i class="far fa-star" data-rating="5"></i>
                <input type="hidden" name="rating" id="rating-value" required>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Votre nom</label>
            <input type="text" name="client_name" class="form-control form-control-lg rounded-pill" placeholder="Ex: Amina Ben Ali" required maxlength="80">
          </div>
          <div class="mb-4">
            <label class="form-label fw-bold">Votre commentaire</label>
            <textarea name="commentaire" rows="4" class="form-control rounded-3" placeholder="Partagez votre expérience..." required minlength="10" maxlength="1000"></textarea>
          </div>
          <div class="text-center">
            <button type="submit" class="btn btn-success btn-lg rounded-pill px-5 shadow">
                Publier mon avis
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
// Étoiles interactives
document.querySelectorAll('.star-rating i').forEach(star => {
    star.addEventListener('click', function() {
        const rating = this.getAttribute('data-rating');
        document.getElementById('rating-value').value = rating;
        document.querySelectorAll('.star-rating i').forEach((s, index) => {
            if (index < rating) {
                s.classList.remove('far'); s.classList.add('fas'); s.style.color = '#ffc107';
            } else {
                s.classList.remove('fas'); s.classList.add('far'); s.style.color = '#ccc';
            }
        });
    });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>