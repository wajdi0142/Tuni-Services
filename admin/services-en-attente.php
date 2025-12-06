<?php
require '../db/config.php';
session_start();

// VÉRIF ADMIN (à adapter selon ton système)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$message = '';

// ACTIONS : VALIDER / REFUSER / SUPPRIMER
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    if ($_GET['action'] === 'valider') {
        $pdo->prepare("UPDATE services SET status = 'validé', updated_at = NOW() WHERE id = ?")->execute([$id]);
        $message = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i> Service validé et publié !
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>';
    }
    elseif ($_GET['action'] === 'refuser') {
        $pdo->prepare("UPDATE services SET status = 'refusé', updated_at = NOW() WHERE id = ?")->execute([$id]);
        $message = '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i> Service refusé.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>';
    }
    elseif ($_GET['action'] === 'delete') {
        // Supprimer l'image si existe
        $s = $pdo->query("SELECT image FROM services WHERE id = $id")->fetch();
        if ($s && $s['image'] && file_exists("../uploads/services/".$s['image'])) {
            unlink("../uploads/services/".$s['image']);
        }
        $pdo->prepare("DELETE FROM services WHERE id = ?")->execute([$id]);
        $message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-trash-alt me-2"></i> Service supprimé définitivement.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>';
    }
}

// Récupérer tous les services en attente + infos fournisseur
$stmt = $pdo->query("
    SELECT s.*, u.name
FROM services s
JOIN users u ON s.user_id = u.id
WHERE s.status = 'en-attente'
");
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr" class="<?php echo isset($_COOKIE['darkMode']) && $_COOKIE['darkMode'] === 'true' ? 'dark' : ''; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - Services en attente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
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
}

main{
    padding: 30px;
    margin: 0;
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

.service-card{
    background: var(--card);
    border-radius: 20px;
    border: 1px solid rgba(74, 123, 250, 0.1);
    box-shadow: 0 8px 32px var(--shadow);
    transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    overflow: hidden;
    height: 100%;
}

.service-card:hover{
    transform: translateY(-10px);
    box-shadow: 0 25px 60px var(--shadow-lg);
    border-color: rgba(74, 123, 250, 0.2);
}

.service-image{
    height: 220px;
    object-fit: cover;
    width: 100%;
    border-bottom: 1px solid rgba(74, 123, 250, 0.1);
}

.service-image-placeholder{
    height: 220px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, rgba(74, 123, 250, 0.05), rgba(231, 223, 0, 0.02));
    border-bottom: 1px solid rgba(74, 123, 250, 0.1);
}

.service-status{
    position: absolute;
    top: 15px;
    right: 15px;
    background: var(--warning);
    color: var(--text);
    font-weight: 700;
    padding: 6px 18px;
    border-radius: 20px;
    font-size: 0.85rem;
    box-shadow: 0 4px 15px rgba(245, 158, 11, 0.2);
}

.service-actions{
    display: flex;
    gap: 10px;
    margin-top: auto;
}

.action-btn{
    flex: 1;
    padding: 12px;
    border-radius: 12px;
    border: none;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.3s ease;
    cursor: pointer;
}

.action-btn:hover{
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}

.btn-validate{
    background: linear-gradient(135deg, var(--success), #059669);
    color: white;
}

.btn-reject{
    background: linear-gradient(135deg, var(--danger), #dc2626);
    color: white;
}

.btn-view{
    background: linear-gradient(135deg, var(--primary), #2563eb);
    color: white;
}

.btn-delete{
    background: linear-gradient(135deg, #6b7280, #4b5563);
    color: white;
}

.provider-info{
    background: rgba(74, 123, 250, 0.05);
    border-radius: 12px;
    padding: 1rem;
    margin: 1rem 0;
    border: 1px solid rgba(74, 123, 250, 0.1);
}

.provider-info-item{
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 8px;
    color: var(--muted);
    font-size: 0.9rem;
}

.provider-info-item:last-child{
    margin-bottom: 0;
}

.provider-info-item i{
    color: var(--primary);
    width: 20px;
}

.empty-state{
    text-align: center;
    padding: 4rem 2rem;
    background: var(--card);
    border-radius: 20px;
    border: 2px dashed rgba(74, 123, 250, 0.2);
}

.empty-state i{
    font-size: 4rem;
    color: var(--success);
    margin-bottom: 1.5rem;
    opacity: 0.8;
}

.empty-state h3{
    color: var(--success);
    margin-bottom: 1rem;
    font-weight: 700;
}

.empty-state p{
    color: var(--muted);
    max-width: 400px;
    margin: 0 auto;
}

.alert{
    border-radius: 12px;
    border: none;
    padding: 1rem 1.5rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.alert-success{
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(5, 150, 105, 0.05));
    border: 1px solid rgba(16, 185, 129, 0.2);
    color: var(--success);
}

.alert-warning{
    background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(217, 119, 6, 0.05));
    border: 1px solid rgba(245, 158, 11, 0.2);
    color: var(--warning);
}

.alert-danger{
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(220, 38, 38, 0.05));
    border: 1px solid rgba(239, 68, 68, 0.2);
    color: var(--danger);
}

.btn-back{
    padding: 0.75rem 1.5rem;
    border-radius: 12px;
    background: rgba(74, 123, 250, 0.1);
    border: 1px solid rgba(74, 123, 250, 0.2);
    color: var(--primary);
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-back:hover{
    background: var(--primary);
    color: white;
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(74, 123, 250, 0.3);
}

@media (max-width: 768px){
    .admin-header{
        flex-direction: column;
        gap: 1rem;
        align-items: flex-start;
    }
    
    .service-actions{
        flex-direction: column;
    }
    
    .action-btn{
        width: 100%;
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

.fade-in{
    animation: fadeInUp 0.6s ease-out forwards;
}
    </style>
</head>
<body>
    <?php include '../admin/includes/sidebar-admin.php'; ?>
    
    <div class="admin-container">
        <?php include '../admin/includes/header-admin.php'; ?>
        
        <main class="p-3 p-md-4">
            <!-- Header -->
            <div class="admin-header fade-in">
                <div>
                    <h1><i class="fas fa-clipboard-check me-3"></i>Validation des services</h1>
                    <p class="text-muted mb-0">Gérez les services en attente de validation</p>
                </div>
                <div>
                    <span class="badge bg-warning text-dark fs-6 px-3 py-2">
                        <i class="fas fa-clock me-2"></i>
                        <?= count($services) ?> en attente
                    </span>
                    <a href="dashboard.php" class="btn-back ms-3">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
                </div>
            </div>

            <?= $message ?>

            <?php if (empty($services)): ?>
                <div class="empty-state fade-in">
                    <i class="fas fa-check-double"></i>
                    <h3>Tous les services sont validés !</h3>
                    <p>Tous les services en attente ont été traités. Aucune action requise pour le moment.</p>
                    <a href="dashboard.php" class="btn-back mt-3">
                        <i class="fas fa-tachometer-alt me-2"></i> Voir le tableau de bord
                    </a>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($services as $s): ?>
                        <div class="col-md-6 col-lg-4 fade-in">
                            <div class="service-card">
                                <?php if (!empty($s['image']) && file_exists("../uploads/services/".$s['image'])): ?>
                                    <img src="../uploads/services/<?= htmlspecialchars($s['image']) ?>" 
                                         class="service-image" 
                                         alt="<?= htmlspecialchars($s['name']) ?>">
                                <?php else: ?>
                                    <div class="service-image-placeholder">
                                        <i class="fas fa-concierge-bell fa-4x" style="color: var(--primary); opacity: 0.3;"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="service-status">
                                    <i class="fas fa-clock me-1"></i> En attente
                                </div>
                                
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h5 class="card-title fw-bold mb-1"><?= htmlspecialchars($s['name']) ?></h5>
                                            <p class="text-muted small mb-0">
                                                <i class="fas fa-utensils me-1"></i>
                                                <?= ucfirst(str_replace('-', ' ', $s['type'])) ?>
                                            </p>
                                        </div>
                                        <small class="text-muted">
                                            <?= date('d/m/Y', strtotime($s['created_at'])) ?>
                                        </small>
                                    </div>
                                    
                                    <p class="card-text text-muted mb-4">
                                        <?= strlen($s['details']) > 100 ? substr(htmlspecialchars($s['details']), 0, 100).'...' : htmlspecialchars($s['details']) ?>
                                    </p>
                                    
                                    <div class="provider-info">
                                        <div class="provider-info-item">
                                            <i class="fas fa-user"></i>
                                            <span><?= htmlspecialchars($s['prenom'].' '.$s['nom']) ?></span>
                                        </div>
                                        <div class="provider-info-item">
                                            <i class="fas fa-envelope"></i>
                                            <span><?= htmlspecialchars($s['email']) ?></span>
                                        </div>
                                        <?php if (!empty($s['telephone'])): ?>
                                        <div class="provider-info-item">
                                            <i class="fas fa-phone"></i>
                                            <span><?= htmlspecialchars($s['telephone']) ?></span>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="service-actions">
                                        <button class="action-btn btn-validate" 
                                                onclick="validateService(<?= $s['id'] ?>)">
                                            <i class="fas fa-check"></i> Valider
                                        </button>
                                        <button class="action-btn btn-reject" 
                                                onclick="rejectService(<?= $s['id'] ?>)">
                                            <i class="fas fa-times"></i> Refuser
                                        </button>
                                        <a href="../visitor/service-details.php?id=<?= $s['id'] ?>" 
                                           target="_blank"
                                           class="action-btn btn-view">
                                            <i class="fas fa-eye"></i> Voir
                                        </a>
                                        <button class="action-btn btn-delete" 
                                                onclick="deleteService(<?= $s['id'] ?>)">
                                            <i class="fas fa-trash"></i> Supprimer
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function validateService(id) {
        if (confirm('Publier ce service ?')) {
            window.location.href = '?action=valider&id=' + id;
        }
    }
    
    function rejectService(id) {
        if (confirm('Refuser ce service ?')) {
            window.location.href = '?action=refuser&id=' + id;
        }
    }
    
    function deleteService(id) {
        if (confirm('SUPPRIMER DÉFINITIVEMENT ce service ?')) {
            window.location.href = '?action=delete&id=' + id;
        }
    }
    
    // Auto-dismiss alerts after 5 seconds
    document.addEventListener('DOMContentLoaded', function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 5000);
        });
        
        // Animation for cards
        const cards = document.querySelectorAll('.fade-in');
        cards.forEach((card, index) => {
            card.style.animationDelay = (index * 0.1) + 's';
        });
    });
    </script>
</body>
</html>