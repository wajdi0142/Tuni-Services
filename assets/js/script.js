// assets/js/script.js - Tuni-Services PRO 2026
document.addEventListener("DOMContentLoaded", () => {
  const API_URL = "../api";

  // Animation au scroll
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('visible');
      }
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

  // Connexion
  const loginForm = document.getElementById("loginForm");
  if (loginForm) {
    loginForm.addEventListener("submit", async (e) => {
      e.preventDefault();
      const email = document.getElementById("email").value.trim();
      const password = document.getElementById("password").value;
      const loading = document.getElementById("loading");

      loading.classList.remove("d-none");

      try {
        const res = await fetch(`${API_URL}/login.php`, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ email, password })
        });
        const data = await res.json();

        if (data.success) {
          const toast = new bootstrap.Toast(document.getElementById("loginToast"));
          toast.show();
          setTimeout(() => window.location.href = "dashboard.php", 1500);
        } else {
          alert(data.message || "Identifiants incorrects");
        }
      } catch (err) {
        alert("Erreur de connexion. Vérifiez XAMPP.");
      } finally {
        loading.classList.add("d-none");
      }
    });
  }

  // Inscription modal
  const registerForm = document.getElementById("registerForm");
  if (registerForm) {
    registerForm.addEventListener("submit", async (e) => {
      e.preventDefault();
      const name = document.getElementById("regName").value.trim();
      const email = document.getElementById("regEmail").value.trim();
      const phone = document.getElementById("regPhone").value.trim();
      const password = document.getElementById("regPassword").value;

      if (password.length < 6) return alert("Mot de passe trop court");

      const res = await fetch(`${API_URL}/register.php`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ name, email, phone, password })
      });
      const data = await res.json();
      alert(data.message);
      if (data.success) {
        bootstrap.Modal.getInstance(document.getElementById("signupModal")).hide();
        registerForm.reset();
      }
    });
  }
});
