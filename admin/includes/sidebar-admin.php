<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
    <div class="sidebar-header">
        <img src="../assets/img/logo-white.png" alt="Logo" height="50">
        <h3>Tuni-Services</h3>
    </div>
    <nav class="sidebar-nav">
        <a href="stats.php" class="<?= $current_page == 'stats.php' ? 'active' : '' ?>">
            <i class="fas fa-tachometer-alt"></i> Tableau de bord
        </a>
        <a href="users.php" class="<?= $current_page == 'users.php' ? 'active' : '' ?>">
            <i class="fas fa-users"></i> Utilisateurs
        </a>
        <a href="services-en-attente.php" class="<?= $current_page == 'services-en-attente.php' ? 'active' : '' ?>">
            <i class="fas fa-clipboard-check"></i> Validation services
            <span class="notification-badge" id="pending-count">0</span>
        </a>
        <a href="services.php" class="<?= $current_page == 'services.php' ? 'active' : '' ?>">
            <i class="fas fa-tools"></i> Tous les services
        </a>
        <a href="demandes.php" class="<?= $current_page == 'demandes.php' ? 'active' : '' ?>">
            <i class="fas fa-envelope"></i> Demandes
        </a>
        <a href="../api/logout.php" class="logout">
            <i class="fas fa-sign-out-alt"></i> Déconnexion
        </a>
    </nav>
</aside>

<style>
.sidebar {
    position: fixed;
    left: 0;
    top: 0;
    width: 280px;
    height: 100vh;
    background: var(--card);
    color: var(--text);
    box-shadow: 5px 0 20px var(--shadow);
    z-index: 1000;
    transition: all 0.4s;
    border-right: 1px solid rgba(74, 123, 250, 0.1);
}
.sidebar-header {
    padding: 30px 20px;
    text-align: center;
    border-bottom: 1px solid rgba(74, 123, 250, 0.1);
}
.sidebar-header img {
    filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.1));
}
.sidebar-header h3 {
    margin: 10px 0 0;
    font-size: 1.4rem;
    font-weight: 700;
    background: linear-gradient(135deg, var(--primary), var(--accent));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
.sidebar-nav {
    padding: 20px 0;
    display: flex;
    flex-direction: column;
    gap: 5px;
}
.sidebar-nav a {
    display: flex;
    align-items: center;
    padding: 16px 25px;
    color: var(--muted);
    text-decoration: none;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border-left: 4px solid transparent;
    position: relative;
    font-weight: 500;
}
.sidebar-nav a:hover {
    background: rgba(74, 123, 250, 0.05);
    color: var(--primary);
    transform: translateX(8px);
}
.sidebar-nav a.active {
    background: linear-gradient(90deg, rgba(74, 123, 250, 0.1), transparent);
    color: var(--primary);
    border-left-color: var(--primary);
    font-weight: 600;
}
.sidebar-nav a i {
    width: 25px;
    margin-right: 12px;
    font-size: 1.1rem;
    transition: transform 0.3s ease;
}
.sidebar-nav a:hover i {
    transform: scale(1.1);
}
.logout {
    margin-top: auto;
    color: var(--danger) !important;
    border-top: 1px solid rgba(74, 123, 250, 0.1);
    padding-top: 20px;
}
.logout:hover {
    background: rgba(239, 68, 68, 0.1) !important;
    color: var(--danger) !important;
}

.notification-badge {
    position: absolute;
    right: 25px;
    background: var(--danger);
    color: white;
    font-size: 0.7rem;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 12px;
    min-width: 22px;
    text-align: center;
    animation: pulse 2s infinite;
}

/* Dark mode adjustments */
:root.dark .sidebar {
    background: var(--card);
    border-right-color: rgba(255, 255, 255, 0.05);
}
:root.dark .sidebar-header {
    border-bottom-color: rgba(255, 255, 255, 0.05);
}
:root.dark .sidebar-nav a:hover {
    background: rgba(255, 255, 255, 0.05);
}

@media (max-width: 991px) {
    .sidebar {
        transform: translateX(-100%);
    }
    .sidebar.open {
        transform: translateX(0);
    }
}
</style>

<script>
// Fetch pending services count
document.addEventListener('DOMContentLoaded', function() {
    fetch('../api/get-pending-count.php')
        .then(response => response.json())
        .then(data => {
            if (data.count > 0) {
                document.getElementById('pending-count').textContent = data.count;
                document.getElementById('pending-count').style.display = 'block';
            }
        })
        .catch(error => console.error('Error fetching pending count:', error));
});
</script>