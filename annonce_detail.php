<?php
session_start();
require_once 'core/db_connect.php';

// 1. Vérifier si un ID est passé en paramètre et s'il est valide
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    // Si pas d'ID, on redirige vers l'accueil
    header('Location: index.php');
    exit();
}
$id_annonce = $_GET['id'];

// 2. Récupérer les détails de l'annonce demandée
// On s'assure de récupérer SEULEMENT si le statut est 'publiee'
$stmt = $pdo->prepare("
    SELECT a.*, c.nom_categorie
    FROM annonces a
    JOIN categories c ON a.id_categorie = c.id_categorie
    WHERE a.id_annonce = ? AND a.statut_annonce = 'publiee'
");
$stmt->execute([$id_annonce]);
$annonce = $stmt->fetch();

// 3. Si aucune annonce n'est trouvée (ID invalide ou non publiée), on redirige
if (!$annonce) {
    // On peut ajouter un message d'erreur si on veut
    // $_SESSION['error_message'] = "Annonce non trouvée ou non publiée.";
    header('Location: index.php');
    exit();
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détail de l'annonce : <?= htmlspecialchars($annonce['titre']) ?> - <?= SITE_NAME ?? 'Findmi' ?></title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body { display: block; background-color: #f4f4f9; }
        .navbar { background-color: #0056b3; padding: 1rem; color: white; display:flex; justify-content: space-between; align-items: center; }
        .navbar a { color: white; text-decoration: none; }
        .container { max-width: 900px; margin: 2rem auto; }
        .annonce-detail-card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: grid;
            grid-template-columns: 1fr 2fr;
            overflow: hidden;
        }
        .annonce-detail-photo {
            padding: 0;
            margin: 0;
        }
        .annonce-detail-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .annonce-detail-content {
            padding: 2rem;
        }
        .annonce-detail-content h1 {
            color: #0056b3;
            margin-top: 0;
        }
        .annonce-detail-content ul {
            list-style: none;
            padding: 0;
            margin: 2rem 0;
        }
        .annonce-detail-content ul li {
            padding: 0.75rem 0;
            border-bottom: 1px solid #eee;
            font-size: 1.1rem;
        }
        .contact-info {
            background: #e9ecef;
            padding: 1.5rem;
            border-radius: 5px;
            text-align: center;
        }
        .contact-info p { margin: 0.5rem 0; }
        .contact-info .btn { margin-top: 1rem; }
    </style>
</head>
<body>

    <nav class="navbar">
        <h1><a href="index.php"><?= htmlspecialchars(SITE_NAME ?? 'Findmi') ?></a></h1>
        <div>
            <?php if (isset($_SESSION['id_utilisateur'])): ?>
                <a href="dashboard.php">Mon tableau de bord</a>
                <a href="logout.php" style="margin-left: 15px;">Déconnexion</a>
            <?php else: ?>
                <a href="connexion.php">Connexion / Inscription</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="container">
        <div class="annonce-detail-card">
            <div class="annonce-detail-photo">
                <img src="<?= htmlspecialchars($annonce['photo_url']) ?>" alt="Photo de l'annonce">
            </div>
            <div class="annonce-detail-content">
                <h1><?= htmlspecialchars($annonce['titre']) ?></h1>
                
                <ul>
                    <li><strong>Nom sur le document:</strong> <?= htmlspecialchars($annonce['nom_sur_document']) ?></li>
                    <li><strong>Catégorie:</strong> <?= htmlspecialchars($annonce['nom_categorie']) ?></li>
                    <li><strong>Lieu de perte:</strong> <?= htmlspecialchars($annonce['lieu_perte_trouve']) ?></li>
                    <li><strong>Date de perte:</strong> <?= date('d/m/Y', strtotime($annonce['date_perte_trouve'])) ?></li>
                    <?php if (!empty($annonce['description'])): ?>
                        <li><strong>Description:</strong><br><?= nl2br(htmlspecialchars($annonce['description'])) ?></li>
                    <?php endif; ?>
                </ul>

                <div class="contact-info">
                    <p>Vous avez trouvé ce document ? Vous reconnaissez cette personne ?</p>
                    <p>Pour des raisons de sécurité, la mise en relation se fait via la plateforme.</p>
                    <?php if (isset($_SESSION['id_utilisateur'])): ?>
                        <a href="messagerie.php?id_annonce=<?= htmlspecialchars($id_annonce) ?>" class="btn">Contacter le déclarant</a>
                    <?php else: ?>
                        <a href="connexion.php" class="btn">Connectez-vous pour contacter le déclarant</a>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>


    <?php include 'footer.php'; ?>

</body>
</html>