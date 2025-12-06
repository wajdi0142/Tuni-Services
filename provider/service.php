<?php
require '../db/config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Suppression
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM services WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $user_id]);
    header("Location: services.php?deleted=1");
    exit;
}

// Récupérer tous les services du fournisseur
$stmt = $pdo->prepare("SELECT * FROM services WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$all_services = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Séparer en deux tableaux
$services_valides = array_filter($all_services, fn($s) => $s['status'] === 'validé');
$services_attente = array_filter($all_services, fn($s) => $s['status'] !== 'validé');
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mes Services - Tuni-Services Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4A7BFA;
            --primary-dark: #2D4FB3;
            --secondary: #E7DF00;
            --success: #10B981;
            --warning: #F59E0B;
            --danger: #EF4444;
            --info: #3B82F6;
            --dark: #0F172A;
            --light: #F8FAFC;
            --gray-50: #F8FAFC;
            --gray-100: #F1F5F9;
            --gray-200: #E2E8F0;
            --gray-300: #CBD5E1;
            --gray-400: #94A3B8;
            --gray-500: #64748B;
            --gray-600: #475569;
            --gray-700: #334155;
            --gray-800: #1E293B;
            --gray-900: #0F172A;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            --shadow-xl: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            --radius: 12px;
            --radius-lg: 16px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
            min-height: 100vh;
            color: var(--gray-900);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* ===== SIDEBAR STYLE ===== */
        .sidebar {
            width: 280px;
            height: 100vh;
            background: linear-gradient(180deg, var(--dark) 0%, #1E293B 100%);
            position: fixed;
            left: 0;
            top: 0;
            padding: 2rem 1.5rem;
            color: white;
            box-shadow: var(--shadow-lg);
            z-index: 1000;
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-brand h2 {
            font-size: 1.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin: 0;
        }

        .nav-menu {
            list-style: none;
            padding: 0;
        }

        .nav-item {
            margin-bottom: 0.5rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 1.25rem;
            color: var(--gray-300);
            text-decoration: none;
            border-radius: var(--radius);
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            transform: translateX(5px);
        }

        .nav-link.active {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            box-shadow: 0 4px 12px rgba(74, 123, 250, 0.3);
        }

        .nav-link i {
            width: 24px;
            text-align: center;
            font-size: 1.1rem;
        }

        /* ===== MAIN CONTENT ===== */
        .main-content {
            margin-left: 280px;
            padding: 2.5rem;
            min-height: 100vh;
        }

        @media (max-width: 992px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            .main-content {
                margin-left: 0;
                padding: 1.5rem;
            }
        }

        /* ===== HEADER ===== */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2.5rem;
            flex-wrap: wrap;
            gap: 1.5rem;
        }

        .page-title {
            font-size: 2.25rem;
            font-weight: 800;
            color: var(--dark);
            margin: 0;
            line-height: 1.2;
        }

        .page-title .highlight {
            color: var(--primary);
        }

        .page-subtitle {
            color: var(--gray-600);
            font-size: 1.1rem;
            margin-top: 0.5rem;
            font-weight: 400;
        }

        /* ===== STATS ===== */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2.5rem;
        }

        .stat-card {
            background: white;
            border-radius: var(--radius-lg);
            padding: 1.75rem;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            border: 1px solid var(--gray-200);
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background: linear-gradient(180deg, var(--primary), var(--primary-dark));
        }

        .stat-icon {
            width: 56px;
            height: 56px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, rgba(74, 123, 250, 0.1), rgba(231, 223, 0, 0.05));
            color: var(--primary);
        }

        .stat-value {
            font-size: 2.25rem;
            font-weight: 800;
            color: var(--dark);
            line-height: 1;
            margin-bottom: 0.25rem;
        }

        .stat-label {
            color: var(--gray-600);
            font-size: 0.95rem;
            font-weight: 500;
        }

        /* ===== TABS ===== */
        .tabs-container {
            background: white;
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            box-shadow: var(--shadow);
            margin-bottom: 2.5rem;
            border: 1px solid var(--gray-200);
        }

        .tabs-header {
            display: flex;
            gap: 0.5rem;
            background: var(--gray-100);
            padding: 0.5rem;
            border-radius: var(--radius);
            margin-bottom: 1.5rem;
        }

        .tab-btn {
            flex: 1;
            padding: 1rem 1.5rem;
            background: transparent;
            border: none;
            border-radius: var(--radius);
            color: var(--gray-600);
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .tab-btn.active {
            background: white;
            color: var(--primary);
            box-shadow: var(--shadow);
        }

        .tab-btn:hover:not(.active) {
            background: rgba(74, 123, 250, 0.1);
            color: var(--primary);
        }

        .tab-badge {
            background: var(--gray-200);
            color: var(--gray-700);
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .tab-btn.active .tab-badge {
            background: var(--primary);
            color: white;
        }

        /* ===== SERVICES GRID ===== */
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.75rem;
        }

        @media (max-width: 768px) {
            .services-grid {
                grid-template-columns: 1fr;
            }
        }

        .service-card {
            background: white;
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid var(--gray-200);
            height: 100%;
            position: relative;
        }

        .service-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-xl);
            border-color: var(--primary);
        }

        .card-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            transition: transform 0.6s ease;
        }

        .service-card:hover .card-image {
            transform: scale(1.05);
        }

        .card-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.875rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            z-index: 2;
        }

        .badge-published {
            background: var(--success);
            color: white;
        }

        .badge-pending {
            background: var(--warning);
            color: var(--dark);
        }

        .badge-rejected {
            background: var(--danger);
            color: white;
        }

        .card-content {
            padding: 1.75rem;
        }

        .card-category {
            display: inline-block;
            background: rgba(74, 123, 250, 0.1);
            color: var(--primary);
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 0.75rem;
            line-height: 1.4;
        }

        .card-description {
            color: var(--gray-600);
            font-size: 0.9375rem;
            line-height: 1.6;
            margin-bottom: 1.5rem;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 1.25rem;
            border-top: 1px solid var(--gray-200);
        }

        .card-date {
            color: var(--gray-500);
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .card-actions {
            display: flex;
            gap: 0.5rem;
        }

        .action-btn {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--gray-100);
            border: 1px solid var(--gray-200);
            color: var(--gray-700);
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .action-btn:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow);
        }

        .action-btn.edit:hover {
            background: var(--info);
            color: white;
            border-color: var(--info);
        }

        .action-btn.delete:hover {
            background: var(--danger);
            color: white;
            border-color: var(--danger);
        }

        .action-btn.view:hover {
            background: var(--success);
            color: white;
            border-color: var(--success);
        }

        /* ===== EMPTY STATE ===== */
        .empty-state {
            grid-column: 1 / -1;
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: var(--radius-lg);
            border: 2px dashed var(--gray-300);
        }

        .empty-state-icon {
            font-size: 4rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 1.5rem;
        }

        .empty-state-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 0.75rem;
        }

        .empty-state-description {
            color: var(--gray-600);
            font-size: 1rem;
            margin-bottom: 2rem;
            max-width: 400px;
            margin-left: auto;
            margin-right: auto;
        }

        /* ===== BUTTONS ===== */
        .btn-primary-custom {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: var(--radius);
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.3s ease;
            text-decoration: none;
            box-shadow: 0 4px 12px rgba(74, 123, 250, 0.3);
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(74, 123, 250, 0.4);
            color: white;
        }

        /* ===== ALERTS ===== */
        .alert-success {
            background: linear-gradient(135deg, var(--success), #059669);
            color: white;
            border: none;
            border-radius: var(--radius);
            padding: 1.25rem 1.5rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            gap: 1rem;
            animation: slideIn 0.5s ease;
        }

        @keyframes slideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* ===== SCROLLBAR ===== */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--gray-100);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: 4px;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .main-content {
                padding: 1.25rem;
            }
            
            .page-title {
                font-size: 1.75rem;
            }
            
            .stats-container {
                grid-template-columns: 1fr;
            }
            
            .tabs-header {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
<?php include '../provider/includes/sidebar.php'; ?>

<!-- Main Content -->
<div class="main-content">
    <!-- Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">Bienvenue, <span class="highlight"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Fournisseur'); ?></span></h1>
            <p class="page-subtitle">Gérez tous vos services professionnels en un seul endroit</p>
        </div>
        <a href="./add-service/index.php" class="btn-primary-custom">
            <i class="fas fa-plus-circle"></i>
            <span>Ajouter un service</span>
        </a>
    </div>

    <!-- Success Message -->
    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert-success">
            <i class="fas fa-check-circle fa-lg"></i>
            <div class="flex-grow-1">
                <strong>Service supprimé avec succès</strong>
                <div class="small opacity-90">Le service a été définitivement supprimé de votre catalogue</div>
            </div>
            <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="stats-container">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-value"><?= count($services_valides) ?></div>
            <div class="stat-label">Services Publiés</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-value"><?= count($services_attente) ?></div>
            <div class="stat-label">En Attente</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-value"><?= count($all_services) ?></div>
            <div class="stat-label">Total Services</div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="tabs-container">
        <div class="tabs-header">
            <button class="tab-btn active" onclick="showTab('published')">
                <i class="fas fa-check-circle"></i>
                <span>Publiés</span>
                <span class="tab-badge"><?= count($services_valides) ?></span>
            </button>
            <button class="tab-btn" onclick="showTab('pending')">
                <i class="fas fa-clock"></i>
                <span>En Attente</span>
                <span class="tab-badge"><?= count($services_attente) ?></span>
            </button>
        </div>

        <!-- Published Services -->
        <div id="published-tab" class="tab-content">
            <?php if (empty($services_valides)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-rocket"></i>
                    </div>
                    <h3 class="empty-state-title">Aucun service publié</h3>
                    <p class="empty-state-description">
                        Publiez votre premier service pour commencer à recevoir des réservations
                    </p>
                    <a href="./add-service/index.php" class="btn-primary-custom">
                        <i class="fas fa-plus-circle"></i>
                        <span>Créer un service</span>
                    </a>
                </div>
            <?php else: ?>
                <div class="services-grid">
                    <?php foreach ($services_valides as $s): ?>
                        <div class="service-card">
                            <?php if (!empty($s['image']) && file_exists("../uploads/services/".$s['image'])): ?>
                                <img src="../uploads/services/<?= htmlspecialchars($s['image']) ?>" 
                                     class="card-image" 
                                     alt="<?= htmlspecialchars($s['name']) ?>">
                            <?php else: ?>
                                <div style="height: 200px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-image fa-3x text-white opacity-50"></i>
                                </div>
                            <?php endif; ?>
                            
                            <span class="card-badge badge-published">
                                <i class="fas fa-check me-1"></i> Publié
                            </span>
                            
                            <div class="card-content">
                                <span class="card-category">
                                    <?= ucfirst(str_replace('-', ' ', $s['type'])) ?>
                                </span>
                                
                                <h3 class="card-title"><?= htmlspecialchars($s['name']) ?></h3>
                                
                                <p class="card-description">
                                    <?= strlen($s['details']) > 120 ? substr(htmlspecialchars($s['details']), 0, 120) . '...' : htmlspecialchars($s['details']) ?>
                                </p>
                                
                                <div class="card-footer">
                                    <div class="card-date">
                                        <i class="far fa-calendar"></i>
                                        <?= date('d/m/Y', strtotime($s['created_at'])) ?>
                                    </div>
                                    
                                    <div class="card-actions">
                                        <a href="./edit-service.php?id=<?= $s['id'] ?>" 
                                           class="action-btn edit" 
                                           title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="?delete=<?= $s['id'] ?>" 
                                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce service publié ?')"
                                           class="action-btn delete" 
                                           title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                        <a href="../visitor/service-details.php?id=<?= $s['id'] ?>" 
                                           target="_blank" 
                                           class="action-btn view" 
                                           title="Voir">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Pending Services -->
        <div id="pending-tab" class="tab-content" style="display: none;">
            <?php if (empty($services_attente)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-check-double"></i>
                    </div>
                    <h3 class="empty-state-title">Tout est publié !</h3>
                    <p class="empty-state-description">
                        Tous vos services sont actuellement publiés et visibles par les clients
                    </p>
                </div>
            <?php else: ?>
                <div class="services-grid">
                    <?php foreach ($services_attente as $s): ?>
                        <div class="service-card">
                            <?php if (!empty($s['image']) && file_exists("../uploads/services/".$s['image'])): ?>
                                <img src="../uploads/services/<?= htmlspecialchars($s['image']) ?>" 
                                     class="card-image" 
                                     alt="<?= htmlspecialchars($s['name']) ?>"
                                     style="opacity: 0.7;">
                            <?php else: ?>
                                <div style="height: 200px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); display: flex; align-items: center; justify-content: center; opacity: 0.7;">
                                    <i class="fas fa-image fa-3x text-white opacity-50"></i>
                                </div>
                            <?php endif; ?>
                            
                            <span class="card-badge <?= $s['status'] == 'en-attente' ? 'badge-pending' : 'badge-rejected' ?>">
                                <i class="fas <?= $s['status'] == 'en-attente' ? 'fa-clock' : 'fa-times' ?> me-1"></i>
                                <?= $s['status'] == 'en-attente' ? 'En attente' : 'Refusé' ?>
                            </span>
                            
                            <div class="card-content">
                                <span class="card-category">
                                    <?= ucfirst(str_replace('-', ' ', $s['type'])) ?>
                                </span>
                                
                                <h3 class="card-title"><?= htmlspecialchars($s['name']) ?></h3>
                                
                                <p class="card-description">
                                    <?= strlen($s['details']) > 120 ? substr(htmlspecialchars($s['details']), 0, 120) . '...' : htmlspecialchars($s['details']) ?>
                                </p>
                                
                                <div class="card-footer">
                                    <div class="card-date">
                                        <i class="far fa-calendar"></i>
                                        <?= date('d/m/Y', strtotime($s['created_at'])) ?>
                                    </div>
                                    
                                    <div class="card-actions">
                                        <a href="edit-service.php?id=<?= $s['id'] ?>" 
                                           class="action-btn edit" 
                                           title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="?delete=<?= $s['id'] ?>" 
                                           onclick="return confirm('Supprimer définitivement ce service ?')"
                                           class="action-btn delete" 
                                           title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function showTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.style.display = 'none';
    });
    
    // Remove active class from all buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Show selected tab
    document.getElementById(tabName + '-tab').style.display = 'block';
    
    // Add active class to clicked button
    event.currentTarget.classList.add('active');
}

// Auto-dismiss success message
const successAlert = document.querySelector('.alert-success');
if (successAlert) {
    setTimeout(() => {
        successAlert.style.transition = 'opacity 0.5s ease';
        successAlert.style.opacity = '0';
        setTimeout(() => {
            if (successAlert.parentNode) {
                successAlert.parentNode.removeChild(successAlert);
            }
        }, 500);
    }, 5000);
}

// Card hover effects
document.querySelectorAll('.service-card').forEach(card => {
    card.addEventListener('mouseenter', () => {
        card.style.zIndex = '10';
    });
    
    card.addEventListener('mouseleave', () => {
        card.style.zIndex = '1';
    });
});
</script>
</body>
</html>