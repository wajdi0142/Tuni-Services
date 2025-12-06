<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}
require '../db/config.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <title>Tableau de bord - Tuni-Services</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
  <style>
  /* ============================================================= */
  /*  Tuni-Services – Tableau de bord Fournisseur 2025            */
  /*  Design moderne avec animations fluides                       */
  /* ============================================================= */
  
  @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@400;500;600;700&display=swap');
  
  :root{
      --primary: #4A7BFA;
      --primary-dark: #2f63ff;
      --accent: #e7df00;
      --dark: #11223a;
      --light: #f4f7ff;
      --white: #ffffff;
      --muted: #6b7280;
      --success: #10b981;
      --warning: #f59e0b;
      --danger: #ef4444;
      --shadow-sm: 0 4px 6px -1px rgba(17, 34, 58, 0.05);
      --shadow-md: 0 10px 15px -3px rgba(17, 34, 58, 0.08);
      --shadow-lg: 0 20px 25px -5px rgba(17, 34, 58, 0.12);
      --shadow-xl: 0 25px 50px -12px rgba(17, 34, 58, 0.18);
      --glass-bg: rgba(255, 255, 255, 0.1);
      --glass-border: rgba(255, 255, 255, 0.2);
  }
  
  *{
      box-sizing: border-box;
      margin: 0;
      padding: 0;
  }
  
  body{
      font-family: 'Inter', sans-serif;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: var(--dark);
      min-height: 100vh;
      overflow-x: hidden;
  }
  
  body::before{
      content: '';
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: 
          radial-gradient(circle at 20% 80%, rgba(255,255,255,0.1) 0%, transparent 50%),
          radial-gradient(circle at 80% 20%, rgba(255,255,255,0.05) 0%, transparent 50%);
      pointer-events: none;
      z-index: -1;
  }

  /* ============================================================= */
  /* CONTENU PRINCIPAL                                            */
  /* ============================================================= */
  
  .flex-grow-1{
      margin-left: 300px;
      min-height: 100vh;
      background: transparent;
      padding: 40px 50px;
      animation: fadeIn 0.8s ease-out 0.3s both;
  }
  
  .flex-grow-1 h2{
      font-family: 'Poppins', sans-serif;
      font-weight: 800;
      font-size: 2.5rem;
      background: linear-gradient(135deg, var(--white), #e6eef9);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      text-shadow: 0 2px 10px rgba(0,0,0,0.1);
      margin-bottom: 40px;
      position: relative;
      display: inline-block;
  }
  
  .flex-grow-1 h2::after{
      content: '';
      position: absolute;
      bottom: -10px;
      left: 0;
      width: 100px;
      height: 4px;
      background: linear-gradient(90deg, var(--accent), transparent);
      border-radius: 2px;
  }
  
  /* ============================================================= */
  /* CARTES STATISTIQUES AVANCÉES                                 */
  /* ============================================================= */
  
  .card-premium{
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      border-radius: 24px;
      padding: 40px 32px;
      text-align: center;
      box-shadow: var(--shadow-xl);
      border: 1px solid var(--glass-border);
      transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
      position: relative;
      overflow: hidden;
      height: 100%;
      opacity: 0;
      transform: translateY(30px);
      animation: fadeInUp 0.6s ease-out forwards;
  }
  
  .card-premium:nth-child(1){ animation-delay: 0.2s; }
  .card-premium:nth-child(2){ animation-delay: 0.4s; }
  .card-premium:nth-child(3){ animation-delay: 0.6s; }
  
  .card-premium::before{
      content: "";
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 6px;
      background: linear-gradient(90deg, var(--primary), var(--accent), var(--primary));
      background-size: 200% 100%;
      border-radius: 24px 24px 0 0;
      animation: gradientMove 3s ease infinite;
  }
  
  .card-premium::after{
      content: '';
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: radial-gradient(circle, rgba(74,123,250,0.1) 0%, transparent 70%);
      opacity: 0;
      transition: opacity 0.5s ease;
  }
  
  .card-premium:hover{
      transform: translateY(-15px) scale(1.02);
      box-shadow: 
          0 40px 80px rgba(17, 34, 58, 0.25),
          inset 0 0 0 1px rgba(255,255,255,0.2);
  }
  
  .card-premium:hover::after{
      opacity: 1;
  }
  
  .card-premium i{
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 80px;
      height: 80px;
      background: linear-gradient(135deg, var(--primary), var(--primary-dark));
      color: var(--white);
      margin-bottom: 24px;
      font-size: 34px;
      border-radius: 20px;
      box-shadow: 0 10px 25px rgba(74, 123, 250, 0.3);
      transition: all 0.5s ease;
  }
  
  .card-premium:hover i{
      transform: scale(1.1) rotate(15deg);
      background: linear-gradient(135deg, var(--accent), #ffdd33);
      box-shadow: 0 15px 35px rgba(231, 223, 0, 0.4);
  }
  
  .stat-number{
      font-size: 52px;
      font-weight: 900;
      color: var(--dark);
      margin: 16px 0;
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, var(--primary), var(--accent));
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      position: relative;
      display: inline-block;
  }
  
  .stat-number::after{
      content: '';
      position: absolute;
      bottom: -5px;
      left: 10%;
      width: 80%;
      height: 3px;
      background: linear-gradient(90deg, transparent, var(--primary), transparent);
      border-radius: 3px;
  }
  
  .card-premium p{
      margin: 0;
      font-size: 16px;
      color: var(--muted);
      font-weight: 500;
      letter-spacing: 0.5px;
  }
  
  /* Carte grande (réservations) */
  .card-premium.large{
      padding: 40px;
      text-align: left;
  }
  
  .card-premium h4{
      color: var(--dark);
      font-weight: 700;
      font-size: 1.8rem;
      margin-bottom: 32px;
      position: relative;
      display: inline-block;
  }
  
  .card-premium h4::after{
      content: '';
      position: absolute;
      bottom: -8px;
      left: 0;
      width: 60px;
      height: 4px;
      background: linear-gradient(90deg, var(--accent), transparent);
      border-radius: 2px;
  }
  
  .empty-state{
      padding: 60px 40px;
      text-align: center;
  }
  
  .empty-state i{
      font-size: 5rem;
      color: var(--primary);
      margin-bottom: 24px;
      opacity: 0.6;
      animation: float 6s ease-in-out infinite;
  }
  
  .empty-state p{
      font-size: 1.2rem;
      color: var(--muted);
      max-width: 400px;
      margin: 0 auto;
  }
  
  /* ============================================================= */
  /* ANIMATIONS                                                   */
  /* ============================================================= */
  
  @keyframes slideInLeft{
      from{
          transform: translateX(-100%);
          opacity: 0;
      }
      to{
          transform: translateX(0);
          opacity: 1;
      }
  }
  
  @keyframes fadeIn{
      from{
          opacity: 0;
      }
      to{
          opacity: 1;
      }
  }
  
  @keyframes fadeInUp{
      from{
          opacity: 0;
          transform: translateY(30px);
      }
      to{
          opacity: 1;
          transform: translateY(0);
      }
  }
  
  @keyframes gradientMove{
      0%, 100%{
          background-position: 0% 50%;
      }
      50%{
          background-position: 100% 50%;
      }
  }
  
  @keyframes float{
      0%, 100%{
          transform: translateY(0) rotate(0deg);
      }
      50%{
          transform: translateY(-20px) rotate(5deg);
      }
  }
  
  @keyframes pulse{
      0%, 100%{
          transform: scale(1);
          opacity: 1;
      }
      50%{
          transform: scale(1.05);
          opacity: 0.8;
      }
  }
  
  /* ============================================================= */
  /* RESPONSIVE DESIGN                                            */
  /* ============================================================= */
  
  @media (max-width: 1200px){
      .sidebar{
          width: 280px;
      }
      
      .flex-grow-1{
          margin-left: 280px;
          padding: 35px 40px;
      }
  }
  
  @media (max-width: 992px){
      .sidebar{
          transform: translateX(-100%);
          transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
          width: 280px;
      }
      
      .flex-grow-1{
          margin-left: 0;
          padding: 30px !important;
      }
      
      .sidebar.show{
          transform: translateX(0);
          box-shadow: var(--shadow-xl);
      }
      
      .menu-toggle{
          display: block !important;
          position: fixed;
          top: 20px;
          right: 20px;
          z-index: 1001;
          background: var(--primary);
          color: white;
          border: none;
          width: 50px;
          height: 50px;
          border-radius: 12px;
          font-size: 1.5rem;
          box-shadow: var(--shadow-md);
          cursor: pointer;
      }
  }
  
  @media (max-width: 768px){
      .flex-grow-1{
          padding: 25px 20px !important;
      }
      
      .flex-grow-1 h2{
          font-size: 2rem;
      }
      
      .card-premium{
          padding: 30px 24px;
      }
      
      .stat-number{
          font-size: 42px;
      }
      
      .card-premium i{
          width: 70px;
          height: 70px;
          font-size: 30px;
      }
  }
  
  @media (max-width: 576px){
      .card-premium{
          padding: 25px 20px;
      }
      
      .stat-number{
          font-size: 36px;
      }
      
      .card-premium i{
          width: 60px;
          height: 60px;
          font-size: 26px;
      }
  }
  
  /* ============================================================= */
  /* EFFETS SPÉCIAUX                                              */
  /* ============================================================= */
  
  .glow{
      animation: pulse 2s ease-in-out infinite;
  }
  
  .hover-lift{
      transition: transform 0.3s ease;
  }
  
  .hover-lift:hover{
      transform: translateY(-5px);
  }
  
  .text-gradient{
      background: linear-gradient(135deg, var(--primary), var(--accent));
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
  }
  
  /* ============================================================= */
  /* BOUTON MENU TOGGLE (mobile)                                 */
  /* ============================================================= */
  
  .menu-toggle{
      display: none;
      position: fixed;
      top: 25px;
      right: 25px;
      z-index: 1100;
      background: linear-gradient(135deg, var(--primary), var(--primary-dark));
      color: white;
      border: none;
      width: 56px;
      height: 56px;
      border-radius: 16px;
      font-size: 1.5rem;
      box-shadow: var(--shadow-lg);
      cursor: pointer;
      transition: all 0.3s ease;
  }
  
  .menu-toggle:hover{
      transform: scale(1.1) rotate(90deg);
      box-shadow: 0 15px 35px rgba(74, 123, 250, 0.4);
  }
  
  /* ============================================================= */
  /* SCROLLBAR PERSONNALISÉE                                     */
  /* ============================================================= */
  
  ::-webkit-scrollbar{
      width: 10px;
      height: 10px;
  }
  
  ::-webkit-scrollbar-track{
      background: rgba(255, 255, 255, 0.1);
      border-radius: 10px;
  }
  
  ::-webkit-scrollbar-thumb{
      background: linear-gradient(135deg, var(--primary), var(--accent));
      border-radius: 10px;
      border: 2px solid rgba(255, 255, 255, 0.1);
  }
  
  ::-webkit-scrollbar-thumb:hover{
      background: linear-gradient(135deg, var(--accent), var(--primary));
  }
  
  /* ============================================================= */
  /* SELECTION TEXT                                               */
  /* ============================================================= */
  
  ::selection{
      background: rgba(74, 123, 250, 0.3);
      color: var(--dark);
  }
  </style>
</head>
<body>
    <?php include '../provider/includes/sidebar.php'; ?>
    <!-- Contenu -->
    <div class="flex-grow-1 p-4 p-lg-5">
      <h2 class="fw-bold mb-5">Tableau de bord</h2>

      <div class="row g-4 mb-5">
        <div class="col-md-4">
          <div class="card card-premium">
            <i class="fas fa-eye"></i>
            <div class="stat-number" id="counter1">0</div>
            <p class="text-muted">Vues ce mois-ci</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card card-premium">
            <i class="fas fa-phone"></i>
            <div class="stat-number" id="counter2">0</div>
            <p class="text-muted">Appels reçus</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card card-premium">
            <i class="fas fa-star"></i>
            <div class="stat-number" id="counter3">0.0</div>
            <p class="text-muted">Note moyenne</p>
          </div>
        </div>
      </div>

      <div class="card card-premium p-4 large">
        <h4 class="mb-4">Dernières réservations</h4>
        <div class="empty-state">
          <i class="fas fa-inbox"></i>
          <p>Aucune réservation pour le moment</p>
        </div>
      </div>
    </div>
  </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
      <script>
  // Menu toggle pour mobile
  document.getElementById('menuToggle').addEventListener('click', function() {
      document.getElementById('sidebar').classList.toggle('show');
      this.classList.toggle('active');
  });
  
  // Animation des compteurs
  function animateCounter(element, start, end, duration) {
      let startTimestamp = null;
      const step = (timestamp) => {
          if (!startTimestamp) startTimestamp = timestamp;
          const progress = Math.min((timestamp - startTimestamp) / duration, 1);
          const current = Math.floor(progress * (end - start) + start);
          
          if (element.id === 'counter3') {
              element.textContent = progress.toFixed(1);
          } else {
              element.textContent = new Intl.NumberFormat().format(current);
          }
          
          if (progress < 1) {
              window.requestAnimationFrame(step);
          }
      };
      window.requestAnimationFrame(step);
  }
  
  // Lancer les animations après le chargement
  document.addEventListener('DOMContentLoaded', function() {
      // Démarrer les compteurs
      setTimeout(() => {
          animateCounter(document.getElementById('counter1'), 0, 1247, 1500);
          animateCounter(document.getElementById('counter2'), 0, 89, 1200);
          animateCounter(document.getElementById('counter3'), 0, 4.8, 1800);
      }, 500);
      
      // Effet parallaxe au scroll
      window.addEventListener('scroll', function() {
          const scrolled = window.pageYOffset;
          const cards = document.querySelectorAll('.card-premium');
          
          cards.forEach((card, index) => {
              const speed = 0.1 * (index + 1);
              card.style.transform = `translateY(${scrolled * speed * 0.1}px)`;
          });
      });
      
      // Effet de hover amélioré
      const cards = document.querySelectorAll('.card-premium');
      cards.forEach(card => {
          card.addEventListener('mouseenter', function() {
              this.style.zIndex = '10';
          });
          
          card.addEventListener('mouseleave', function() {
              this.style.zIndex = '1';
          });
      });
      
      // Effet de ripple sur les boutons
      document.querySelectorAll('.sidebar nav a').forEach(link => {
          link.addEventListener('click', function(e) {
              const ripple = document.createElement('span');
              const rect = this.getBoundingClientRect();
              const size = Math.max(rect.width, rect.height);
              const x = e.clientX - rect.left - size / 2;
              const y = e.clientY - rect.top - size / 2;
              
              ripple.style.cssText = `
                  position: absolute;
                  border-radius: 50%;
                  background: rgba(255,255,255,0.3);
                  transform: scale(0);
                  animation: ripple 0.6s linear;
                  width: ${size}px;
                  height: ${size}px;
                  top: ${y}px;
                  left: ${x}px;
              `;
              
              this.appendChild(ripple);
              setTimeout(() => ripple.remove(), 600);
          });
      });
  });
  
  // Ajouter l'animation ripple au CSS
  const style = document.createElement('style');
  style.textContent = `
      @keyframes ripple {
          to {
              transform: scale(4);
              opacity: 0;
          }
      }
  `;
  document.head.appendChild(style);
  </script>
</body>
</html>