<?php
//index principale
session_start();
require_once 'core/db_connect.php';

// Récupérer les 10 dernières annonces publiées
$stmt = $pdo->query("
    SELECT a.id_annonce, a.titre, a.date_perte_trouve, a.lieu_perte_trouve, a.photo_url, c.nom_categorie
    FROM annonces a
    JOIN categories c ON a.id_categorie = c.id_categorie
    WHERE a.statut_annonce = 'publiee'
    ORDER BY a.date_validation DESC
    LIMIT 10
");
$annonces_publiees = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Findmi - Retrouvez vos documents perdus</title>
    <!-- On lie le fichier CSS externe -->
    <link rel="stylesheet" href="css/style.css">
    <!-- Font Awesome pour les icônes -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <!-- Police de caractères moderne depuis Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

    <style>

        /* --- Annonces --- */
        .latest-annonces {
            padding: 4rem 0;
        }

        .annonces-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
        }

        .annonce-card {
            background: var(--white-color);
            border-radius: 10px;
            box-shadow: var(--shadow);
            overflow: hidden;
            text-decoration: none;
            color: var(--text-color);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .annonce-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);
        }

        .card-image-wrapper {
            height: 200px;
            overflow: hidden;
        }

        .card-image-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .annonce-card:hover .card-image-wrapper img {
            transform: scale(1.1);
        }

        .card-content {
            padding: 1.5rem;
        }

        .card-content h3 {
            margin: 0 0 1rem;
            color: var(--primary-color);
        }

        .card-content p {
            margin: 0.5rem 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #666;
        }

        /* --- Section "Comment ça marche ?" --- */
        .how-it-works-section {
            background: var(--white-color);
            padding: 4rem 1rem;
        }

        .steps-container {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            gap: 2rem;
        }

        .step {
            text-align: center;
            max-width: 300px;
        }

        .step-icon {
            font-size: 2.5rem;
            color: var(--secondary-color);
            background-color: #eaf2ff;
            width: 80px;
            height: 80px;
            line-height: 80px;
            border-radius: 50%;
            margin: 0 auto 1.5rem;
            transition: transform 0.3s, background-color 0.3s;
        }

        .step:hover .step-icon {
            transform: scale(1.1) rotate(15deg);
            background-color: #dbe8ff;
        }

        /* --- Section CTA avec fond animé --- */
        .cta-section.with-floating-background {
            position: relative;
            /* Contexte de positionnement pour les éléments flottants */
            overflow: hidden;
            /* Empêche les icônes de déborder */
            background: linear-gradient(135deg,  #0057b3b2, #007bff50);
            color: #000;
            padding: 4rem 1rem;
            text-align: center;
        }

        .floating-container {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
            /* Derrière le contenu */
        }

        .floating-item {
            position: absolute;
            background-color: rgba(255, 255, 255, 0.1);
            color: var(--white-color);
            padding: 10px 20px;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 600;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 0 15px rgba(255, 255, 255, 0.1);
            /* L'animation est appliquée ici */
            animation: float 20s infinite ease-in-out;
            user-select: none;
            /* Empêche la sélection du texte des icônes */
            pointer-events: none;
            /* Les icônes ne sont pas cliquables */
        }

        .floating-item i {
            font-size: 1.5rem;
            /* Icônes un peu plus grandes */
        }

        /* Le contenu doit être au-dessus des icônes flottantes */
        .cta-content {
            position: relative;
            z-index: 2;
        }

        .cta-button {
            font-size: 15px;
            text-decoration: none;
            background-color: #dc3546bb;
            padding: 15px;
            border-radius: 15px;
            color: white;
            font-weight: bold;
        }

        .cta-button:hover {
            font-size: 20px;
            background-color: #dc3545;
        }

        /* L'animation de flottement */
        @keyframes float {
            0% {
                transform: translateY(0px) translateX(0px) rotate(0deg);
            }

            25% {
                transform: translateY(-20px) translateX(15px) rotate(5deg);
            }

            50% {
                transform: translateY(10px) translateX(-10px) rotate(-3deg);
            }

            75% {
                transform: translateY(-15px) translateX(-20px) rotate(4deg);
            }

            100% {
                transform: translateY(0px) translateX(0px) rotate(0deg);
            }
        }

        /* --- Animations et Responsive --- */
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in-section {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.8s ease-out, transform 0.8s ease-out;
        }

        .fade-in-section.is-visible {
            opacity: 1;
            transform: translateY(0);
        }

    </style>
</head>

<body>

    <!-- On garde votre barre de navigation qui est très bien structurée -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="nav-logo">Findmi</a>
            <button class="menu-toggle" id="menuToggle" aria-label="Ouvrir le menu">
                <i class="fas fa-bars"></i>
            </button>
            <ul class="nav-links" id="navLinks">
                <li><a href="dashboard.php">Tableau de bord</a></li>
                <?php if (isset($_SESSION['id_utilisateur'])): ?>
                    <li><a href="profil.php">Profil</a></li>
                    <li><a href="logout.php">Déconnexion</a></li>
                <?php else: ?>
                    <li><a href="connexion.php">Connexion</a></li>
                <?php endif; ?>
                <li><a href="apropos.php">À Propos</a></li>
                <li><a href="contact.php">Contact</a></li>
            </ul>
        </div>
    </nav>

    <header class="hero-section">
        <div class="hero-content">
            <img src="/images/logofindmi.jpg" alt="Logo Findmi" class="hero-logo">
            <h1 class="hero-title">Ne perdez plus espoir, retrouvez-les.</h1>
            <p class="hero-subtitle">La plateforme citoyenne pour retrouver vos documents et proches.</p>
            <div class="search-bar">
                <form action="recherche.php" method="GET">
                    <input type="text" name="q" placeholder="Rechercher une CNI, un passeport...">
                    <button type="submit" aria-label="Rechercher"><i class="fas fa-search"></i></button>
                </form>
            </div>
        </div>
    </header>

    <main class="main-container">
        <section class="latest-annonces fade-in-section">
            <h2 class="section-title">Dernières annonces publiées</h2>
            <div class="annonces-grid">
                <?php if (empty($annonces_publiees)): ?>
                    <p>Aucune annonce n'a été publiée pour le moment.</p>
                <?php else: ?>
                    <?php foreach ($annonces_publiees as $annonce): ?>
                        <a href="annonce_detail.php?id=<?= $annonce['id_annonce'] ?>" class="annonce-card">
                            <div class="card-image-wrapper">
                                <img src="<?= htmlspecialchars($annonce['photo_url']) ?>" alt="Photo de l'annonce">
                            </div>
                            <div class="card-content">
                                <h3><?= htmlspecialchars($annonce['titre']) ?></h3>
                                <p><i class="fas fa-tag"></i> <?= htmlspecialchars($annonce['nom_categorie']) ?></p>
                                <p><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($annonce['lieu_perte_trouve']) ?></p>
                                <p><i class="fas fa-calendar-alt"></i> Perdu le: <?= date('d/m/Y', strtotime($annonce['date_perte_trouve'])) ?></p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- NOUVELLE SECTION : Comment ça marche ? -->
        <section class="how-it-works-section fade-in-section">
            <h2 class="section-title">C'est simple comme bonjour</h2>
            <div class="steps-container">
                <div class="step">
                    <div class="step-icon"><i class="fas fa-bullhorn"></i></div>
                    <h3>1. Déclarez la perte</h3>
                    <p>Créez une annonce en quelques clics avec les détails du document perdu.</p>
                </div>
                <div class="step">
                    <div class="step-icon"><i class="fas fa-bell"></i></div>
                    <h3>2. Recevez une notification</h3>
                    <p>Notre communauté et les autorités sont alertées et peuvent vous contacter.</p>
                </div>
                <div class="step">
                    <div class="step-icon"><i class="fas fa-handshake"></i></div>
                    <h3>3. Retrouvez votre bien</h3>
                    <p>Mise en relation sécurisée pour récupérer votre document rapidement.</p>
                </div>
            </div>
        </section>

        <section class="cta-section with-floating-background">
            <!-- Conteneur pour les icônes flottantes -->
            <div class="floating-container">
                <div class="floating-item"><i class="fas fa-search"></i></div>
                <div class="floating-item">Passeport</div>
                <div class="floating-item"><i class="fas fa-id-card"></i></div>
                <div class="floating-item">Retrouver</div>
                <div class="floating-item"><i class="fas fa-handshake-angle"></i></div>
                <div class="floating-item">Entraide</div>
                <div class="floating-item"><i class="fas fa-map-marker-alt"></i></div>
                <div class="floating-item">Espoir</div>
                <div class="floating-item"><i class="fas fa-shield-alt"></i></div>
                <div class="floating-item">Findmi</div>
            </div>

            <!-- Le contenu de la section, qui s'affichera par-dessus -->
            <div class="cta-content">
                <h2>Vous avez trouvé un document ?</h2>
                <p>Devenez un héros du quotidien. Aidez une personne à retrouver le sourire.</p>
                <a href="dashboard.php" class="cta-button">Déclarer une trouvaille</a>
            </div>
        </section>

    </main>

    <?php include 'footer.php'; ?>

    <!-- Le script JS juste avant la fin du body pour de meilleures performances -->
    <script src="js/script.js"></script>
</body>

</html>