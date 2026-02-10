<?php
session_start();
require_once '../core/db_connect.php';

// Vérifier que l'utilisateur est connecté en tant qu'autorité
if (!isset($_SESSION['id_autorite'])) {
    header('Location: connexion.php');
    exit();
}

// Traiter l'approbation ou le rejet d'un message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['id_message'])) {
    $id_message = (int)$_POST['id_message'];
    $action = $_POST['action'];

    // S'assurer que la colonne est_rejete existe
    try {
        $pdo->exec("ALTER TABLE messages ADD COLUMN est_rejete TINYINT(1) DEFAULT 0");
    } catch (Exception $e) {
        // Column already exists, ignoring error
    }

    if ($action === 'approve') {
        try {
            $pdo->beginTransaction();

            // Marquer le message comme validé
            $stmt = $pdo->prepare("UPDATE messages SET valide_par_admin = 1, est_rejete = 0, date_validation = NOW(), id_validateur = ? WHERE id_message = ?");
            $stmt->execute([$_SESSION['id_autorite'], $id_message]);

            // Récupérer la conversation liée au message
            $stmt = $pdo->prepare("SELECT id_conversation FROM messages WHERE id_message = ?");
            $stmt->execute([$id_message]);
            $m = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($m && !empty($m['id_conversation'])) {
                $id_conversation = $m['id_conversation'];

                // Récupérer l'annonce liée à la conversation
                $stmt = $pdo->prepare("SELECT id_annonce FROM conversations WHERE id_conversation = ?");
                $stmt->execute([$id_conversation]);
                $conv = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($conv && !empty($conv['id_annonce'])) {
                    $id_annonce_to_update = $conv['id_annonce'];

                    // Mettre à jour le statut de l'annonce en 'retrouve' et enregistrer le validateur
                    $stmt = $pdo->prepare("UPDATE annonces SET statut_annonce = 'retrouve', date_validation = NOW(), id_validateur = ? WHERE id_annonce = ?");
                    $stmt->execute([$_SESSION['id_autorite'], $id_annonce_to_update]);

                    // Marquer la conversation comme résolue
                    $stmt = $pdo->prepare("UPDATE conversations SET statut = 'resolue', date_derniere_activite = NOW() WHERE id_conversation = ?");
                    $stmt->execute([$id_conversation]);
                }
            }

            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log('Erreur lors de l\'approbation: ' . $e->getMessage());
        }
    } elseif ($action === 'reject') {
        $stmt = $pdo->prepare("UPDATE messages SET est_rejete = 1, date_validation = NOW(), id_validateur = ? WHERE id_message = ?");
        $stmt->execute([$_SESSION['id_autorite'], $id_message]);
    }

    // Rafraîchir la page
    header('Location: moderation_messages.php');
    exit();
}

// Récupérer tous les messages en attente de modération
$stmt = $pdo->prepare(
    "SELECT m.*, u.nom as nom_utilisateur, u.email as email_utilisateur,
           a.titre as titre_annonce, conv.id_utilisateur_1, conv.id_utilisateur_2,
           u1.nom as nom_user1, u2.nom as nom_user2
    FROM messages m
    JOIN utilisateurs u ON m.id_utilisateur = u.id_utilisateur
    JOIN conversations conv ON m.id_conversation = conv.id_conversation
    JOIN annonces a ON conv.id_annonce = a.id_annonce
    JOIN utilisateurs u1 ON conv.id_utilisateur_1 = u1.id_utilisateur
    JOIN utilisateurs u2 ON conv.id_utilisateur_2 = u2.id_utilisateur
    WHERE m.valide_par_admin = 0 AND m.est_rejete = 0
    ORDER BY m.date_envoi DESC"
);
$stmt->execute();
$messages_pending = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer aussi les messages approuvés (pour historique)
$stmt = $pdo->prepare(
    "SELECT m.*, u.nom as nom_utilisateur, a.titre as titre_annonce, conv.id_conversation,
           u1.nom as nom_user1, u2.nom as nom_user2
    FROM messages m
    JOIN utilisateurs u ON m.id_utilisateur = u.id_utilisateur
    JOIN conversations conv ON m.id_conversation = conv.id_conversation
    JOIN annonces a ON conv.id_annonce = a.id_annonce
    JOIN utilisateurs u1 ON conv.id_utilisateur_1 = u1.id_utilisateur
    JOIN utilisateurs u2 ON conv.id_utilisateur_2 = u2.id_utilisateur
    WHERE m.valide_par_admin = 1 AND m.est_rejete = 0
    ORDER BY m.date_envoi DESC
    LIMIT 20"
);
$stmt->execute();
$messages_approved = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les messages rejetés
$stmt = $pdo->prepare(
    "SELECT m.*, u.nom as nom_utilisateur, a.titre as titre_annonce, conv.id_conversation,
           u1.nom as nom_user1, u2.nom as nom_user2
    FROM messages m
    JOIN utilisateurs u ON m.id_utilisateur = u.id_utilisateur
    JOIN conversations conv ON m.id_conversation = conv.id_conversation
    JOIN annonces a ON conv.id_annonce = a.id_annonce
    JOIN utilisateurs u1 ON conv.id_utilisateur_1 = u1.id_utilisateur
    JOIN utilisateurs u2 ON conv.id_utilisateur_2 = u2.id_utilisateur
    WHERE m.est_rejete = 1
    ORDER BY m.date_validation DESC
    LIMIT 20"
);
$stmt->execute();
$messages_rejected = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modération des Messages - Admin Findmi</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="admin_style.css">
    <style>
        body {
            background-color: #f4f4f9;
        }

        .admin-navbar {
            background-color: #dc3545;
            padding: 1rem;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .admin-navbar a {
            color: white;
            text-decoration: none;
            margin-left: 1rem;
        }

        .container {
            max-width: 1200px;
            margin: 2rem auto;
        }

        .admin-header {
            background: #fff;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .admin-header h1 {
            margin: 0;
            color: #dc3545;
        }

        .tabs {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            border-bottom: 2px solid #eee;
        }

        .tab-btn {
            padding: 0.75rem 1.5rem;
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
            font-size: 1rem;
            border-bottom: 3px solid transparent;
        }

        .tab-btn.active {
            color: #dc3545;
            border-bottom-color: #dc3545;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .message-card {
            background: #fff;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            border-left: 4px solid #ffc107;
        }

        .message-card.approved {
            border-left-color: #28a745;
        }

        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 1rem;
        }

        .message-meta {
            flex: 1;
        }

        .message-meta h3 {
            margin: 0 0 0.5rem 0;
            color: #0056b3;
        }

        .message-meta p {
            margin: 0.25rem 0;
            font-size: 0.9rem;
            color: #666;
        }

        .message-text {
            background: #f9f9f9;
            padding: 1rem;
            border-radius: 5px;
            margin: 1rem 0;
            line-height: 1.6;
        }

        .message-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .btn-approve,
        .btn-reject {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: bold;
        }

        .btn-approve {
            background-color: #28a745;
            color: white;
        }

        .btn-approve:hover {
            background-color: #218838;
        }

        .btn-reject {
            background-color: #dc3545;
            color: white;
        }

        .btn-reject:hover {
            background-color: #c82333;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #999;
        }

        .stats {
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
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .stat-card h3 {
            margin: 0;
            color: #666;
            font-size: 0.9rem;
            text-transform: uppercase;
        }

        .stat-card .number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #dc3545;
            margin: 0.5rem 0;
        }

        @media (max-width: 768px) {
            .stats {
                grid-template-columns: 1fr;
            }

            .message-actions {
                flex-direction: column;
            }

            .tabs {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>

    <nav class="admin-navbar">
        <h1>Admin Findmi</h1>
        <div>
            <a href="index.php">Tableau de bord</a>
            <a href="deconnexion.php">Déconnexion</a>
        </div>
    </nav>

    <div class="container">
        <div class="admin-header">
            <h1>🔒 Modération des Messages</h1>
            <p>Validez ou rejetez les messages entre utilisateurs</p>
        </div>

        <!-- Statistiques -->
        <div class="stats">
            <div class="stat-card">
                <h3>En attente</h3>
                <div class="number"><?= count($messages_pending) ?></div>
            </div>
            <div class="stat-card">
                <h3>Approuvés (derniers)</h3>
                <div class="number"><?= count($messages_approved) ?></div>
            </div>
            <div class="stat-card">
                <h3>Total conversations</h3>
                <div class="number"><?php $stmt = $pdo->query("SELECT COUNT(*) as count FROM conversations");
                                    $count = $stmt->fetch()['count'];
                                    echo $count; ?></div>
            </div>
        </div>

        <!-- Onglets -->
        <div class="tabs">
            <button class="tab-btn active" onclick="switchTab('pending')">
                Messages en attente (<?= count($messages_pending) ?>)
            </button>
            <button class="tab-btn" onclick="switchTab('approved')">
                Messages approuvés
            </button>
            <button class="tab-btn" onclick="switchTab('rejected')">
                Messages rejetés
            </button>
        </div>

        <!-- Tab: Messages en attente -->
        <div id="pending" class="tab-content active">
            <?php if (empty($messages_pending)): ?>
                <div class="empty-state">
                    <p>✅ Aucun message en attente de modération</p>
                </div>
            <?php else: ?>
                <?php foreach ($messages_pending as $msg): ?>
                    <div class="message-card">
                        <div class="message-header">
                            <div class="message-meta">
                                <h3>📨 <?= htmlspecialchars($msg['titre_annonce']) ?></h3>
                                <p><strong>De :</strong> <?= htmlspecialchars($msg['nom_utilisateur']) ?> (<?= htmlspecialchars($msg['email_utilisateur']) ?>)</p>
                                <p><strong>Conversation entre :</strong> <?= htmlspecialchars($msg['nom_user1']) ?> et <?= htmlspecialchars($msg['nom_user2']) ?></p>
                                <p><strong>Date :</strong> <?= date('d/m/Y à H:i', strtotime($msg['date_envoi'])) ?></p>
                            </div>
                        </div>

                        <div class="message-text">
                            <?= nl2br(htmlspecialchars($msg['texte_message'])) ?>
                        </div>

                        <div class="message-actions">
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="id_message" value="<?= htmlspecialchars($msg['id_message']) ?>">
                                <input type="hidden" name="action" value="approve">
                                <button type="submit" class="btn-approve">✅ Approuver</button>
                            </form>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="id_message" value="<?= htmlspecialchars($msg['id_message']) ?>">
                                <input type="hidden" name="action" value="reject">
                                <button type="submit" class="btn-reject" onclick="return confirm('Êtes-vous sûr de vouloir rejeter ce message ?');">❌ Rejeter</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Tab: Messages approuvés -->
        <div id="approved" class="tab-content">
            <?php if (empty($messages_approved)): ?>
                <div class="empty-state">
                    <p>Aucun message approuvé pour le moment</p>
                </div>
            <?php else: ?>
                <?php foreach ($messages_approved as $msg): ?>
                    <div class="message-card approved">
                        <div class="message-header">
                            <div class="message-meta">
                                <h3>📨 <?= htmlspecialchars($msg['titre_annonce']) ?></h3>
                                <p><strong>De :</strong> <?= htmlspecialchars($msg['nom_utilisateur']) ?></p>
                                <p><strong>Conversation entre :</strong> <?= htmlspecialchars($msg['nom_user1']) ?> et <?= htmlspecialchars($msg['nom_user2']) ?></p>
                                <p><strong>Date :</strong> <?= date('d/m/Y à H:i', strtotime($msg['date_envoi'])) ?></p>
                                <p style="color: #28a745;"><strong>✅ Approuvé</strong></p>
                            </div>
                        </div>

                        <div class="message-text">
                            <?= nl2br(htmlspecialchars($msg['texte_message'])) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Tab: Messages rejetés -->
        <div id="rejected" class="tab-content">
            <?php if (empty($messages_rejected)): ?>
                <div class="empty-state">
                    <p>Aucun message rejeté pour le moment</p>
                </div>
            <?php else: ?>
                <?php foreach ($messages_rejected as $msg): ?>
                    <div class="message-card" style="border-left-color: #dc3545;">
                        <div class="message-header">
                            <div class="message-meta">
                                <h3>📨 <?= htmlspecialchars($msg['titre_annonce']) ?></h3>
                                <p><strong>De :</strong> <?= htmlspecialchars($msg['nom_utilisateur']) ?></p>
                                <p><strong>Conversation entre :</strong> <?= htmlspecialchars($msg['nom_user1']) ?> et <?= htmlspecialchars($msg['nom_user2']) ?></p>
                                <p><strong>Date :</strong> <?= date('d/m/Y à H:i', strtotime($msg['date_envoi'])) ?></p>
                                <p style="color: #dc3545;"><strong>❌ Rejeté</strong></p>
                            </div>
                        </div>

                        <div class="message-text">
                            <?= nl2br(htmlspecialchars($msg['texte_message'])) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function switchTab(tabName) {
            // Masquer tous les onglets
            const contents = document.querySelectorAll('.tab-content');
            contents.forEach(content => content.classList.remove('active'));

            // Désactiver tous les boutons
            const buttons = document.querySelectorAll('.tab-btn');
            buttons.forEach(btn => btn.classList.remove('active'));

            // Afficher l'onglet sélectionné et activer le bouton
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
        }
    </script>

    <?php include '../footer.php'; ?>

</body>

</html>