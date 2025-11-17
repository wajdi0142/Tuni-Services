// assets/js/script.js
// Tuni-Services - Version finale 2025 (fonctionnel à 100%)

document.addEventListener("DOMContentLoaded", () => {
  const API_URL = "../api"; // Chemin vers le dossier api/

  // ========================
  // 1. Toggle Afficher/Masquer mot de passe
  // ========================
  const toggleBtn = document.getElementById("togglePassword");
  const passwordInput = document.getElementById("password");

  if (toggleBtn && passwordInput) {
    toggleBtn.addEventListener("click", () => {
      const isPassword = passwordInput.type === "password";
      passwordInput.type = isPassword ? "text" : "password";
      const icon = toggleBtn.querySelector("i");
      icon.classList.toggle("fa-eye", !isPassword);
      icon.classList.toggle("fa-eye-slash", isPassword);
    });
  }

  // ========================
  // 2. Connexion Fournisseur
  // ========================
  const loginForm = document.getElementById("loginForm");
  if (loginForm) {
    loginForm.addEventListener("submit", async (e) => {
      e.preventDefault();

      const email = document.getElementById("email").value.trim();
      const password = document.getElementById("password").value;
      const loading = document.getElementById("loading");

      if (!email || !password) {
        alert("Veuillez remplir tous les champs");
        return;
      }

      loading.classList.remove("d-none");

      try {
        const response = await fetch(`${API_URL}/login.php`, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ email, password })
        });

        const data = await response.json();

        if (data.success) {
          const toast = new bootstrap.Toast(document.getElementById("loginToast"));
          toast.show();
          setTimeout(() => {
            window.location.href = "dashboard.php";
          }, 1500);
        } else {
          alert(data.message || "Identifiants incorrects");
        }
      } catch (err) {
        console.error("Erreur login:", err);
        alert("Erreur réseau. Vérifiez que XAMPP est démarré et que vous êtes sur http://localhost");
      } finally {
        loading.classList.add("d-none");
      }
    });
  }

  // ========================
  // 3. Inscription (modal)
  // ========================
  const registerForm = document.getElementById("registerForm");
  if (registerForm) {
    registerForm.addEventListener("submit", async (e) => {
      e.preventDefault();

      const name = document.getElementById("regName").value.trim();
      const email = document.getElementById("regEmail").value.trim();
      const phone = document.getElementById("regPhone").value.trim();
      const password = document.getElementById("regPassword").value;

      if (!name || !email || password.length < 6) {
        alert("Veuillez remplir correctement tous les champs");
        return;
      }

      try {
        const response = await fetch(`${API_URL}/register.php`, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ name, email, phone, password })
        });

        const data = await response.json();

        alert(data.message);

        if (data.success) {
          // Ferme le modal et réinitialise
          const modal = bootstrap.Modal.getInstance(document.getElementById("signupModal"));
          modal.hide();
          registerForm.reset();
        }
      } catch (err) {
        console.error("Erreur inscription:", err);
        alert("Erreur réseau. Vérifiez votre connexion et XAMPP.");
      }
    });
  }
});