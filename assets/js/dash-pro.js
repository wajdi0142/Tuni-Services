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
