// assets/js/script.js - Tuni-Services PRO 2026 ©
document.addEventListener("DOMContentLoaded", () => {
  const API_URL = "../api";

  // Animation au scroll
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) entry.target.classList.add('visible');
    });
  }, { threshold: 0.1 });
  document.querySelectorAll('.fade-in').forEach(el => observer.observe(el));

  // Toggle mot de passe
  const toggleBtn = document.getElementById("togglePassword");
  if (toggleBtn) {
    toggleBtn.addEventListener("click", () => {
      const input = document.getElementById("password");
      const icon = toggleBtn.querySelector("i");
      if (input.type === "password") {
        input.type = "text";
        icon.classList.replace("fa-eye", "fa-eye-slash");
      } else {
        input.type = "password";
        icon.classList.replace("fa-eye-slash", "fa-eye");
      }
    });
  }

  // === CONNEXION AVEC SÉLECTEUR DE RÔLE ===
  const loginForm = document.getElementById("loginForm");
  if (loginForm) {
    loginForm.addEventListener("submit", async (e) => {
      e.preventDefault();

      const email    = document.getElementById("email").value.trim();
      const password = document.getElementById("password").value;
      const role     = document.getElementById("role").value.toLowerCase();
      const loading  = document.getElementById("loading");

      loading.classList.remove("d-none");

      try {
        const res = await fetch(`${API_URL}/login.php`, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ email, password, role })
        });

        const data = await res.json();

        if (data.success) {
          const toast = new bootstrap.Toast(document.getElementById("loginToast"));
          toast.show();

          setTimeout(() => {
            window.location.href = data.redirect;
          }, 1600);
        } else {
          alert("Erreur : " + data.message);
        }
      } catch (err) {
        console.error(err);
        alert("Impossible de contacter le serveur. Vérifiez que XAMPP est lancé.");
      } finally {
        loading.classList.add("d-none");
      }
    });
  }

  // === INSCRIPTION MODAL ===
  const registerForm = document.getElementById("registerForm");
  if (registerForm) {
    registerForm.addEventListener("submit", async (e) => {
      e.preventDefault();
      const name     = document.getElementById("regName").value.trim();
      const email    = document.getElementById("regEmail").value.trim();
      const phone    = document.getElementById("regPhone").value.trim();
      const password = document.getElementById("regPassword").value;

      if (password.length < 6) return alert("Le mot de passe doit faire 6 caractères minimum");

      const res = await fetch(`${API_URL}/register.php`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ name, email, phone, password, role: "fournisseur" })
      });
      const data = await res.json();

      alert(data.message);
      if (data.success) {
        bootstrap.Modal.getInstance(document.getElementById("signupModal")).hide();
        registerForm.reset();
      }
    });
  }

  // Vérifie si déjà connecté → redirection auto
  fetch(`${API_URL}/check-session.php`)
    .then(r => r.json())
    .then(d => {
      if (d.loggedIn) {
        window.location.href = d.role === "admin" ? "../admin/stats.php" : "dashboard.php";
      }
    })
    .catch(() => {});
});