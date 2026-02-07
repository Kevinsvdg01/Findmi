<?php
session_start();
require_once 'core/db_connect.php';

// 1. Récupérer et valider le terme de recherche
// On s'assure qu'une recherche a bien été lancée et qu'elle n'est pas vide
$search_query = '';
if (isset($_GET['q']) && !empty(trim($_GET['q']))) {
    $search_query = trim($_GET['q']);

    // 2. Préparer la requête SQL de recherche
    // On utilise LIKE avec des '%' pour chercher le terme n'importe où dans les champs
    $sql = "
        SELECT a.id_annonce, a.titre, a.date_perte_trouve, a.lieu_perte_trouve, a.photo_url, c.nom_categorie
        FROM annonces a
        JOIN categories c ON a.id_categorie = c.id_categorie
        WHERE a.statut_annonce = 'publiee'
        AND (
            a.titre LIKE ? 
            OR a.nom_sur_document LIKE ? 
            OR a.lieu_perte_trouve LIKE ?
            OR c.nom_categorie LIKE ?
        )
        ORDER BY a.date_validation DESC
    ";
    
    $stmt = $pdo->prepare($sql);
    
    // On ajoute les '%' autour du terme de recherche pour le `LIKE`
    $search_term = "%" . $search_query . "%";
    
    // On exécute la requête en passant le même terme pour chaque '?'
    $stmt->execute([$search_term, $search_term, $search_term, $search_term]);
    
    $resultats = $stmt->fetchAll();

} else {
    // Si la recherche est vide, on initialise un tableau de résultats vide
    $resultats = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résultats de recherche pour "<?= htmlspecialchars($search_query) ?>" - Findmi</title>
    <link rel="stylesheet" href="css/style.css">
    <!-- On réutilise les mêmes styles que pour la page d'accueil -->
    <style>
        body { display: block; }
        .header {
            background-color: #0056b3;
            color: white;
            padding: 2rem 1rem; /* Moins haut que la page d'accueil */
            text-align: center;
        }
        .header h1 { margin: 0; font-size: 2.5rem; }
        .search-bar { margin-top: 1rem; }
        .search-bar input[type="text"] {
            width: 50%;
            padding: 1rem;
            font-size: 1rem;
            border: none;
            border-radius: 5px 0 0 5px;
        }
        .search-bar button {
            padding: 1rem 2rem;
            font-size: 1rem;
            border: none;
            background-color: #ffc107;
            color: #333;
            cursor: pointer;
            border-radius: 0 5px 5px 0;
            margin-left: -5px;
        }
        .container { max-width: 1200px; margin: 2rem auto; padding: 0 1rem; }
        .results-header {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #eee;
        }
        .annonces-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        .annonce-card-public {
            background: #fff;
            border: 1px solid #eee;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .annonce-card-public:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .annonce-card-public img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .annonce-card-content {
            padding: 1rem;
        }
        .annonce-card-content h3 { margin: 0 0 0.5rem 0; font-size: 1.2rem; color: #0056b3; }
        .annonce-card-content p { margin: 0.2rem 0; color: #666; }
        .annonce-card-public a { text-decoration: none; color: inherit; }
    </style>
</head>
<body>

    <header class="header">
        <h1><a href="index.php" style="color:white; text-decoration:none;">Findmi</a></h1>
        <div class="search-bar">
            <!-- On affiche la barre de recherche ici aussi pour permettre une nouvelle recherche -->
            <form action="recherche.php" method="GET">
                <input type="text" name="q" placeholder="Affiner votre recherche..." value="<?= htmlspecialchars($search_query) ?>">
                <button type="submit">Rechercher</button>
            </form>
        </div>
    </header>

    <main class="container">
        <div class="results-header">
            <?php if (!empty($search_query)): ?>
                <h2>Résultats de recherche pour : "<?= htmlspecialchars($search_query) ?>"</h2>
                <p><strong><?= count($resultats) ?></strong> annonce(s) trouvée(s).</p>
            <?php else: ?>
                <h2>Veuillez entrer un terme à rechercher</h2>
            <?php endif; ?>
        </div>
        
        <div class="annonces-grid">
            <?php if (!empty($resultats)): ?>
                <?php foreach ($resultats as $annonce): ?>
                    <a href="annonce_detail.php?id=<?= $annonce['id_annonce'] ?>" class="annonce-card-public">
                        <img src="<?= htmlspecialchars($annonce['photo_url']) ?>" alt="Photo de l'annonce">
                        <div class="annonce-card-content">
                            <h3><?= htmlspecialchars($annonce['titre']) ?></h3>
                            <p><strong>Catégorie:</strong> <?= htmlspecialchars($annonce['nom_categorie']) ?></p>
                            <p><strong>Lieu:</strong> <?= htmlspecialchars($annonce['lieu_perte_trouve']) ?></p>
                            <p><strong>Perdu le:</strong> <?= date('d/m/Y', strtotime($annonce['date_perte_trouve'])) ?></p>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php elseif (!empty($search_query)): ?>
                <p>Aucun résultat ne correspond à votre recherche. Essayez avec d'autres mots-clés.</p>
            <?php endif; ?>
        </div>
    </main>

</body>
</html>