<?php
// includes/provider-avis.php   → version anti-"Accès refusé"

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si l'utilisateur n'est pas connecté → on arrête tout proprement
if (empty($_SESSION['user_id']) || !is_numeric($_SESSION['user_id'])) {
    echo '
    <div class="alert alert-warning rounded-4 shadow-sm text-center py-4">
        <i class="fas fa-exclamation-triangle fa-2x mb-3"></i><br>
        Vous devez être connecté pour voir vos avis.
    </div>';
    return;
}

$user_id = (int)$_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("
        SELECT a.*, s.name AS service_name, s.type AS service_type
        FROM avis a
        JOIN services s ON a.service_id = s.id
        WHERE s.user_id = ?
        ORDER BY a.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $avis = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $moyenne = $avis ? round(array_sum(array_column($avis, 'rating')) / count($avis), 1) : 0;

} catch (Exception $e) {
    echo '<div class="alert alert-danger">Erreur de chargement des avis.</div>';
    return;
}
?>

<!-- SECTION AVIS (même design que avant, mais maintenant infaillible) -->
<div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-5">
    <div class="card-header bg-primary text-white py-4" style="background:linear-gradient(135deg,#0d6efd,#6610f2)!important;">
        <h3 class="mb-0 fw-bold d-flex align-items-center justify-content-between">
            <span><i class="fas fa-comments me-3"></i> Avis clients reçus</span>
            <span class="badge bg-white text-primary fs-5">
                <?= $moyenne ?> <i class="fas fa-star text-warning"></i> (<?= count($avis) ?>)
            </span>
        </h3>
    </div>

    <div class="card-body p-4">
        <?php if ($avis): ?>
            <div class="row g-4">
                <?php foreach ($avis as $a): ?>
                    <div class="col-lg-6 col-xxl-4">
                        <div class="p-4 bg-white rounded-4 border-start border-5 border-primary shadow-sm position-relative hover-shadow">
                            <?php if ($a['verified']): ?>
                                <span class="position-absolute top-0 end-0 mt-2 me-3 badge bg-success rounded-pill">
                                    <i class="fas fa-check"></i> Vérifié
                                </span>
                            <?php endif; ?>

                            <div class="d-flex align-items-start gap-3 mb-3">
                                <img src="https://i.pravatar.cc/80?u=<?= md5($a['client_name']) ?>" 
                                     class="rounded-circle shadow-sm" width="56" height="56" alt="">
                                <div>
                                    <h6 class="fw-bold mb-1"><?= htmlspecialchars($a['client_name']) ?></h6>
                                    <small class="text-muted"><?= date('d/m/Y à H:i', strtotime($a['created_at'])) ?></small>
                                </div>
                            </div>

                            <div class="mb-3">
                                <?php for($i=1;$i<=5;$i++): ?>
                                    <i class="fas fa-star <?= $i<=$a['rating']?'text-warning':'text-muted' ?> fa-lg"></i>
                                <?php endfor; ?>
                                <strong class="ms-2"><?= $a['rating'] ?>.0</strong>
                            </div>

                            <p class="mb-0 lh-lg fst-italic text-dark">
                                "<?= nl2br(htmlspecialchars($a['commentaire'])) ?>"
                            </p>

                            <small class="text-muted d-block mt-3">
                                <i class="fas fa-concierge-bell me-1"></i>
                                <strong><?= htmlspecialchars($a['service_name']) ?></strong>
                            </small>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-comment-slash fa-5x text-muted opacity-50 mb-4"></i>
                <h5 class="text-muted">Aucun avis pour le moment</h5>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.hover-shadow{transition:all .3s}.hover-shadow:hover{transform:translateY(-8px);box-shadow:0 20px 40px rgba(0,0,0,.15)!important}
</style>