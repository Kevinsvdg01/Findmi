<?php
// admin/historique.php
session_start();
require_once '../core/db_connect.php';

// 1. VÉRIFICATION DE LA SESSION AUTORITÉ
if (!isset($_SESSION['id_autorite'])) {
    header('Location: connexion.php');
    exit();
}
$nom_autorite = $_SESSION['nom_autorite'];

// 2. GESTION DES FILTRES ET DE LA PAGINATION
$filtre_statut = $_GET['statut'] ?? '';
$annonces_par_page = 15;
$page_actuelle = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page_actuelle - 1) * $annonces_par_page;

$sql_base = "
    FROM annonces a
    JOIN categories c ON a.id_categorie = c.id_categorie
    JOIN utilisateurs u ON a.id_utilisateur = u.id_utilisateur
    LEFT JOIN autorites aut ON a.id_validateur = aut.id_autorite
    WHERE a.statut_annonce IN ('publiee', 'rejetee')
";
$params = [];

if (!empty($filtre_statut)) {
    $sql_base .= " AND a.statut_annonce = ?";
    $params[] = $filtre_statut;
}

$sql_count = "SELECT COUNT(*) " . $sql_base;
$stmt_count = $pdo->prepare($sql_count);
$stmt_count->execute($params);
$total_annonces = $stmt_count->fetchColumn();
$total_pages = ceil($total_annonces / $annonces_par_page);

$sql_select = "
    SELECT a.id_annonce, a.titre, a.statut_annonce, a.date_validation, a.motif_rejet, 
           u.email AS email_utilisateur, aut.nom_autorite AS nom_validateur
" . $sql_base . " ORDER BY a.date_validation DESC LIMIT ? OFFSET ?";
$params_select = array_merge($params, [$annonces_par_page, $offset]);
$stmt_select = $pdo->prepare($sql_select);
$stmt_select->execute($params_select);
$historique_annonces = $stmt_select->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historique des validations - Findmi Admin</title>
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
                <a href="index.php"><i class="fas fa-hourglass-half"></i> Annonces en attente</a>
                <a href="historique.php" class="active"><i class="fas fa-history"></i> Historique</a>
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
                <h1>Historique des Validations</h1>
                <form action="historique.php" method="GET" class="filter-form">
                    <select name="statut" onchange="this.form.submit()">
                        <option value="">Tous les statuts</option>
                        <option value="publiee" <?= $filtre_statut == 'publiee' ? 'selected' : '' ?>>Approuvées</option>
                        <option value="rejetee" <?= $filtre_statut == 'rejetee' ? 'selected' : '' ?>>Rejetées</option>
                    </select>
                </form>
            </header>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th><th>Titre</th><th>Statut</th><th>Date Traitement</th>
                            <th>Validateur</th><th>Utilisateur</th><th>Détails</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($historique_annonces)): ?>
                            <tr>
                                <td colspan="7" style="text-align:center;">Aucun historique trouvé.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($historique_annonces as $annonce): ?>
                                <tr>
                                    <td data-label="ID">#<?= $annonce['id_annonce'] ?></td>
                                    <td data-label="Titre"><?= htmlspecialchars($annonce['titre']) ?></td>
                                    <td data-label="Statut">
                                        <span class="statut-badge-table status-<?= $annonce['statut_annonce'] ?>">
                                            <?= $annonce['statut_annonce'] == 'publiee' ? 'Approuvée' : 'Rejetée' ?>
                                        </span>
                                    </td>
                                    <td data-label="Date Traitement"><?= date('d/m/Y H:i', strtotime($annonce['date_validation'])) ?></td>
                                    <td data-label="Validateur"><?= htmlspecialchars($annonce['nom_validateur'] ?? 'N/A') ?></td>
                                    <td data-label="Utilisateur"><?= htmlspecialchars($annonce['email_utilisateur']) ?></td>
                                    <td data-label="Détails">
                                        <?php if ($annonce['statut_annonce'] == 'rejetee'): ?>
                                            <span class="motif-tooltip" title="<?= htmlspecialchars($annonce['motif_rejet']) ?>"><i class="fas fa-info-circle"></i> Motif</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?= $i ?>&statut=<?= $filtre_statut ?>" class="<?= $i == $page_actuelle ? 'active' : '' ?>"><?= $i ?></a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <script src="admin_script.js"></script>
</body>
</html>