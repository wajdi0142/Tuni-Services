<?php
$token = $_GET['token'] ?? '';
if (empty($token)) {
    die('<h1 class="text-center text-danger mt-5">Lien invalide ou expiré.</h1>');
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Réinitialiser le mot de passe - Tuni-Services</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

  <div class="container py-5 d-flex align-items-center" style="min-height:100vh;">
    <div class="row justify-content-center w-100">
      <div class="col-md-6 col-lg-5">
        <div class="card card-premium shadow-lg p-4" style="border-radius:20px;">
          <div class="text-center mb-4">
            <img src="../assets/img/logo.png" alt="Logo" height="60">
            <h3 class="mt-3">Nouveau mot de passe</h3>
          </div>

          <form id="newPasswordForm">
            <input type="hidden" value="<?= htmlspecialchars($token) ?>" id="token">
            
            <div class="mb-3">
              <label class="form-label">Nouveau mot de passe (8+ caractères)</label>
              <div class="input-group">
                <input type="password" id="newPassword" class="form-control" required minlength="8">
                <button type="button" class="btn btn-outline-secondary" id="togglePass">
                  <i class="fas fa-eye"></i>
                </button>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label">Confirmer</label>
              <input type="password" id="confirmPassword" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary w-100 rounded-pill">
              Mettre à jour le mot de passe
            </button>
          </form>

          <div id="result" class="mt-4 text-center"></div>
          <div class="text-center mt-3">
            <a href="login.html" class="text-muted small">Retour à la connexion</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
  document.getElementById("togglePass").addEventListener("click", () => {
    const input = document.getElementById("newPassword");
    const icon = document.querySelector("#togglePass i");
    if (input.type === "password") {
      input.type = "text";
      icon.classList.replace("fa-eye", "fa-eye-slash");
    } else {
      input.type = "password";
      icon.classList.replace("fa-eye-slash", "fa-eye");
    }
  });

  document.getElementById("newPasswordForm").addEventListener("submit", async (e) => {
    e.preventDefault();

    const pass1 = document.getElementById("newPassword").value;
    const pass2 = document.getElementById("confirmPassword").value;
    const token = document.getElementById("token").value.trim();

    if (pass1 !== pass2) {
      alert("Les mots de passe ne correspondent pas !");
      return;
    }
    if (pass1.length < 8) {
      alert("Minimum 8 caractères !");
      return;
    }

    const btn = e.target.querySelector("button[type='submit']");
    const originalText = btn.textContent;
    btn.disabled = true;
    btn.textContent = "Envoi en cours...";

    try {
      const res = await fetch("../api/reset-password.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ token, password: pass1 })
      });
      const data = await res.json();

      document.getElementById("result").innerHTML = `
        <div class="alert alert-success mt-3">
            <strong>Félicitations !</strong> Votre mot de passe a été changé avec succès !
            <br><a href="login.html" class="btn btn-primary btn-sm mt-2">Se connecter maintenant</a>
        </div>`;

      if (data.success) {
        document.getElementById("newPasswordForm").style.display = "none";
      }
    } catch (err) {
      alert("Erreur réseau. Vérifiez votre connexion.");
    } finally {
      btn.disabled = false;
      btn.textContent = originalText;
    }
  });
  </script>
</body>
</html>