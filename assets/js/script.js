document.addEventListener("DOMContentLoaded", () => {
  // Toggle password
  const toggleBtn = document.getElementById("togglePassword");
  const password = document.getElementById("password");
  if (toggleBtn && password) {
    toggleBtn.addEventListener("click", () => {
      const type = password.type === "password" ? "text" : "password";
      password.type = type;
      toggleBtn.querySelector("i").classList.toggle("fa-eye");
      toggleBtn.querySelector("i").classList.toggle("fa-eye-slash");
    });
  }

  // Login
  const loginForm = document.getElementById("loginForm");
  if (loginForm) {
    loginForm.addEventListener("submit", async (e) => {
      e.preventDefault();
      const email = document.getElementById("email").value;
      const password = document.getElementById("password").value;
      const loading = document.getElementById("loading");
      loading.classList.remove("d-none");

      const res = await fetch("../api/login.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ email, password })
      });
      const data = await res.json();
      loading.classList.add("d-none");

      if (data.success) {
        const toast = new bootstrap.Toast(document.getElementById("loginToast"));
        toast.show();
        setTimeout(() => window.location.href = "dashboard.php", 1500);
      } else {
        alert(data.message);
      }
    });
  }

  // Register
  const regForm = document.getElementById("registerForm");
  if (regForm) {
    regForm.addEventListener("submit", async (e) => {
      e.preventDefault();
      const name = document.getElementById("regName").value;
      const email = document.getElementById("regEmail").value;
      const phone = document.getElementById("regPhone").value;
      const password = document.getElementById("regPassword").value;

      const res = await fetch("../api/register.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ name, email, phone, password })
      });
      const data = await res.json();
      alert(data.message);
      if (data.success) {
        bootstrap.Modal.getInstance(document.getElementById("signupModal")).hide();
      }
    });
  }
});