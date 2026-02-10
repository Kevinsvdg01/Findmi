<?php
session_start();
require_once 'core/db_connect.php';

// 1. VÉRIFICATION DE LA SESSION UTILISATEUR
if (!isset($_SESSION['id_utilisateur'])) {
    header('Location: connexion.php');
    exit();
}
$id_utilisateur = $_SESSION['id_utilisateur'];

// 2. VÉRIFICATION DE L'ID DE L'ANNONCE
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['dashboard_message'] = "Erreur : Annonce non spécifiée.";
    header('Location: dashboard.php');
    exit();
}
$id_annonce = $_GET['id'];

// 3. RÉCUPÉRATION DES DONNÉES DE L'ANNONCE À MODIFIER
// On vérifie que l'annonce existe ET qu'elle appartient à l'utilisateur connecté
$stmt = $pdo->prepare("SELECT * FROM annonces WHERE id_annonce = ? AND id_utilisateur = ?");
$stmt->execute([$id_annonce, $id_utilisateur]);
$annonce = $stmt->fetch();

if (!$annonce) {
    $_SESSION['dashboard_message'] = "Erreur : Annonce introuvable ou vous n'avez pas la permission de la modifier.";
    header('Location: dashboard.php');
    exit();
}

$errors = [];

// 4. TRAITEMENT DU FORMULAIRE DE MODIFICATION
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupération des données
    $titre = trim($_POST['titre']);
    $nom_sur_document = trim($_POST['nom_sur_document']);
    $id_categorie = $_POST['id_categorie'];
    $date_perte = $_POST['date_perte'];
    $lieu_perte = trim($_POST['lieu_perte']);
    $description = trim($_POST['description']);

    // Validation (similaire à la création)
    if (empty($titre) || empty($nom_sur_document) || empty($id_categorie) || empty($date_perte) || empty($lieu_perte)) {
        $errors[] = "Tous les champs marqués d'une * sont obligatoires.";
    }

    if (empty($errors)) {
        // Logique de mise à jour
        $sql = "UPDATE annonces SET 
                    titre = ?, 
                    nom_sur_document = ?, 
                    id_categorie = ?, 
                    date_perte_trouve = ?, 
                    lieu_perte_trouve = ?, 
                    description = ?,
                    statut_annonce = 'en_attente_validation', -- L'annonce doit être re-validée
                    motif_rejet = NULL -- On efface un éventuel motif de rejet précédent
                WHERE id_annonce = ? AND id_utilisateur = ?";

        $stmt = $pdo->prepare($sql);
        try {
            $stmt->execute([$titre, $nom_sur_document, $id_categorie, $date_perte, $lieu_perte, $description, $id_annonce, $id_utilisateur]);
            $_SESSION['dashboard_message'] = "Votre annonce a été modifiée et soumise à une nouvelle validation.";
            header('Location: dashboard.php');
            exit();
        } catch (PDOException $e) {
            $errors[] = "Erreur lors de la mise à jour de l'annonce : " . $e->getMessage();
        }
    }
}

// Récupérer les catégories pour le formulaire (comme dans le dashboard)
$stmt_cat = $pdo->query("SELECT id_categorie, nom_categorie FROM categories ORDER BY nom_categorie ASC");
$categories = $stmt_cat->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier l'annonce - Findmi</title>
    <link rel="stylesheet" href="css/style.css">
    <!-- On réutilise les styles du dashboard -->
    <style>
        body {
            display: block;
            background-color: #f4f4f9;
        }

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
        }

        .container {
            max-width: 900px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .form-section {
            background: #fff;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #0056b3;
            border-bottom: 2px solid #f4f4f9;
            padding-bottom: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-info {
            background: #e2e3e5;
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 2rem;
        }

        .btn2 {
            width: 100%;
            padding: 12px;
            background-color: brown;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
        }

        .btn2:hover {
            background-color: #8b3434ff;
        }
    </style>
</head>

<body>

    <nav class="navbar">
        <h1><a href="index.php"><?= htmlspecialchars(SITE_NAME ?? 'Findmi') ?></a></h1>
        <div>
            <a href="dashboard.php">Retour au tableau de bord</a>
        </div>
    </nav>

    <div class="container">
        <div class="form-section">
            <h2>Modifier mon annonce</h2>

            <?php if (!empty($errors)): ?>
                <div class="errors" style="margin-bottom: 1rem;">
                    <?php foreach ($errors as $error): ?><p><?= htmlspecialchars($error) ?></p><?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="form-info">
                <p><strong>Note :</strong> La modification de votre annonce la soumettra à une nouvelle validation par les autorités. La photo ne peut pas être modifiée. Si vous devez changer la photo, veuillez supprimer cette annonce et en créer une nouvelle.</p>
            </div>

            <form action="modifier_annonce.php?id=<?= $id_annonce ?>" method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="titre">Titre de l'annonce *</label>
                        <input type="text" id="titre" name="titre" value="<?= htmlspecialchars($annonce['titre']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="nom_sur_document">Nom complet sur le document *</label>
                        <input type="text" id="nom_sur_document" name="nom_sur_document" value="<?= htmlspecialchars($annonce['nom_sur_document']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="id_categorie">Type de document *</label>
                        <select id="id_categorie" name="id_categorie" required>
                            <option value="">-- Choisissez une catégorie --</option>
                            <?php foreach ($categories as $categorie): ?>
                                <option value="<?= $categorie['id_categorie'] ?>" <?= ($annonce['id_categorie'] == $categorie['id_categorie']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($categorie['nom_categorie']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="date_perte">Date de la perte *</label>
                        <input type="date" id="date_perte" name="date_perte" value="<?= htmlspecialchars($annonce['date_perte_trouve']) ?>" required>
                    </div>
                    <div class="form-group full-width">
                        <label for="lieu_perte">Lieu approximatif de la perte *</label>
                        <input type="text" id="lieu_perte" name="lieu_perte" value="<?= htmlspecialchars($annonce['lieu_perte_trouve']) ?>" required>
                    </div>
                    <div class="form-group full-width">
                        <label for="description">Description (circonstances, détails...)</label>
                        <textarea id="description" name="description" rows="4"><?= htmlspecialchars($annonce['description']) ?></textarea>
                    </div>
                </div>
                <button type="submit" class="btn" style="margin-top: 1rem;">Enregistrer les modifications</button>
                <button class="btn2" onclick="window.location.href='dashboard.php'" style="margin-top: 1rem;">Annuler</button>
            </form>
        </div>
    </div>

</body>

</html>