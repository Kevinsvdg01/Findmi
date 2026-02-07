<?php
// admin/index.php
session_start();
require_once '../core/db_connect.php';

// 1. VÉRIFICATION DE LA SESSION AUTORITÉ
if (!isset($_SESSION['id_autorite'])) {
    header('Location: connexion.php');
    exit();
}
$id_autorite = $_SESSION['id_autorite'];
$nom_autorite = $_SESSION['nom_autorite'];

// 2. TRAITEMENT DES ACTIONS (APPROBATION / REJET)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_annonce'])) {
    $id_annonce_action = $_POST['id_annonce'];

    if (isset($_POST['approuver'])) {
        $sql = "UPDATE annonces SET statut_annonce = 'publiee', id_validateur = ?, date_validation = NOW() WHERE id_annonce = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_autorite, $id_annonce_action]);
        $_SESSION['admin_message'] = "L'annonce #$id_annonce_action a été approuvée avec succès.";
    } elseif (isset($_POST['rejeter'])) {
        $motif_rejet = trim($_POST['motif_rejet']);
        if (empty($motif_rejet)) $motif_rejet = "Annonce non conforme aux directives.";
        // On met à jour la date de validation aussi pour les rejets pour garder une trace de la date de traitement
        $sql = "UPDATE annonces SET statut_annonce = 'rejetee', id_validateur = ?, date_validation = NOW(), motif_rejet = ? WHERE id_annonce = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_autorite, $motif_rejet, $id_annonce_action]);
        $_SESSION['admin_message'] = "L'annonce #$id_annonce_action a été rejetée.";
    }
    header('Location: index.php');
    exit();
}

// 3. RÉCUPÉRATION DES DONNÉES
// Annonces en attente
$stmt_pending = $pdo->query("
    SELECT a.*, c.nom_categorie, u.email AS email_utilisateur
    FROM annonces a
    JOIN categories c ON a.id_categorie = c.id_categorie
    JOIN utilisateurs u ON a.id_utilisateur = u.id_utilisateur
    WHERE a.statut_annonce = 'en_attente_validation'
    ORDER BY a.date_creation ASC
");
$annonces_en_attente = $stmt_pending->fetchAll();

// Statistiques
$stats_validées = $pdo->query("SELECT COUNT(*) FROM annonces WHERE statut_annonce = 'publiee'")->fetchColumn();
$stats_rejetées = $pdo->query("SELECT COUNT(*) FROM annonces WHERE statut_annonce = 'rejetee'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - Findmi</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="admin_style.css">
</head>
<body>

    <div class="admin-grid-container">
        <!-- Sidebar de navigation -->
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-shield-alt"></i> Findmi Admin</h2>
            </div>
            <nav class="sidebar-nav">
                <a href="index.php" class="active"><i class="fas fa-hourglass-half"></i> Annonces en attente</a>
                <a href="historique.php"><i class="fas fa-history"></i> Historique</a>
                <a href="deconnexion.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
            </nav>
            <div class="sidebar-footer">
                <p>Connecté en tant que:</p>
                <strong><?= htmlspecialchars($nom_autorite) ?></strong>
            </div>
        </aside>

        <!-- Overlay pour mobile -->
        <div class="admin-overlay"></div>

        <!-- Contenu principal -->
        <main class="admin-main-content">
            <header class="admin-header">
                <button id="adminMenuToggle" class="admin-menu-toggle" aria-label="Ouvrir le menu"><i class="fas fa-bars"></i></button>
                <h1>Annonces à Valider</h1>
                <div class="admin-stats">
                    <div class="stat-box" title="Annonces en attente de validation">
                        <span class="stat-number"><?= count($annonces_en_attente) ?></span>
                        <span class="stat-label">En Attente</span>
                    </div>
                    <div class="stat-box" title="Annonces déjà validées">
                        <span class="stat-number"><?= $stats_validées ?></span>
                        <span class="stat-label">Validées</span>
                    </div>
                    <div class="stat-box" title="Annonces déjà rejetées">
                        <span class="stat-number"><?= $stats_rejetées ?></span>
                        <span class="stat-label">Rejetées</span>
                    </div>
                </div>
            </header>
            
            <?php if (isset($_SESSION['admin_message'])): ?>
                <div class="alert alert-success"><?= htmlspecialchars($_SESSION['admin_message']); ?></div>
                <?php unset($_SESSION['admin_message']); endif; ?>

            <div class="annonces-list">
                <?php if (empty($annonces_en_attente)): ?>
                    <div class="empty-state">
                        <i class="fas fa-check-double"></i>
                        <h3>Tout est à jour !</h3>
                        <p>Il n'y a aucune annonce à valider pour le moment.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($annonces_en_attente as $annonce): ?>
                        <div class="admin-annonce-card">
                            <div class="card-photo">
                                <a href="../<?= htmlspecialchars($annonce['photo_url']) ?>" target="_blank" title="Cliquez pour agrandir">
                                    <img src="../<?= htmlspecialchars($annonce['photo_url']) ?>" alt="Photo du document">
                                </a>
                            </div>
                            <div class="card-details">
                                <h3 class="card-title"><?= htmlspecialchars($annonce['titre']) ?></h3>
                                <p class="card-meta">
                                    <span title="Utilisateur"><i class="fas fa-user"></i> <?= htmlspecialchars($annonce['email_utilisateur']) ?></span> | 
                                    <span title="Date de soumission"><i class="fas fa-calendar-plus"></i> <?= date('d/m/Y H:i', strtotime($annonce['date_creation'])) ?></span>
                                </p>
                                <ul class="details-list">
                                    <li><strong>Nom:</strong> <?= htmlspecialchars($annonce['nom_sur_document']) ?></li>
                                    <li><strong>Catégorie:</strong> <?= htmlspecialchars($annonce['nom_categorie']) ?></li>
                                    <li><strong>Lieu/Date:</strong> <?= htmlspecialchars($annonce['lieu_perte_trouve']) ?> le <?= date('d/m/Y', strtotime($annonce['date_perte_trouve'])) ?></li>
                                </ul>
                                <?php if(!empty($annonce['description'])): ?>
                                    <p class="description"><strong>Description:</strong> <?= nl2br(htmlspecialchars($annonce['description'])) ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="card-actions">
                                <form action="index.php" method="POST" onsubmit="return confirm('Voulez-vous vraiment APPROUVER cette annonce ?');">
                                    <input type="hidden" name="id_annonce" value="<?= $annonce['id_annonce'] ?>">
                                    <button type="submit" name="approuver" class="btn btn-approve"><i class="fas fa-check"></i> Approuver</button>
                                </form>
                                <button type="button" class="btn btn-reject" onclick="openRejectModal(<?= $annonce['id_annonce'] ?>)"><i class="fas fa-times"></i> Rejeter</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Modale de Rejet -->
    <div id="rejectModal" class="modal-overlay">
        <div class="modal-content">
            <h3 class="modal-title">Motif du Rejet</h3>
            <p>Veuillez indiquer la raison du rejet. Cette information sera utile pour l'utilisateur.</p>
            <form action="index.php" method="POST">
                <input type="hidden" id="modal_id_annonce" name="id_annonce" value="">
                <textarea id="modal_motif_rejet" name="motif_rejet" rows="4" placeholder="Ex: Photo illisible, informations sensibles non masquées..." required></textarea>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeRejectModal()">Annuler</button>
                    <button type="submit" name="rejeter" class="btn btn-reject">Confirmer le Rejet</button>
                </div>
            </form>
        </div>
    </div>

    <script src="admin_script.js"></script>
</body>
</html>