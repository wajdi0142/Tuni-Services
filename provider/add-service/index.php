<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.html');
    exit;
}
require '../../db/config.php';

// Liste des types prédéfinis
$types = ['fast-food', 'taxi', 'transport', 'depanneuse', 'meubles', 'autre'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <title>Choisir un type de service - Tuni-Services</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
  <link rel="stylesheet" href="../../assets/css/add-service.css" />
</head>
<body>

  <header>
    <div class="container d-flex justify-content-between align-items-center py-3">
      <div class="d-flex align-items-center gap-3">
        <img src="../../assets/img/logo.png" alt="Logo" class="logo" height="48" />
        <h1 class="h5 mb-0 fw-bold">Ajouter un service</h1>
      </div>
      <div>
      <a href="../service.php" class="btn btn-outline-primary btn-sm"> Retour </a>
      </div>
    </div>
  </header>

  <div class="container my-5">
    <div class="card card-premium shadow-lg p-5" style="max-width: 900px; margin: 0 auto;">
      <h3 class="text-center mb-4">Choisissez le type de service</h3>

      <div class="row g-4">
        <?php foreach ($types as $t): ?>
          <div class="col-md-4">
            <div class="type-card card h-100 text-center p-4" data-type="<?= $t ?>">
              <i class="fas fa-<?= $t==='fast-food'?'utensils':($t==='taxi'?'taxi':($t==='transport'?'truck':($t==='depanneuse'?'tools':($t==='meubles'?'couch':'question')))) ?> fa-3x text-primary mb-3"></i>
              <h5 class="fw-bold"><?= ucfirst($t) ?></h5>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <div id="formContainer" class="mt-5"></div>
    </div>
  </div>

  <script>
    document.querySelectorAll('.type-card').forEach(card => {
      card.addEventListener('click', () => {
        document.querySelectorAll('.type-card').forEach(c => c.classList.remove('selected'));
        card.classList.add('selected');
        const type = card.dataset.type;
        const formDiv = document.getElementById('formContainer');
        
        if (type === 'autre') {
          formDiv.innerHTML = `
            <form method="POST" action="custom-save.php" enctype="multipart/form-data">
              <input type="hidden" name="type" value="custom" />
              <div class="mb-3">
                <label class="form-label">Nom du service *</label>
                <input type="text" name="name" class="form-control" required />
              </div>
              <div class="row mb-3">
                <div class="col-md-6">
                  <label class="form-label">Gouvernorat *</label>
                  <select name="gouvernorat" class="form-select" required>
                    <option value="">Choisir...</option>
                    <option>Tunis</option><option>Sfax</option><option>Sousse</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Ville *</label>
                  <input type="text" name="ville" class="form-control" required />
                </div>
              </div>
              <div class="mb-3">
                <label class="form-label">Prix moyen (DT)</label>
                <input type="number" name="prix" step="0.1" class="form-control" />
              </div>
              <div class="mb-3">
                <label class="form-label">Description détaillée</label>
                <textarea name="details" class="form-control" rows="4" placeholder="Horaires, spécialités, contact..."></textarea>
              </div>
              <div class="mb-3">
                <label class="form-label">Photo</label>
                <input type="file" name="photo" class="form-control" accept="image/*" />
              </div>
              <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg rounded-pill">
                  Ajouter ce service
                </button>
              </div>
            </form>
          `;
        } else {
          // Redirection vers formulaire prédéfini
          window.location.href = type + '.php';
        }
      });
    });
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>