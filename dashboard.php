<?php
// dash
session_start();
require_once 'core/db_connect.php';

// 1. VÉRIFICATION DE LA SESSION UTILISATEUR
if (!isset($_SESSION['id_utilisateur'])) {
    header('Location: connexion.php');
    exit();
}
$id_utilisateur = $_SESSION['id_utilisateur'];
$errors = [];

// 2. TRAITEMENT DES ACTIONS (FORMULAIRES)
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // ACTION : SOUMETTRE UNE NOUVELLE ANNONCE
    if (isset($_POST['submit_annonce'])) {
        // Récupération et nettoyage des données
        $titre = trim($_POST['titre']);
        $nom_sur_document = trim($_POST['nom_sur_document']);
        $id_categorie = $_POST['id_categorie'];
        $date_perte = $_POST['date_perte'];
        $lieu_perte = trim($_POST['lieu_perte']);
        $description = trim($_POST['description']);
        $photo = $_FILES['photo'];

        // Validation des champs
        if (empty($titre) || empty($nom_sur_document) || empty($id_categorie) || empty($date_perte) || empty($lieu_perte)) {
            $errors[] = "Tous les champs marqués d'une * sont obligatoires.";
        }
        // Validation de la photo
        if (isset($photo) && $photo['error'] === UPLOAD_ERR_OK) {
            $file_tmp_path = $photo['tmp_name'];
            $file_name = $photo['name'];
            $file_size = $photo['size'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
            if (!in_array($file_ext, $allowed_exts)) $errors[] = "Type de fichier non autorisé (JPG, JPEG, PNG, GIF).";
            if ($file_size > 4000000) $errors[] = "Le fichier est trop volumineux (max 4MB).";
        } else {
            $errors[] = "Une photo est requise pour valider l'annonce.";
        }

        // Si pas d'erreurs, on procède à l'enregistrement
        if (empty($errors)) {
            $new_file_name = 'doc_' . $id_utilisateur . '_' . time() . '.' . $file_ext;
            $dest_path = 'uploads/images/' . $new_file_name;

            if (move_uploaded_file($file_tmp_path, $dest_path)) {
                $sql = "INSERT INTO annonces (titre, description, type_annonce, date_perte_trouve, lieu_perte_trouve, nom_sur_document, photo_url, id_utilisateur, id_categorie) VALUES (?, ?, 'perdu', ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                try {
                    $stmt->execute([$titre, $description, $date_perte, $lieu_perte, $nom_sur_document, $dest_path, $id_utilisateur, $id_categorie]);
                    $_SESSION['dashboard_message'] = "Votre annonce a bien été soumise ! Elle est en attente de validation.";
                    // Redirection pour éviter la resoumission du formulaire
                    header('Location: dashboard.php');
                    exit();
                } catch (PDOException $e) {
                    $errors[] = "Erreur lors de l'enregistrement : " . $e->getMessage();
                    // En cas d'erreur, on supprime le fichier uploadé
                    if (file_exists($dest_path)) unlink($dest_path);
                }
            } else {
                $errors[] = "Erreur lors du téléversement de l'image.";
            }
        }
    }

    // ACTION : MARQUER COMME RETROUVÉ
    if (isset($_POST['marquer_retrouve'])) {
        $id_annonce_action = $_POST['id_annonce_action'];
        $sql = "UPDATE annonces SET statut_annonce = 'retrouve' WHERE id_annonce = ? AND id_utilisateur = ?";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$id_annonce_action, $id_utilisateur])) {
            $_SESSION['dashboard_message'] = "Félicitations ! Votre annonce a été marquée comme retrouvée.";
        } else {
            $errors[] = "Une erreur est survenue lors de la mise à jour.";
        }
        header('Location: dashboard.php');
        exit();
    }

    // ACTION : SUPPRIMER L'ANNONCE
    if (isset($_POST['supprimer_annonce'])) {
        $id_annonce_action = $_POST['id_annonce_action'];
        $sql = "UPDATE annonces SET statut_annonce = 'supprimee' WHERE id_annonce = ? AND id_utilisateur = ?";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$id_annonce_action, $id_utilisateur])) {
            $_SESSION['dashboard_message'] = "Votre annonce a été supprimée avec succès.";
        } else {
            $errors[] = "Une erreur est survenue lors de la suppression.";
        }
        header('Location: dashboard.php');
        exit();
    }
}

// 3. RÉCUPÉRATION DES DONNÉES POUR L'AFFICHAGE
$stmt_cat = $pdo->query("SELECT id_categorie, nom_categorie FROM categories ORDER BY nom_categorie ASC");
$categories = $stmt_cat->fetchAll();

$stmt_annonces = $pdo->prepare("
    SELECT a.*, c.nom_categorie 
    FROM annonces a 
    JOIN categories c ON a.id_categorie = c.id_categorie 
    WHERE a.id_utilisateur = ? AND a.statut_annonce != 'supprimee'
    ORDER BY a.date_creation DESC
");
$stmt_annonces->execute([$id_utilisateur]);
$mes_annonces = $stmt_annonces->fetchAll();

// NOUVEAU : Calcul des statistiques pour l'affichage
$stats = ['total' => 0, 'publiee' => 0, 'en_attente' => 0, 'retrouve' => 0];
foreach ($mes_annonces as $annonce) {
    $stats['total']++;
    if ($annonce['statut_annonce'] == 'publiee') $stats['publiee']++;
    if ($annonce['statut_annonce'] == 'en_attente_validation') $stats['en_attente']++;
    if ($annonce['statut_annonce'] == 'retrouve') $stats['retrouve']++;
}
?>


<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord - Findmi</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="nav-logo"><?= htmlspecialchars(SITE_NAME ?? 'Findmi') ?></a>
            <button class="menu-toggle" id="menuToggle" aria-label="Ouvrir le menu">
                <i class="fas fa-bars"></i>
            </button>
            <ul class="nav-links" id="navLinks">
                <li><a href="index.php"><?= htmlspecialchars(t('home')) ?></a></li>
                <?php if (isset($_SESSION['id_utilisateur'])): ?>
                    <li><a href="profil.php"><?= htmlspecialchars(t('profile')) ?></a></li>
                    <li><a href="historique_messages.php"><?= htmlspecialchars(t('my_messages')) ?></a></li>
                    <li><a href="logout.php"><?= htmlspecialchars(t('logout')) ?></a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <div class="dashboard-container">
        <header class="dashboard-header">
            <h1><?= htmlspecialchars(t('dashboard')) ?></h1>
            <p>Bonjour, <?= htmlspecialchars($_SESSION['email_utilisateur']) ?> ! <?= htmlspecialchars(t('profile')) ?> ici.</p>
        </header>

        <!-- Affichage des messages et erreurs -->
        <?php if (isset($_SESSION['dashboard_message'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_SESSION['dashboard_message']); ?></div>
        <?php unset($_SESSION['dashboard_message']);
        endif; ?>
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger"><?php foreach ($errors as $error): ?><p><?= htmlspecialchars($error) ?></p><?php endforeach; ?></div>
        <?php endif; ?>

        <div class="dashboard-grid">
            <aside class="dashboard-sidebar">
                <div class="stats-card">
                    <h3>Vos Statistiques</h3>
                    <div class="stats-grid">
                        <div class="stat-item"><span class="stat-number"><?= $stats['total'] ?></span><span class="stat-label">Annonces</span></div>
                        <div class="stat-item"><span class="stat-number"><?= $stats['publiee'] ?></span><span class="stat-label">Publiées</span></div>
                        <div class="stat-item"><span class="stat-number"><?= $stats['en_attente'] ?></span><span class="stat-label">En attente</span></div>
                        <div class="stat-item"><span class="stat-number"><?= $stats['retrouve'] ?></span><span class="stat-label">Retrouvées</span></div>
                    </div>
                </div>

                <div class="new-annonce-toggle">
                    <button id="toggleFormBtn" class="btn-primary"><i class="fas fa-plus-circle"></i> Déclarer une nouvelle perte</button>
                </div>

                <div id="newAnnonceForm" class="form-section hidden">
                    <h3>Nouvelle Déclaration</h3>
                    <form action="dashboard.php" method="POST" enctype="multipart/form-data">
                        <div class="form-group"><label for="titre">Titre *</label><input type="text" id="titre" name="titre" required></div>
                        <div class="form-group"><label for="nom_sur_document">Nom sur le document *</label><input type="text" id="nom_sur_document" name="nom_sur_document" required></div>
                        <div class="form-group"><label for="id_categorie">Type *</label><select id="id_categorie" name="id_categorie" required>
                                <option value="">-- Catégorie --</option><?php foreach ($categories as $categorie): ?><option value="<?= $categorie['id_categorie'] ?>"><?= htmlspecialchars($categorie['nom_categorie']) ?></option><?php endforeach; ?>
                            </select></div>
                        <div class="form-group"><label for="date_perte">Date de perte *</label><input type="date" id="date_perte" name="date_perte" required></div>
                        <div class="form-group full-width"><label for="lieu_perte">Lieu de perte *</label><input type="text" id="lieu_perte" name="lieu_perte" required></div>
                        <div class="form-group full-width"><label for="description">Description</label><textarea id="description" name="description" rows="3"></textarea></div>
                        <div class="form-group full-width"><label for="photo">Photo *</label><input type="file" id="photo" name="photo" required>
                            <div class="form-warning"><strong>Important :</strong> Masquez les données sensibles avant envoi.</div>
                        </div>
                        <button type="submit" name="submit_annonce" class="btn-primary">Soumettre ma déclaration</button>
                    </form>
                </div>
            </aside>

            <main class="dashboard-main">
                <div class="list-section">
                    <h3>Mes Annonces</h3>
                    <?php if (empty($mes_annonces)): ?>
                        <p class="empty-list-message">Vous n'avez aucune annonce pour le moment. Cliquez sur "Déclarer une nouvelle perte" pour commencer !</p>
                    <?php else: ?>
                        <?php foreach ($mes_annonces as $annonce): ?>
                            <div class="dash-annonce-card">
                                <img src="<?= htmlspecialchars($annonce['photo_url']) ?>" alt="Photo annonce" class="dash-annonce-img">
                                <div class="dash-annonce-content">
                                    <h4><?= htmlspecialchars($annonce['titre']) ?></h4>
                                    <p class="annonce-meta"><i class="fas fa-tag"></i> <?= htmlspecialchars($annonce['nom_categorie']) ?> | <i class="fas fa-calendar-alt"></i> <?= date('d/m/Y', strtotime($annonce['date_perte_trouve'])) ?></p>
                                    <div class="annonce-status-actions">
                                        <span class="statut-badge status-<?= str_replace('_', '-', $annonce['statut_annonce']) ?>">
                                            <?php echo ['en_attente_validation' => 'En attente', 'publiee' => 'Publiée', 'rejetee' => 'Rejetée', 'retrouve' => 'Retrouvé'][$annonce['statut_annonce']] ?? 'Inconnu'; ?>
                                        </span>
                                        <div class="actions-dropdown">
                                            <button class="actions-btn"><i class="fas fa-ellipsis-h"></i></button>
                                            <div class="dropdown-content">
                                                <?php if ($annonce['statut_annonce'] === 'publiee'): ?>
                                                    <form action="dashboard.php" method="POST" style="margin:0;"><input type="hidden" name="id_annonce_action" value="<?= $annonce['id_annonce'] ?>"><button type="submit" name="marquer_retrouve" class="dropdown-item success"><i class="fas fa-check-circle"></i> Marquer Retrouvé</button></form>
                                                <?php endif; ?>
                                                <a href="modifier_annonce.php?id=<?= $annonce['id_annonce'] ?>" class="dropdown-item edit"><i class="fas fa-pencil-alt"></i> Modifier</a>
                                                <form action="dashboard.php" method="POST" style="margin:0;" onsubmit="return confirmAction(event, 'Êtes-vous sûr de vouloir supprimer cette annonce ?');"><input type="hidden" name="id_annonce_action" value="<?= $annonce['id_annonce'] ?>"><button type="submit" name="supprimer_annonce" class="dropdown-item danger"><i class="fas fa-trash-alt"></i> Supprimer</button></form>
                                            </div>
                                        </div>
                                    </div>
                                    <?php if ($annonce['statut_annonce'] === 'rejetee' && !empty($annonce['motif_rejet'])): ?>
                                        <p class="rejection-reason"><i class="fas fa-exclamation-circle"></i> Motif du rejet : <?= htmlspecialchars($annonce['motif_rejet']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <?php include 'footer.php'; ?>
    <script src="js/script.js"></script>
</body>

</html>