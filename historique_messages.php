<?php
session_start();
require_once 'core/db_connect.php';

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['id_utilisateur'])) {
    header('Location: connexion.php');
    exit();
}

// Ajouter la colonne est_rejete si elle n'existe pas (migration)
try {
    $pdo->exec("ALTER TABLE messages ADD COLUMN est_rejete TINYINT(1) DEFAULT 0");
} catch (Exception $e) {
    // Colonne existe déjà, on ignore
}

$id_utilisateur = $_SESSION['id_utilisateur'];

// Récupérer tous les messages de l'utilisateur avec l'historique
$stmt = $pdo->prepare("
    SELECT m.id_message, m.texte_message, m.date_envoi, m.valide_par_admin, m.est_rejete, 
           m.date_validation, a.titre as titre_annonce, a.id_annonce, conv.id_conversation,
           u.nom as nom_destinataire, u.email as email_destinataire
    FROM messages m
    JOIN conversations conv ON m.id_conversation = conv.id_conversation
    JOIN annonces a ON conv.id_annonce = a.id_annonce
    LEFT JOIN utilisateurs u ON (
        CASE 
            WHEN m.id_utilisateur = conv.id_utilisateur_1 THEN u.id_utilisateur = conv.id_utilisateur_2
            ELSE u.id_utilisateur = conv.id_utilisateur_1
        END
    )
    WHERE m.id_utilisateur = ?
    ORDER BY m.date_envoi DESC
");
$stmt->execute([$id_utilisateur]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Compter les statistiques
$messages_en_attente = 0;
$messages_approuves = 0;
$messages_rejetes = 0;

foreach ($messages as $msg) {
    if ($msg['est_rejete'] == 1) {
        $messages_rejetes++;
    } elseif ($msg['valide_par_admin'] == 1) {
        $messages_approuves++;
    } else {
        $messages_en_attente++;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historique des Messages - <?= SITE_NAME ?? 'Findmi' ?></title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body { background-color: #f4f4f9; }
        
        .navbar { 
            background-color: #0056b3; 
            padding: 1rem; 
            color: white; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
        }
        
        .navbar a { 
            color: white; 
            text-decoration: none; 
            margin-left: 1rem;
        }
        
        .container { 
            max-width: 900px; 
            margin: 2rem auto; 
            padding: 0 1rem;
        }
        
        .page-header {
            background: #fff;
            padding: 2rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .page-header h1 {
            margin: 0 0 0.5rem 0;
            color: #0056b3;
        }
        
        .page-header p {
            margin: 0;
            color: #666;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: #fff;
            padding: 1.5rem;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 4px solid #0056b3;
        }
        
        .stat-card.pending {
            border-left-color: #ffc107;
        }
        
        .stat-card.approved {
            border-left-color: #28a745;
        }
        
        .stat-card.rejected {
            border-left-color: #dc3545;
        }
        
        .stat-card h3 {
            margin: 0 0 0.5rem 0;
            font-size: 0.9rem;
            color: #666;
            text-transform: uppercase;
        }
        
        .stat-card .number {
            font-size: 2rem;
            font-weight: bold;
        }
        
        .stat-card.pending .number { color: #ffc107; }
        .stat-card.approved .number { color: #28a745; }
        .stat-card.rejected .number { color: #dc3545; }
        
        .messages-container {
            display: grid;
            gap: 1.5rem;
        }
        
        .message-item {
            background: #fff;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 4px solid #0056b3;
        }
        
        .message-item.pending {
            border-left-color: #ffc107;
        }
        
        .message-item.approved {
            border-left-color: #28a745;
        }
        
        .message-item.rejected {
            border-left-color: #dc3545;
        }
        
        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }
        
        .message-info h3 {
            margin: 0 0 0.5rem 0;
            color: #0056b3;
        }
        
        .message-info p {
            margin: 0.25rem 0;
            font-size: 0.9rem;
            color: #666;
        }
        
        .message-status {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: bold;
            white-space: nowrap;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-approved {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-rejected {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .message-text {
            background: #f9f9f9;
            padding: 1rem;
            border-radius: 5px;
            margin: 1rem 0;
            line-height: 1.6;
            color: #333;
        }
        
        .message-meta {
            display: flex;
            gap: 2rem;
            font-size: 0.85rem;
            color: #999;
            flex-wrap: wrap;
        }
        
        .empty-state {
            background: #fff;
            padding: 3rem;
            border-radius: 8px;
            text-align: center;
            color: #999;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .empty-state p {
            margin: 0 0 1rem 0;
        }
        
        .view-conversation-btn {
            display: inline-block;
            margin-top: 1rem;
            padding: 0.5rem 1rem;
            background-color: #0056b3;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 0.9rem;
        }
        
        .view-conversation-btn:hover {
            background-color: #004085;
        }
        
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .message-header {
                flex-direction: column;
            }
            
            .message-status {
                margin-top: 1rem;
            }
            
            .message-meta {
                flex-direction: column;
                gap: 0.5rem;
            }
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <h1><a href="index.php"><?= htmlspecialchars(SITE_NAME ?? 'Findmi') ?></a></h1>
        <div>
            <a href="index.php"><?= htmlspecialchars(t('home')) ?></a>
            <a href="dashboard.php"><?= htmlspecialchars(t('dashboard')) ?></a>
            <?php if (isset($_SESSION['id_utilisateur'])): ?>
                <a href="profil.php"><?= htmlspecialchars(t('profile')) ?></a>
                <a href="logout.php"><?= htmlspecialchars(t('logout')) ?></a>
            <?php else: ?>
                <a href="connexion.php"><?= htmlspecialchars(t('login')) ?></a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="container">
        <!-- En-tête -->
        <div class="page-header">
            <h1>📨 <?= htmlspecialchars(t('messages_history')) ?></h1>
            <p><?= htmlspecialchars(t('messages_history')) ?></p>
        </div>

        <!-- Statistiques -->
        <div class="stats-grid">
            <div class="stat-card pending">
                <h3>En attente</h3>
                <div class="number"><?= $messages_en_attente ?></div>
            </div>
            <div class="stat-card approved">
                <h3>Approuvés</h3>
                <div class="number"><?= $messages_approuves ?></div>
            </div>
            <div class="stat-card rejected">
                <h3>Rejetés</h3>
                <div class="number"><?= $messages_rejetes ?></div>
            </div>
        </div>

        <!-- Historique des messages -->
        <?php if (count($messages) === 0): ?>
            <div class="empty-state">
                <p>📭 Vous n'avez pas encore envoyé de messages</p>
                <p>Recherchez un document ou une annonce et entrez en contact avec les utilisateurs.</p>
                <a href="recherche.php" class="view-conversation-btn"><?= htmlspecialchars(t('browse_listings')) ?></a>
            </div>
        <?php else: ?>
            <div class="messages-container">
                <?php foreach ($messages as $msg): 
                    $classe_status = '';
                    $texte_status = '';
                    $badge_class = '';
                    
                    if ($msg['est_rejete'] == 1) {
                        $classe_status = 'rejected';
                        $texte_status = '❌ Rejeté';
                        $badge_class = 'status-rejected';
                    } elseif ($msg['valide_par_admin'] == 1) {
                        $classe_status = 'approved';
                        $texte_status = '✅ Approuvé';
                        $badge_class = 'status-approved';
                    } else {
                        $classe_status = 'pending';
                        $texte_status = '⏳ En attente';
                        $badge_class = 'status-pending';
                    }
                ?>
                    <div class="message-item <?= $classe_status ?>">
                        <div class="message-header">
                            <div class="message-info">
                                <h3>📌 <?= htmlspecialchars($msg['titre_annonce']) ?></h3>
                                <p><strong>À :</strong> <?= htmlspecialchars($msg['nom_destinataire'] ?? 'Utilisateur') ?></p>
                            </div>
                            <span class="message-status <?= $badge_class ?>"><?= $texte_status ?></span>
                        </div>

                        <div class="message-text">
                            <?= nl2br(htmlspecialchars($msg['texte_message'])) ?>
                        </div>

                        <div class="message-meta">
                            <span>📤 Envoyé le <?= date('d/m/Y à H:i', strtotime($msg['date_envoi'])) ?></span>
                            <?php if ($msg['date_validation']): ?>
                                <span>✓ Validé le <?= date('d/m/Y à H:i', strtotime($msg['date_validation'])) ?></span>
                            <?php endif; ?>
                            <a href="messagerie.php?id_annonce=<?= htmlspecialchars($msg['id_annonce']) ?>" class="view-conversation-btn" style="margin-top: 0;">Voir la conversation</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'footer.php'; ?>

</body>
</html>
