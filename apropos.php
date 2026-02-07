<?php
$pageTitle = "À propos - Findmi";
session_start();
require_once 'core/db_connect.php';

// Exemple sécurisé pour compter les annonces
$stmt = $pdo->query("SELECT COUNT(*) AS total FROM annonces");
$annonces = $stmt->fetch();
$total_annonces = $annonces['total'];
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>

    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f7f9fc;
            color: #333;
            margin: 0;
        }

        /* HERO */
        .about-hero {
            background: linear-gradient(135deg, #4f46e5, #6366f1);
            color: white;
            padding: 80px 20px;
            text-align: center;
        }

        .about-hero h1 {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }

        .about-hero p {
            max-width: 700px;
            margin: auto;
            opacity: 0.95;
        }

        /* SECTIONS */
        .about-section {
            padding: 60px 20px;
        }

        .about-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            max-width: 1100px;
            margin: auto;
        }

        .about-box {
            background: white;
            padding: 30px;
            border-radius: 14px;
            text-align: center;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s;
        }

        .about-box:hover {
            transform: translateY(-6px);
        }

        .about-box i {
            font-size: 2rem;
            color: #4f46e5;
            margin-bottom: 15px;
        }

        /* FEATURES */
        .about-features {
            background: #fff;
            padding: 60px 20px;
        }

        .about-features h2 {
            text-align: center;
            margin-bottom: 40px;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 25px;
            max-width: 1100px;
            margin: auto;
        }

        .feature-card {
            background: #f7f9fc;
            padding: 25px;
            border-radius: 12px;
            text-align: center;
        }

        .feature-card i {
            font-size: 1.8rem;
            color: #4f46e5;
            margin-bottom: 10px;
        }

        /* STATS */
        .about-stats {
            background: #4f46e5;
            color: white;
            padding: 50px 20px;
        }

        .stats-container {
            display: flex;
            justify-content: center;
            gap: 40px;
            flex-wrap: wrap;
        }

        .stat-item h3 {
            font-size: 2rem;
            margin-bottom: 5px;
        }
    </style>
</head>

<body>

    <!-- NAVBAR -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="nav-logo">Findmi</a>
            <ul class="nav-links">
                <li><a href="index.php">Accueil</a></li>
                <li><a href="dashboard.php">Annonces</a></li>
                <li><a href="apropos.php" class="active">À propos</a></li>
                <li><a href="contact.php">Contact</a></li>

                <?php if (isset($_SESSION['id_utilisateur'])): ?>
                    <li><a href="profil.php">Profil</a></li>
                    <li><a href="logout.php" class="btn-logout">Déconnexion</a></li>
                <?php else: ?>
                    <li><a href="connexion.php" class="btn-login">Connexion</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <!-- HERO -->
    <header class="about-hero">
        <div class="hero-content">
            <h1>À propos de Findmi</h1>
            <p>
                Findmi est une plateforme moderne dédiée à la publication et à la recherche d’annonces locales.
                Notre objectif est de rapprocher les personnes, les services et les opportunités, simplement.
            </p>
        </div>
    </header>

    <!-- MISSION / VISION -->
    <section class="about-section">
        <div class="about-grid">
            <div class="about-box">
                <i class="fas fa-bullseye"></i>
                <h3>Notre mission</h3>
                <p>
                    Faciliter la recherche et la diffusion d’annonces en mettant en avant la proximité,
                    la simplicité et la fiabilité.
                </p>
            </div>

            <div class="about-box">
                <i class="fas fa-eye"></i>
                <h3>Notre vision</h3>
                <p>
                    Devenir une référence locale pour connecter efficacement les utilisateurs
                    aux opportunités qui les entourent.
                </p>
            </div>

            <div class="about-box">
                <i class="fas fa-handshake"></i>
                <h3>Nos valeurs</h3>
                <p>
                    Confiance, transparence, accessibilité et innovation sont au cœur de notre engagement.
                </p>
            </div>
        </div>
    </section>

    <!-- FONCTIONNALITÉS -->
    <section class="about-features">
        <h2>Pourquoi choisir Findmi ?</h2>
        <div class="features-grid">
            <div class="feature-card">
                <i class="fas fa-location-dot"></i>
                <h4>Proximité</h4>
                <p>Des annonces géolocalisées pour trouver rapidement ce qui est proche de vous.</p>
            </div>

            <div class="feature-card">
                <i class="fas fa-lock"></i>
                <h4>Sécurité</h4>
                <p>Protection des données et contrôle des annonces pour une expérience fiable.</p>
            </div>

            <div class="feature-card">
                <i class="fas fa-bolt"></i>
                <h4>Simplicité</h4>
                <p>Une interface claire et intuitive accessible à tous.</p>
            </div>

            <div class="feature-card">
                <i class="fas fa-users"></i>
                <h4>Communauté</h4>
                <p>Une communauté active qui partage et collabore au quotidien.</p>
            </div>
        </div>
    </section>

    <!-- STATISTIQUES -->
    <section class="about-stats">
        <div class="stats-container">
            <div class="stat-item">
                <h3><?= $total_annonces ?>+</h3>
                <p>Annonces publiées</p>
            </div>
            <div class="stat-item">
                <h3>5 000+</h3>
                <p>Utilisateurs actifs</p>
            </div>
            <div class="stat-item">
                <h3>99%</h3>
                <p>Taux de satisfaction</p>
            </div>
        </div>
    </section>

    <?php include 'footer.php'; ?>

</body>

</html>