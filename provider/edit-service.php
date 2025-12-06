<?php
require '../db/config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$user_id    = $_SESSION['user_id'];
$service_id = (int)($_GET['id'] ?? 0);

if ($service_id <= 0) {
    header('Location: services.php');
    exit;
}

// Créer le dossier d'upload s'il n'existe pas
$upload_dir = '../uploads/services/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Récupérer le service
$stmt = $pdo->prepare("SELECT * FROM services WHERE id = ? AND user_id = ?");
$stmt->execute([$service_id, $user_id]);
$service = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$service) {
    header('Location: services.php');
    exit;
}

$message = '';

if ($_POST) {
    $name        = trim($_POST['name'] ?? '');
    $type        = $_POST['type'] ?? '';
    $gouvernorat = trim($_POST['gouvernorat'] ?? '');
    $ville       = trim($_POST['ville'] ?? '');
    $prix_moyen  = (float)($_POST['prix_moyen'] ?? 0);
    $details     = trim($_POST['details'] ?? '');
    $menu        = trim($_POST['menu'] ?? '');

    // Validation
    if (empty($name) || empty($type) || empty($gouvernorat) || empty($ville) || $prix_moyen <= 0 || empty($details)) {
        $message = '<div class="alert alert-danger">Tous les champs obligatoires sont requis.</div>';
    } else {
        $image_name = $service['image']; // garder l’ancienne image

        // Nouvelle image ?
        if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

            if (in_array($ext, $allowed) && $_FILES['image']['size'] <= 5*1024*1024) {
                if ($image_name && file_exists($upload_dir . $image_name)) {
                    @unlink($upload_dir . $image_name);
                }
                $image_name = 'service_' . time() . '_' . rand(1000,9999) . '.' . $ext;
                move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image_name);
            } else {
                $message = '<div class="alert alert-warning">Image invalide (JPG/PNG/WEBP ≤ 5 Mo)</div>';
            }
        }

        if (empty($message)) {
            try {
                // VIRGULE AJOUTÉE ICI → c’était la cause de l’erreur !
                $sql = "UPDATE services SET 
                        name = ?, type = ?, gouvernorat = ?, ville = ?, 
                        prix_moyen = ?, details = ?, menu = ?, image = ?,
                        status = 'en-attente', updated_at = NOW()
                        WHERE id = ? AND user_id = ?";

                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $name, $type, $gouvernorat, $ville,
                    $prix_moyen, $details, $menu, $image_name,
                    $service_id, $user_id
                ]);

                $message = '<div class="alert alert-success">
                    Modifications enregistrées avec succès ! Votre service est en attente de revalidation.
                </div>';

                // Rafraîchir l’affichage
                $service['name']        = $name;
                $service['type']        = $type;
                $service['gouvernorat'] = $gouvernorat;
                $service['ville']       = $ville;
                $service['prix_moyen']  = $prix_moyen;
                $service['details']     = $details;
                $service['menu']        = $menu;
                $service['image']       = $image_name;

            } catch (Exception $e) {
                $message = '<div class="alert alert-danger">Erreur base de données : ' . $e->getMessage() . '</div>';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Modifier - <?= htmlspecialchars($service['name'] ?? 'Service') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        body { background:#f8f9ff; }
        .form-card { max-width: 1000px; margin: 2rem auto;padding-left:170px; }
        .preview-img { max-height: 320px; object-fit: cover; border-radius: 18px; margin-top: 1rem; display:none; }
        .current-photo { border: 4px dashed #0d6efd; padding: 15px; border-radius: 20px; background: #f0f4ff; }
        .status-badge { font-size: 1rem; padding: 10px 24px; border-radius: 50px; }
    </style>
</head>
<body>

<?php include '../provider/includes/sidebar.php'; ?>

<div class="container py-12">
    <div class="form-card">
        <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="card-header bg-primary text-white text-center py-4">
                <h2 class="mb-0 fw-bold">Modifier mon service</h2>
            </div>

            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <span class="status-badge <?= $service['status']=='validé' ? 'bg-success' : 'bg-warning text-dark' ?>">
                        <?= $service['status']=='validé' ? 'Publié' : 'En attente / Refusé' ?>
                    </span>
                </div>

                <?= $message ?>

                <!-- Le reste du formulaire reste identique (je le garde pour être complet) -->
                <form method="POST" enctype="multipart/form-data">
                    <div class="row g-5">
                        <div class="col-lg-5 text-center">
                            <label class="form-label fw-bold mb-3">Photo actuelle</label>
                            <div class="current-photo mb-4">
                                <?php if (!empty($service['image']) && file_exists("../uploads/services/".$service['image'])): ?>
                                    <img src="../uploads/services/<?= htmlspecialchars($service['image']) ?>" class="img-fluid rounded-3 shadow" style="max-height:300px;">
                                <?php else: ?>
                                    <div class="bg-light d-flex align-items-center justify-content-center rounded-3" style="height:300px;">
                                        <i class="fas fa-image fa-5x text-muted"></i>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <label class="form-label fw-bold text-primary">Changer la photo (facultatif)</label>
                            <input type="file" name="image" accept="image/*" class="form-control form-control-lg" onchange="previewImage(this)">
                            <img id="preview" class="preview-img shadow-lg img-fluid" alt="Prévisualisation">
                            <small class="text-muted d-block mt-2">JPG, PNG, WEBP – max 5 Mo</small>
                        </div>

                        <div class="col-lg-7">
                            <div class="row g-4">
                                <div class="col-12">
                                    <label class="form-label fw-bold">Nom du service <span class="text-danger">*</span></label>
                                    <input type="text" name="name" value="<?= htmlspecialchars($service['name'] ?? '') ?>" class="form-control form-control-lg rounded-pill" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Type <span class="text-danger">*</span></label>
                                    <select name="type" class="form-select form-select-lg rounded-pill" required>
                                        <option value="fast-food"    <?= ($service['type']??'')=='fast-food'?'selected':'' ?>>Fast Food</option>
                                        <option value="restaurant"   <?= ($service['type']??'')=='restaurant'?'selected':'' ?>>Restaurant</option>
                                        <option value="traiteur"     <?= ($service['type']??'')=='traiteur'?'selected':'' ?>>Traiteur</option>
                                        <option value="patisserie"   <?= ($service['type']??'')=='patisserie'?'selected':'' ?>>Pâtisserie</option>
                                        <option value="cafe"         <?= ($service['type']??'')=='cafe'?'selected':'' ?>>Café</option>
                                        <option value="livraison"    <?= ($service['type']??'')=='livraison'?'selected':'' ?>>Livraison</option>
                                        <option value="autre"        <?= ($service['type']??'')=='autre'?'selected':'' ?>>Autre</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Prix moyen (DT) <span class="text-danger">*</span></label>
                                    <input type="number" name="prix_moyen" value="<?= $service['prix_moyen'] ?? '' ?>" step="0.5" min="1" class="form-control form-control-lg rounded-pill" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Gouvernorat <span class="text-danger">*</span></label>
                                    <input type="text" name="gouvernorat" value="<?= htmlspecialchars($service['gouvernorat'] ?? '') ?>" class="form-control form-control-lg rounded-pill" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Ville / Délégation <span class="text-danger">*</span></label>
                                    <input type="text" name="ville" value="<?= htmlspecialchars($service['ville'] ?? '') ?>" class="form-control form-control-lg rounded-pill" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold">Description complète <span class="text-danger">*</span></label>
                                    <textarea name="details" rows="5" class="form-control rounded-3" required><?= htmlspecialchars($service['details'] ?? '') ?></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold">Menu / Prestations (facultatif)</label>
                                    <textarea name="menu" rows="4" class="form-control rounded-3"><?= htmlspecialchars($service['menu'] ?? '') ?></textarea>
                                </div>
                                <div class="col-12 text-center mt-5">
                                    <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5 shadow-lg">
                                        Enregistrer
                                    </button>
                                    <a href="service.php" class="btn btn-outline-secondary btn-lg rounded-pill px-5 ms-3">Annuler</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function previewImage(input) {
    const preview = document.getElementById('preview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => { preview.src = e.target.result; preview.style.display = 'block'; };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>