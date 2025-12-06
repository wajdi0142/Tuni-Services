<?php require_once 'protect.php'; ?>
<?php
require '../db/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// === TOUTES LES STATS EN UNE REQUÊTE ===
$stmt = $pdo->query("
    SELECT 
        COUNT(*) as total_services,
        SUM(CASE WHEN status = 'validé' THEN 1 ELSE 0 END) as services_valides,
        SUM(CASE WHEN status = 'en-attente' THEN 1 ELSE 0 END) as en_attente,
        SUM(CASE WHEN status = 'refusé' THEN 1 ELSE 0 END) as refuses,
        COUNT(DISTINCT user_id) as total_fournisseurs,
        (SELECT COUNT(*) FROM users WHERE role = 'fournisseur') as fournisseurs_actifs,
        (SELECT COUNT(*) FROM users WHERE role = 'client') as clients
    FROM services
");
$global = $stmt->fetch();

// Top 5 des types de services
$top_types = $pdo->query("
    SELECT type, COUNT(*) as count 
    FROM services WHERE status = 'validé' 
    GROUP BY type ORDER BY count DESC LIMIT 5
")->fetchAll();

// Top 5 des villes
$top_villes = $pdo->query("
    SELECT ville, COUNT(*) as count 
    FROM services WHERE status = 'validé' 
    GROUP BY ville ORDER BY count DESC LIMIT 6
")->fetchAll();

// Évolution sur les 30 derniers jours
$evolution = $pdo->query("
    SELECT DATE(created_at) as date, COUNT(*) as nb 
    FROM services 
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY DATE(created_at) ORDER BY date
")->fetchAll();
$dates = []; $values = [];
foreach ($evolution as $e) {
    $dates[] = date('d/m', strtotime($e['date']));
    $values[] = (int)$e['nb'];
}
$chart_dates = json_encode($dates);
$chart_values = json_encode($values);

// Stats mensuelles
$month_stats = $pdo->query("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        COUNT(*) as total,
        SUM(CASE WHEN status = 'validé' THEN 1 ELSE 0 END) as valides,
        SUM(CASE WHEN status = 'en-attente' THEN 1 ELSE 0 END) as en_attente
    FROM services 
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month DESC
    LIMIT 6
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr" class="<?php echo isset($_COOKIE['darkMode']) && $_COOKIE['darkMode'] === 'true' ? 'dark' : ''; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - Tableau de bord</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
:root{
    --bg: #f4f7ff;
    --card: #ffffff;
    --text: #11223a;
    --muted: #6b7280;
    --primary: #4A7BFA;
    --accent: #e7df00;
    --provider-beige: #EFECE3;
    --shadow: rgba(17,34,58,0.06);
    --shadow-lg: rgba(17,34,58,0.16);
    --glass: rgba(255,255,255,0.6);
    --success: #10b981;
    --warning: #f59e0b;
    --danger: #ef4444;
    --info: #3b82f6;
}

:root.dark{
    --bg: #071224;
    --card: #0f1724;
    --text: #e6eef9;
    --muted: #b9c6d8;
    --primary: #2f63ff;
    --accent: #ffdd33;
    --provider-beige: #2b2a28;
    --shadow: rgba(0,0,0,0.5);
    --shadow-lg: rgba(0,0,0,0.7);
    --success: #34d399;
    --warning: #fbbf24;
    --danger: #f87171;
    --info: #60a5fa;
}

*{
    box-sizing: border-box;
}

html,body{
    height: 100%;
}

body{
    margin: 0;
    font-family: 'Inter', sans-serif;
    background: linear-gradient(180deg, #f7f9ff 0%, var(--bg) 60%);
    color: var(--text);
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    transition: background .35s ease, color .35s ease;
}

.admin-container{
    min-height: 100vh;
    padding-left: 280px;
    padding-top: 80px;
    transition: all 0.3s ease;
    position: relative;
    z-index: 1;
}

main{
    padding: 30px;
    margin: 0;
    min-height: calc(100vh - 80px);
}

.topbar{
    left: 280px !important;
}

@media (max-width: 991px){
    .admin-container{
        padding-left: 0;
        padding-top: 70px;
    }
    
    .topbar{
        left: 0 !important;
    }
}

.stat-card{
    background: var(--card);
    border-radius: 18px;
    padding: 1.75rem;
    border: 1px solid rgba(74, 123, 250, 0.1);
    box-shadow: 0 6px 24px var(--shadow);
    transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
    height: 100%;
}

.stat-card::before{
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 6px;
    height: 100%;
    background: linear-gradient(180deg, var(--primary), var(--accent));
    border-radius: 18px 0 0 18px;
}

.stat-card:hover{
    transform: translateY(-8px);
    box-shadow: 0 20px 60px var(--shadow-lg);
    border-color: rgba(74, 123, 250, 0.2);
}

.stat-card:nth-child(2)::before{
    background: linear-gradient(180deg, var(--success), #059669);
}

.stat-card:nth-child(3)::before{
    background: linear-gradient(180deg, var(--warning), #d97706);
}

.stat-card:nth-child(4)::before{
    background: linear-gradient(180deg, var(--info), #1d4ed8);
}

.stat-card .counter{
    font-size: 2.8rem;
    font-weight: 900;
    line-height: 1;
    margin-bottom: 0.5rem;
    background: linear-gradient(135deg, var(--text), var(--muted));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    transition: all 0.35s ease;
}

.stat-card:hover .counter{
    background: linear-gradient(135deg, var(--primary), var(--accent));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.stat-icon{
    width: 64px;
    height: 64px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
    background: linear-gradient(135deg, rgba(74, 123, 250, 0.1), rgba(231, 223, 0, 0.05));
    color: var(--primary);
    margin-bottom: 1rem;
    transition: all 0.35s ease;
}

.stat-card:hover .stat-icon{
    transform: scale(1.1) rotate(5deg);
    background: linear-gradient(135deg, var(--primary), var(--accent));
    color: white;
}

.chart-container{
    background: var(--card);
    border-radius: 18px;
    padding: 1.75rem;
    border: 1px solid rgba(74, 123, 250, 0.1);
    box-shadow: 0 6px 24px var(--shadow);
    transition: all 0.35s ease;
    height: 100%;
}

.chart-container:hover{
    border-color: rgba(74, 123, 250, 0.2);
    box-shadow: 0 12px 32px var(--shadow-lg);
}

.top-item{
    background: rgba(74, 123, 250, 0.05);
    border-radius: 12px;
    padding: 1rem 1.25rem;
    margin-bottom: 0.75rem;
    border: 1px solid rgba(74, 123, 250, 0.1);
    transition: all 0.25s ease;
    cursor: pointer;
}

.top-item:hover{
    background: rgba(74, 123, 250, 0.1);
    transform: translateX(8px);
    border-color: rgba(74, 123, 250, 0.2);
}

.top-badge{
    background: linear-gradient(135deg, var(--primary), var(--accent));
    color: var(--text);
    font-weight: 700;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    animation: pulse 2s infinite;
}

.progress-container{
    height: 6px;
    background: rgba(74, 123, 250, 0.1);
    border-radius: 3px;
    overflow: hidden;
    margin-top: 0.5rem;
}

.progress-fill{
    height: 100%;
    background: linear-gradient(90deg, var(--primary), var(--accent));
    border-radius: 3px;
    transition: width 1s ease-in-out;
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

@keyframes pulse{
    0%, 100%{
        transform: scale(1);
    }
    50%{
        transform: scale(1.05);
    }
}

@keyframes shimmer{
    0%{
        background-position: -1000px 0;
    }
    100%{
        background-position: 1000px 0;
    }
}

.fade-in{
    animation: fadeInUp 0.6s ease-out forwards;
}

.fade-in-delay-1{
    animation-delay: 0.1s;
    opacity: 0;
}

.fade-in-delay-2{
    animation-delay: 0.2s;
    opacity: 0;
}

.fade-in-delay-3{
    animation-delay: 0.3s;
    opacity: 0;
}

.fade-in-delay-4{
    animation-delay: 0.4s;
    opacity: 0;
}

.shimmer{
    background: linear-gradient(90deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.3) 50%, rgba(255,255,255,0) 100%);
    background-size: 1000px 100%;
    animation: shimmer 2s infinite;
}

.stats-grid{
    display: grid;
    gap: 1.5rem;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
}

.admin-header{
    background: var(--card);
    border-radius: 18px;
    padding: 1.5rem 2rem;
    margin-bottom: 2rem;
    border: 1px solid rgba(74, 123, 250, 0.1);
    box-shadow: 0 6px 24px var(--shadow);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.admin-header h1{
    font-size: 1.75rem;
    font-weight: 800;
    margin: 0;
    background: linear-gradient(135deg, var(--primary), var(--accent));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.time-selector{
    display: flex;
    gap: 0.5rem;
    background: rgba(74, 123, 250, 0.05);
    padding: 0.5rem;
    border-radius: 12px;
}

.time-btn{
    padding: 0.5rem 1rem;
    border: none;
    background: transparent;
    color: var(--muted);
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.25s ease;
}

.time-btn.active{
    background: var(--primary);
    color: white;
}

.time-btn:hover:not(.active){
    background: rgba(74, 123, 250, 0.1);
    color: var(--text);
}

.kpi-card{
    background: linear-gradient(135deg, rgba(74, 123, 250, 0.05) 0%, rgba(231, 223, 0, 0.02) 100%);
    border-radius: 16px;
    padding: 1.5rem;
    border: 1px solid rgba(74, 123, 250, 0.1);
    transition: all 0.35s ease;
}

.kpi-card:hover{
    background: linear-gradient(135deg, rgba(74, 123, 250, 0.1) 0%, rgba(231, 223, 0, 0.05) 100%);
    border-color: rgba(74, 123, 250, 0.2);
}

.custom-tooltip{
    background: var(--card);
    border: 1px solid rgba(74, 123, 250, 0.2);
    border-radius: 12px;
    padding: 1rem;
    box-shadow: 0 10px 40px var(--shadow-lg);
}

@media (max-width: 768px){
    .stat-card{
        padding: 1.25rem;
    }
    
    .stat-card .counter{
        font-size: 2.2rem;
    }
    
    .admin-header{
        flex-direction: column;
        gap: 1rem;
        align-items: flex-start;
    }
    
    .time-selector{
        width: 100%;
        overflow-x: auto;
    }
    
    main{
        padding: 20px 15px;
    }
}

::-webkit-scrollbar{
    width: 8px;
    height: 8px;
}

::-webkit-scrollbar-track{
    background: rgba(74, 123, 250, 0.05);
    border-radius: 4px;
}

::-webkit-scrollbar-thumb{
    background: linear-gradient(135deg, var(--primary), var(--accent));
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover{
    background: linear-gradient(135deg, var(--primary), var(--accent));
}

::selection{
    background: rgba(74, 123, 250, 0.3);
    color: var(--text);
}

body{
    overflow-x: hidden;
}

@media (max-width: 480px){
    .admin-container{
        padding-top: 60px;
    }
    
    main{
        padding: 15px 10px;
    }
}
</style>
</head>
<body><?php include '../admin/includes/sidebar-admin.php'; ?>
    
    
    <div class="admin-container">
        <?php include '../admin/includes/header-admin.php'; ?>
        
        <main class="p-3 p-md-4">
            <!-- Header with title and time selector -->
            <div class="admin-header fade-in">
                <div>
                    <h1><i class="fas fa-chart-line me-3"></i>Tableau de bord</h1>
                    <p class="text-muted mb-0">Aperçu global des performances et statistiques</p>
                </div>
                <div class="time-selector">
                    <button class="time-btn active" data-period="30d">30j</button>
                    <button class="time-btn" data-period="90d">90j</button>
                    <button class="time-btn" data-period="1y">1an</button>
                    <button class="time-btn" data-period="all">Tout</button>
                </div>
            </div>

            <!-- Main Stats Grid -->
            <div class="row g-4 mb-5">
                <div class="col-md-3">
                    <div class="stat-card fade-in fade-in-delay-1">
                        <div class="stat-icon">
                            <i class="fas fa-concierge-bell"></i>
                        </div>
                        <div class="counter" id="counter-services"><?= $global['total_services'] ?></div>
                        <h6 class="mb-2">Services totaux</h6>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-success small">
                                <i class="fas fa-arrow-up me-1"></i>12%
                            </span>
                            <span class="text-muted small">Ce mois</span>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="stat-card fade-in fade-in-delay-2">
                        <div class="stat-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="counter" id="counter-valid"><?= $global['services_valides'] ?></div>
                        <h6 class="mb-2">Services publiés</h6>
                        <div class="progress-container">
                            <div class="progress-fill" style="width: <?= $global['total_services'] ? ($global['services_valides'] / $global['total_services'] * 100) : 0 ?>%"></div>
                        </div>
                        <span class="text-muted small">Taux de publication</span>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="stat-card fade-in fade-in-delay-3">
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="counter" id="counter-pending"><?= $global['en_attente'] ?></div>
                        <h6 class="mb-2">En attente</h6>
                        <div class="progress-container">
                            <div class="progress-fill" style="width: <?= $global['total_services'] ? ($global['en_attente'] / $global['total_services'] * 100) : 0 ?>%; background: linear-gradient(90deg, var(--warning), #f59e0b)"></div>
                        </div>
                        <span class="text-muted small">Nécessitent validation</span>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="stat-card fade-in fade-in-delay-4">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="counter" id="counter-providers"><?= $global['fournisseurs_actifs'] ?></div>
                        <h6 class="mb-2">Fournisseurs actifs</h6>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-success small">
                                <i class="fas fa-arrow-up me-1"></i>5%
                            </span>
                            <span class="text-muted small">Ce trimestre</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="row g-4 mb-5">
                <!-- Evolution Chart -->
                <div class="col-lg-8">
                    <div class="chart-container">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h5 class="mb-1">Évolution des services</h5>
                                <p class="text-muted small mb-0">Activité sur les 30 derniers jours</p>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-filter me-2"></i>Filtrer
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#" data-chart="services">Services</a></li>
                                    <li><a class="dropdown-item" href="#" data-chart="providers">Fournisseurs</a></li>
                                    <li><a class="dropdown-item" href="#" data-chart="clients">Clients</a></li>
                                </ul>
                            </div>
                        </div>
                        <canvas id="evolutionChart" height="120"></canvas>
                    </div>
                </div>

                <!-- Top Categories -->
                <div class="col-lg-4">
                    <div class="chart-container mb-4">
                        <h5 class="mb-4"><i class="fas fa-star me-2" style="color: var(--accent)"></i>Top catégories</h5>
                        <?php foreach ($top_types as $index => $t): ?>
                            <div class="top-item" data-category="<?= $t['type'] ?>">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div style="flex: 1;">
                                        <div class="d-flex align-items-center mb-1">
                                            <span class="fw-bold me-2"><?= $index + 1 ?>.</span>
                                            <span class="fw-semibold"><?= ucfirst(str_replace('-', ' ', $t['type'])) ?></span>
                                        </div>
                                        <div class="progress-container">
                                            <div class="progress-fill" style="width: <?= $global['services_valides'] ? ($t['count'] / $global['services_valides'] * 100) : 0 ?>%"></div>
                                        </div>
                                    </div>
                                    <span class="top-badge"><?= $t['count'] ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Bottom Row -->
            <div class="row g-4">
                <!-- Top Villes -->
                <div class="col-lg-6">
                    <div class="chart-container">
                        <h5 class="mb-4"><i class="fas fa-map-marker-alt me-2" style="color: var(--danger)"></i>Top villes</h5>
                        <?php foreach ($top_villes as $index => $v): ?>
                            <div class="top-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div style="flex: 1;">
                                        <div class="d-flex align-items-center mb-1">
                                            <span class="fw-bold me-2"><?= $index + 1 ?>.</span>
                                            <span class="fw-semibold"><?= htmlspecialchars($v['ville']) ?></span>
                                        </div>
                                        <div class="progress-container">
                                            <div class="progress-fill" style="width: <?= $global['services_valides'] ? ($v['count'] / $global['services_valides'] * 100) : 0 ?>%"></div>
                                        </div>
                                    </div>
                                    <span class="top-badge"><?= $v['count'] ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Monthly Stats -->
                <div class="col-lg-6">
                    <div class="chart-container">
                        <h5 class="mb-4"><i class="fas fa-calendar-alt me-2" style="color: var(--success)"></i>Statistiques mensuelles</h5>
                        <div class="table-responsive">
                            <table class="table table-borderless">
                                <thead>
                                    <tr>
                                        <th class="text-muted small">Mois</th>
                                        <th class="text-muted small text-end">Total</th>
                                        <th class="text-muted small text-end">Validés</th>
                                        <th class="text-muted small text-end">En attente</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($month_stats as $stat): ?>
                                        <?php 
                                            $month = DateTime::createFromFormat('Y-m', $stat['month']);
                                            $monthName = $month ? $month->format('M Y') : $stat['month'];
                                        ?>
                                        <tr class="border-bottom border-light">
                                            <td class="fw-semibold"><?= $monthName ?></td>
                                            <td class="text-end fw-bold"><?= $stat['total'] ?></td>
                                            <td class="text-end text-success"><?= $stat['valides'] ?></td>
                                            <td class="text-end text-warning"><?= $stat['en_attente'] ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Counter animations
        function animateCounter(element, start, end, duration) {
            let startTimestamp = null;
            const step = (timestamp) => {
                if (!startTimestamp) startTimestamp = timestamp;
                const progress = Math.min((timestamp - startTimestamp) / duration, 1);
                element.textContent = Math.floor(progress * (end - start) + start);
                if (progress < 1) {
                    window.requestAnimationFrame(step);
                }
            };
            window.requestAnimationFrame(step);
        }

        // Animate all counters
        document.querySelectorAll('.counter').forEach(counter => {
            const finalValue = parseInt(counter.textContent);
            animateCounter(counter, 0, finalValue, 1500);
        });

        // Time selector
        document.querySelectorAll('.time-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.time-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                // Ici vous pourriez rafraîchir les données selon la période
            });
        });

        // Evolution Chart
        const ctx = document.getElementById('evolutionChart').getContext('2d');
        
        // Gradient pour l'arrière-plan
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(74, 123, 250, 0.3)');
        gradient.addColorStop(1, 'rgba(74, 123, 250, 0.05)');
        
        // Couleur pour le dark mode
        const isDark = document.documentElement.classList.contains('dark');
        const gridColor = isDark ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.05)';
        const textColor = isDark ? '#e6eef9' : '#11223a';
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= $chart_dates ?>,
                datasets: [{
                    label: 'Services',
                    data: <?= $chart_values ?>,
                    borderColor: 'var(--primary)',
                    backgroundColor: gradient,
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: 'var(--accent)',
                    pointBorderColor: 'var(--card)',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 8
                }]
            },
            options: {
                responsive: true,
                plugins: { 
                    legend: { 
                        labels: {
                            color: textColor,
                            font: { family: 'Inter' }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'var(--card)',
                        titleColor: textColor,
                        bodyColor: textColor,
                        borderColor: 'var(--primary)',
                        borderWidth: 1,
                        cornerRadius: 8,
                        usePointStyle: true,
                        callbacks: {
                            label: function(context) {
                                return `${context.dataset.label}: ${context.parsed.y}`;
                            }
                        }
                    }
                },
                scales: { 
                    y: { 
                        beginAtZero: true,
                        grid: { 
                            color: gridColor,
                            drawBorder: false
                        },
                        ticks: { 
                            color: textColor,
                            callback: function(value) {
                                return value % 1 === 0 ? value : '';
                            }
                        }
                    },
                    x: {
                        grid: { 
                            color: gridColor,
                            drawBorder: false
                        },
                        ticks: { color: textColor }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        });

        // Category click
        document.querySelectorAll('.top-item[data-category]').forEach(item => {
            item.addEventListener('click', function() {
                const category = this.getAttribute('data-category');
                alert(`Voir les détails pour la catégorie: ${category}`);
                // Ici vous pourriez rediriger vers une page de détails
            });
        });

        // Hover effects
        const statCards = document.querySelectorAll('.stat-card');
        statCards.forEach(card => {
            card.addEventListener('mouseenter', () => {
                card.style.zIndex = '10';
            });
            
            card.addEventListener('mouseleave', () => {
                card.style.zIndex = '1';
            });
        });
    });

    // Dark mode toggle
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
        
        // Update chart colors if needed
        // Vous pourriez recréer le graphique ici si nécessaire
    }
    </script>
</body>
</html>