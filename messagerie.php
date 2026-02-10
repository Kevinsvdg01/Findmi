<?php
session_start();
require_once 'core/db_connect.php';

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['id_utilisateur'])) {
    header('Location: connexion.php');
    exit();
}

// Vérifier et créer les tables si elles n'existent pas
try {
    // Créer la table conversations
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS conversations (
            id_conversation INT NOT NULL AUTO_INCREMENT,
            id_annonce INT NOT NULL,
            id_utilisateur_1 INT NOT NULL,
            id_utilisateur_2 INT NOT NULL,
            statut ENUM('en attente','en cours','resolue','fermee') NOT NULL DEFAULT 'en attente',
            date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            date_derniere_activite DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id_conversation),
            KEY id_annonce (id_annonce),
            KEY id_utilisateur_1 (id_utilisateur_1),
            KEY id_utilisateur_2 (id_utilisateur_2)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
    ");
    
    // Créer la table messages
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS messages (
            id_message INT NOT NULL AUTO_INCREMENT,
            id_conversation INT NOT NULL,
            id_utilisateur INT NOT NULL,
            texte_message TEXT NOT NULL,
            date_envoi DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            valide_par_admin TINYINT NOT NULL DEFAULT 0,
            est_rejete TINYINT(1) NOT NULL DEFAULT 0,
            date_validation DATETIME DEFAULT NULL,
            id_validateur INT DEFAULT NULL,
            PRIMARY KEY (id_message),
            KEY id_conversation (id_conversation),
            KEY id_utilisateur (id_utilisateur)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
    ");

    // Ajouter la colonne est_rejete si elle n'existe pas (migration)
    try {
        $pdo->exec("ALTER TABLE messages ADD COLUMN est_rejete TINYINT(1) DEFAULT 0");
    } catch (Exception $e) {
        // Column already exists, ignoring error
    }
} catch (PDOException $e) {
    // Si les tables ne peuvent pas être créées, on continue
    error_log("Erreur création tables: " . $e->getMessage());
}

// Récupérer l'ID de l'annonce
if (!isset($_GET['id_annonce']) || !is_numeric($_GET['id_annonce'])) {
    header('Location: index.php');
    exit();
}

$id_annonce = $_GET['id_annonce'];
$id_utilisateur = $_SESSION['id_utilisateur'];

// Récupérer les informations de l'annonce et du déclarant
$stmt = $pdo->prepare("
    SELECT a.*, u.nom, u.email, u.telephone, c.nom_categorie
    FROM annonces a
    JOIN utilisateurs u ON a.id_utilisateur = u.id_utilisateur
    JOIN categories c ON a.id_categorie = c.id_categorie
    WHERE a.id_annonce = ? AND a.statut_annonce = 'publiee'
");
$stmt->execute([$id_annonce]);
$annonce = $stmt->fetch();

if (!$annonce) {
    header('Location: index.php');
    exit();
}

$id_declarant = $annonce['id_utilisateur'];

// Empêcher le déclarant de contacter lui-même
// Empêcher le déclarant de contacter lui-même — afficher un message au lieu d'une redirection silencieuse
if ($id_utilisateur == $id_declarant) {
    // Afficher une page simple expliquant pourquoi la messagerie n'est pas accessible
    http_response_code(200);
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title>Messagerie — Findmi</title>
        <link rel="stylesheet" href="css/style.css">
    </head>
    <body>
        <div class="container" style="max-width:700px;margin:3rem auto;text-align:center;">
            <h2>Vous êtes le déclarant de cette annonce</h2>
            <p>Vous ne pouvez pas ouvrir une conversation avec vous‑même. Si vous souhaitez vérifier les messages reçus, rendez‑vous sur votre tableau de bord.</p>
            <p style="margin-top:1.5rem;"><a class="btn" href="dashboard.php">Aller au tableau de bord</a>
            <a style="margin-left:10px;" class="btn" href="annonce_detail.php?id=<?= htmlspecialchars($id_annonce) ?>">Retour à l'annonce</a></p>
        </div>
        <?php include 'footer.php'; ?>
    </body>
    </html>
    <?php
    exit();
}

// Créer ou récupérer la conversation
$stmt = $pdo->prepare("
    SELECT id_conversation FROM conversations
    WHERE id_annonce = ? AND (
        (id_utilisateur_1 = ? AND id_utilisateur_2 = ?) OR
        (id_utilisateur_1 = ? AND id_utilisateur_2 = ?)
    )
");
$stmt->execute([$id_annonce, $id_utilisateur, $id_declarant, $id_declarant, $id_utilisateur]);
$conversation = $stmt->fetch();

if (!$conversation) {
    // Créer une nouvelle conversation
    $stmt = $pdo->prepare("
        INSERT INTO conversations (id_annonce, id_utilisateur_1, id_utilisateur_2, statut, date_creation)
        VALUES (?, ?, ?, 'en attente', NOW())
    ");
    $stmt->execute([$id_annonce, $id_utilisateur, $id_declarant]);
    $id_conversation = $pdo->lastInsertId();
} else {
    $id_conversation = $conversation['id_conversation'];
}

// Traiter l'envoi d'un message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message_texte'])) {
    $message_texte = trim($_POST['message_texte']);
    
    if (!empty($message_texte)) {
        $stmt = $pdo->prepare("
            INSERT INTO messages (id_conversation, id_utilisateur, texte_message, date_envoi, valide_par_admin)
            VALUES (?, ?, ?, NOW(), 0)
        ");
        $stmt->execute([$id_conversation, $id_utilisateur, $message_texte]);
    }
    
    // Rafraîchir la page pour afficher le nouveau message
    header('Location: messagerie.php?id_annonce=' . $id_annonce);
    exit();
}

// Récupérer les messages de la conversation
$stmt = $pdo->prepare("
    SELECT m.*, u.nom, u.email
    FROM messages m
    JOIN utilisateurs u ON m.id_utilisateur = u.id_utilisateur
    WHERE m.id_conversation = ?
    ORDER BY m.date_envoi ASC
");
$stmt->execute([$id_conversation]);
$messages = $stmt->fetchAll();

// Récupérer les informations de l'utilisateur connecté
$stmt = $pdo->prepare("SELECT nom, email FROM utilisateurs WHERE id_utilisateur = ?");
$stmt->execute([$id_utilisateur]);
$utilisateur = $stmt->fetch();

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messagerie - <?= SITE_NAME ?? 'Findmi' ?></title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body { background-color: #f4f4f9; }
        .navbar { background-color: #0056b3; padding: 1rem; color: white; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: white; text-decoration: none; }
        .container { max-width: 1000px; margin: 2rem auto; }
        
        .messagerie-wrapper {
            display: grid;
            grid-template-columns: 1.2fr 2fr;
            gap: 2rem;
        }

        .annonce-recap {
            background: #fff;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            height: fit-content;
        }

        .annonce-recap h3 {
            color: #0056b3;
            margin-top: 0;
        }

        .annonce-recap p {
            margin: 0.5rem 0;
            font-size: 0.95rem;
        }

        .declarant-info {
            background: #f9f9f9;
            padding: 1rem;
            border-radius: 5px;
            margin-top: 1rem;
        }

        .messages-container {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            height: 600px;
        }

        .messages-list {
            flex: 1;
            overflow-y: auto;
            padding: 1.5rem;
            border-bottom: 1px solid #eee;
        }

        .message {
            margin-bottom: 1.5rem;
            display: flex;
            flex-direction: column;
        }

        .message.own {
            align-items: flex-end;
        }

        .message-content {
            background: #e3f2fd;
            padding: 1rem;
            border-radius: 8px;
            max-width: 70%;
            word-wrap: break-word;
        }

        .message.own .message-content {
            background: #c8e6c9;
        }

        .message-info {
            font-size: 0.75rem;
            color: #999;
            margin-top: 0.25rem;
        }

        .message-form {
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .message-form textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-family: Arial, sans-serif;
            resize: vertical;
            min-height: 80px;
        }

        .message-form button {
            background-color: #0056b3;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
        }

        .message-form button:hover {
            background-color: #004085;
        }

        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: bold;
            margin-top: 0.5rem;
        }

        .status-badge.pending {
            background-color: #ffc107;
            color: #000;
        }

        .status-badge.approved {
            background-color: #28a745;
            color: #fff;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 1rem;
            color: #0056b3;
            text-decoration: none;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .messagerie-wrapper {
                grid-template-columns: 1fr;
            }

            .messages-container {
                height: auto;
            }

            .message-content {
                max-width: 100%;
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
        <a href="annonce_detail.php?id=<?= htmlspecialchars($id_annonce) ?>" class="back-link">← <?= htmlspecialchars(t('back')) ?> à l'annonce</a>

        <div class="messagerie-wrapper">
            <!-- Récapitulatif de l'annonce -->
            <div class="annonce-recap">
                <h3><?= htmlspecialchars($annonce['titre']) ?></h3>
                <p><strong>Catégorie :</strong> <?= htmlspecialchars($annonce['nom_categorie']) ?></p>
                <p><strong>Lieu :</strong> <?= htmlspecialchars($annonce['lieu_perte_trouve']) ?></p>
                <p><strong>Date :</strong> <?= date('d/m/Y', strtotime($annonce['date_perte_trouve'])) ?></p>
                
                <div class="declarant-info">
                    <h4>Déclarant</h4>
                    <p><strong><?= htmlspecialchars($annonce['nom']) ?></strong></p>
                    <p>📧 <?= htmlspecialchars($annonce['email']) ?></p>
                    <?php if (!empty($annonce['telephone'])): ?>
                        <p>📱 <?= htmlspecialchars($annonce['telephone']) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Messagerie -->
            <div class="messages-container">
                <div class="messages-list">
                    <?php if (empty($messages)): ?>
                        <p style="text-align: center; color: #999;">Aucun message pour le moment. Soyez le premier à écrire !</p>
                    <?php else: ?>
                        <?php foreach ($messages as $msg): ?>
                            <div class="message <?= ($msg['id_utilisateur'] == $id_utilisateur) ? 'own' : '' ?>">
                                <div class="message-content">
                                    <?= nl2br(htmlspecialchars($msg['texte_message'])) ?>
                                    <?php if ($msg['valide_par_admin'] == 0 && $msg['id_utilisateur'] == $id_utilisateur): ?>
                                        <span class="status-badge pending">En attente de validation</span>
                                    <?php endif; ?>
                                </div>
                                <div class="message-info">
                                    <strong><?= htmlspecialchars($msg['nom']) ?></strong> - 
                                    <?= date('d/m/Y à H:i', strtotime($msg['date_envoi'])) ?>
                                    <?php if ($msg['valide_par_admin'] == 1): ?>
                                        ✓ Approuvé
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="message-form">
                    <form method="POST">
                        <textarea name="message_texte" placeholder="Tapez votre message ici..." required></textarea>
                        <button type="submit">Envoyer le message</button>
                        <p style="font-size: 0.85rem; color: #999; margin: 0;">
                            ℹ️ Vos messages seront vérifiés et validés par une autorité compétente avant d'être visibles.
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

</body>
</html>
