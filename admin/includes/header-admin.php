<?php
$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Administrateur';
?>
<header class="topbar">
    <div class="topbar-left">
        <button class="menu-toggle" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>
        <div>
            <h2>Tableau de bord Admin</h2>
            <p class="subtitle">Bienvenue, <?= htmlspecialchars($user_name) ?></p>
        </div>
    </div>
    <div class="topbar-right">
        <button class="theme-toggle" onclick="toggleDarkMode()">
            <i class="fas fa-moon"></i>
            <i class="fas fa-sun"></i>
        </button>
        <div class="user-info">
            <div class="user-details">
                <span class="user-name"><?= htmlspecialchars($user_name) ?></span>
                <span class="user-role">Administrateur</span>
            </div>
            <div class="avatar-container">
                <img src="../assets/img/avatar-admin.jpg" alt="Admin" class="avatar" onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($user_name) ?>&background=4A7BFA&color=fff'">
                <div class="status-indicator"></div>
            </div>
        </div>
    </div>
</header>

<style>
.topbar {
    height: 80px;
    background: var(--card);
    box-shadow: 0 2px 20px var(--shadow);
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 30px;
    position: fixed;
    top: 0;
    left: 280px;
    right: 0;
    z-index: 999;
    transition: all 0.35s ease;
    border-bottom: 1px solid rgba(74, 123, 250, 0.1);
}

.topbar-left {
    display: flex;
    align-items: center;
    gap: 20px;
}

.menu-toggle {
    width: 45px;
    height: 45px;
    border-radius: 12px;
    background: rgba(74, 123, 250, 0.1);
    border: 1px solid rgba(74, 123, 250, 0.2);
    color: var(--primary);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.menu-toggle:hover {
    background: var(--primary);
    color: white;
    transform: rotate(90deg);
}

.topbar-left h2 {
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0;
    background: linear-gradient(135deg, var(--text), var(--muted));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.subtitle {
    font-size: 0.85rem;
    color: var(--muted);
    margin: 2px 0 0 0;
}

.topbar-right {
    display: flex;
    align-items: center;
    gap: 20px;
}

.theme-toggle {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    background: rgba(74, 123, 250, 0.1);
    border: 1px solid rgba(74, 123, 250, 0.2);
    color: var(--primary);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.theme-toggle i {
    position: absolute;
    transition: all 0.3s ease;
}

.theme-toggle .fa-moon {
    opacity: 1;
}
.theme-toggle .fa-sun {
    opacity: 0;
    transform: rotate(-90deg);
}

:root.dark .theme-toggle .fa-moon {
    opacity: 0;
    transform: rotate(90deg);
}
:root.dark .theme-toggle .fa-sun {
    opacity: 1;
    transform: rotate(0);
}

.theme-toggle:hover {
    background: var(--primary);
    color: white;
    transform: scale(1.05);
}

.user-info {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 8px 15px;
    border-radius: 15px;
    background: rgba(74, 123, 250, 0.05);
    border: 1px solid rgba(74, 123, 250, 0.1);
    transition: all 0.3s ease;
}

.user-info:hover {
    background: rgba(74, 123, 250, 0.1);
    border-color: rgba(74, 123, 250, 0.2);
}

.user-details {
    text-align: right;
}

.user-name {
    display: block;
    font-weight: 600;
    color: var(--text);
    font-size: 0.95rem;
}

.user-role {
    display: block;
    font-size: 0.8rem;
    color: var(--muted);
}

.avatar-container {
    position: relative;
}

.avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid rgba(74, 123, 250, 0.2);
    transition: all 0.3s ease;
}

.user-info:hover .avatar {
    border-color: var(--primary);
    transform: scale(1.05);
}

.status-indicator {
    position: absolute;
    bottom: 0;
    right: 0;
    width: 12px;
    height: 12px;
    background: var(--success);
    border: 2px solid var(--card);
    border-radius: 50%;
}

@media (max-width: 991px) {
    .topbar {
        left: 0;
    }
    
    .menu-toggle {
        display: flex;
    }
    
    .topbar-left h2 {
        font-size: 1.2rem;
    }
    
    .user-details {
        display: none;
    }
    
    .theme-toggle {
        width: 45px;
        height: 45px;
    }
}

@media (max-width: 768px) {
    .topbar {
        padding: 0 15px;
        height: 70px;
    }
    
    .menu-toggle {
        width: 40px;
        height: 40px;
    }
    
    .avatar {
        width: 45px;
        height: 45px;
    }
}
</style>

<script>
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const topbar = document.querySelector('.topbar');
    sidebar.classList.toggle('open');
    
    if (window.innerWidth <= 991) {
        if (sidebar.classList.contains('open')) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = 'auto';
        }
    }
}

function toggleDarkMode() {
    const html = document.documentElement;
    const isDark = html.classList.contains('dark');
    
    if (isDark) {
        html.classList.remove('dark');
        document.cookie = "darkMode=false; path=/; max-age=31536000";
    } else {
        html.classList.add('dark');
        document.cookie = "darkMode=true; path=/; max-age=31536000";
    }
    
    // Dispatch event for charts to update
    window.dispatchEvent(new Event('darkModeToggle'));
}
</script>