<?php
session_start();
require_once 'core/db_connect.php';

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['id_utilisateur'])) {
    header('Location: connexion.php');
    exit();
}

$id_utilisateur = $_SESSION['id_utilisateur'];
$messages = [];
$errors = [];

// Récupérer les informations de l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id_utilisateur = ?");
$stmt->execute([$id_utilisateur]);
$utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$utilisateur) {
    header('Location: logout.php');
    exit();
}

// Traiter la modification du profil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');

    // Validation
    if (empty($nom)) {
        $errors[] = "Le nom ne peut pas être vide.";
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email invalide.";
    }
    if (empty($telephone)) {
        $errors[] = "Le téléphone ne peut pas être vide.";
    }

    // Vérifier que l'email n'existe pas pour un autre utilisateur
    if (empty($errors) && $email !== $utilisateur['email']) {
        $stmt = $pdo->prepare("SELECT id_utilisateur FROM utilisateurs WHERE email = ? AND id_utilisateur != ?");
        $stmt->execute([$email, $id_utilisateur]);
        if ($stmt->fetch()) {
            $errors[] = "Cet email est déjà utilisé par un autre compte.";
        }
    }

    // Si pas d'erreurs, mettre à jour
    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE utilisateurs SET nom = ?, email = ?, telephone = ? WHERE id_utilisateur = ?");
        if ($stmt->execute([$nom, $email, $telephone, $id_utilisateur])) {
            $messages[] = "✅ Profil mis à jour avec succès.";
            $utilisateur['nom'] = $nom;
            $utilisateur['email'] = $email;
            $utilisateur['telephone'] = $telephone;
        } else {
            $errors[] = "Erreur lors de la mise à jour du profil.";
        }
    }
}

// Traiter le changement de mot de passe
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $ancien_pwd = $_POST['ancien_mdp'] ?? '';
    $nouveau_pwd = $_POST['nouveau_mdp'] ?? '';
    $confirmer_pwd = $_POST['confirmer_mdp'] ?? '';

    // Validation
    if (empty($ancien_pwd)) {
        $errors[] = "Veuillez entrer votre ancien mot de passe.";
    } elseif (!password_verify($ancien_pwd, $utilisateur['mot_de_passe'])) {
        $errors[] = "L'ancien mot de passe est incorrect.";
    }

    if (empty($nouveau_pwd) || strlen($nouveau_pwd) < 6) {
        $errors[] = "Le nouveau mot de passe doit contenir au minimum 6 caractères.";
    }

    if ($nouveau_pwd !== $confirmer_pwd) {
        $errors[] = "Les mots de passe ne correspondent pas.";
    }

    // Si pas d'erreurs, mettre à jour le mot de passe
    if (empty($errors)) {
        $hashed_pwd = password_hash($nouveau_pwd, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("UPDATE utilisateurs SET mot_de_passe = ? WHERE id_utilisateur = ?");
        if ($stmt->execute([$hashed_pwd, $id_utilisateur])) {
            $messages[] = "✅ Mot de passe modifié avec succès.";
        } else {
            $errors[] = "Erreur lors de la modification du mot de passe.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - <?= SITE_NAME ?? 'Findmi' ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        :root {
            --primary: #0ea5a4;
            --primary-dark: #0d9395;
            --secondary: #f0f9f9;
            --danger: #ef4444;
            --success: #10b981;
            --text-dark: #1f2937;
            --text-light: #6b7280;
            --border: #e5e7eb;
            --shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            --shadow-lg: 0 20px 40px rgba(0, 0, 0, 0.12);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #f0f9f9 0%, #f9fafb 100%);
            color: var(--text-dark);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            min-height: 100vh;
        }

        /* NAVBAR */
        .navbar {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            padding: 1rem 2%;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow);
        }

        .navbar h1 {
            font-size: 1.5rem;
            font-weight: 700;
        }

        .navbar h1 a {
            color: white;
            text-decoration: none;
        }

        .navbar div {
            display: flex;
            gap: 1.5rem;
            align-items: center;
        }

        .navbar a {
            color: white;
            text-decoration: none;
            font-size: 0.95rem;
            transition: opacity 0.3s;
        }

        .navbar a:hover {
            opacity: 0.8;
        }

        /* MAIN CONTAINER */
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        /* PROFILE WRAPPER */
        .profile-wrapper {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 2rem;
            margin-bottom: 3rem;
        }

        /* SIDEBAR */
        .profile-sidebar {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: var(--shadow);
            height: fit-content;
            position: sticky;
            top: 20px;
        }

        .sidebar-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid var(--border);
        }

        .sidebar-avatar {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary) 0%, #14b8a6 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
        }

        .sidebar-info h3 {
            font-size: 0.95rem;
            margin-bottom: 0.25rem;
            color: var(--text-dark);
        }

        .sidebar-info p {
            font-size: 0.8rem;
            color: var(--text-light);
        }

        .profile-sidebar nav {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .nav-btn {
            padding: 0.9rem 1rem;
            border: none;
            background: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.95rem;
            text-align: left;
            color: var(--text-dark);
            transition: all 0.3s ease;
            font-family: inherit;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .nav-btn:hover {
            background: var(--secondary);
            color: var(--primary);
        }

        .nav-btn.active {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            box-shadow: 0 2px 8px rgba(14, 165, 164, 0.3);
        }

        .nav-btn i {
            font-size: 1.1rem;
            width: 20px;
        }

        /* MAIN CONTENT */
        .profile-main {
            background: white;
            border-radius: 12px;
            padding: 2.5rem;
            box-shadow: var(--shadow);
        }

        .profile-section {
            display: none;
            animation: fadeIn 0.3s ease;
        }

        .profile-section.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .section-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid var(--border);
        }

        .section-header h2 {
            font-size: 1.5rem;
            color: var(--text-dark);
            margin: 0;
        }

        .section-header i {
            font-size: 1.8rem;
            color: var(--primary);
        }

        /* ALERTS */
        .alert {
            padding: 1rem 1.25rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            border-left: 4px solid;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateX(-20px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .alert.success {
            background: #ecfdf5;
            color: #065f46;
            border-color: var(--success);
        }

        .alert.error {
            background: #fef2f2;
            color: #7f1d1d;
            border-color: var(--danger);
        }

        .alert ul {
            list-style: none;
            margin: 0;
        }

        .alert li {
            margin: 0.5rem 0;
        }

        /* FORMS */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.6rem;
            font-weight: 600;
            color: var(--text-dark);
            font-size: 0.95rem;
        }

        .form-group input {
            width: 100%;
            padding: 0.85rem 1rem;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            background: #fafbfc;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 3px rgba(14, 165, 164, 0.1);
        }

        .form-group small {
            display: block;
            margin-top: 0.4rem;
            color: var(--text-light);
            font-size: 0.85rem;
        }

        /* BUTTONS */
        .btn-submit {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 0.9rem 2rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.95rem;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            box-shadow: 0 2px 8px rgba(14, 165, 164, 0.3);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(14, 165, 164, 0.4);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        /* INFO BOX */
        .info-box {
            background: var(--secondary);
            padding: 1rem 1.25rem;
            border-radius: 8px;
            border-left: 3px solid var(--primary);
            margin: 2rem 0;
        }

        .info-box strong {
            color: var(--text-dark);
        }

        .info-box p {
            margin: 0.5rem 0;
            font-size: 0.95rem;
        }

        /* STATS */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            margin: 2rem 0;
        }

        .stat-box {
            background: var(--secondary);
            padding: 1.5rem;
            border-radius: 8px;
            border: 1px solid var(--border);
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .stat-icon {
            font-size: 2rem;
            color: var(--primary);
        }

        .stat-content h4 {
            margin: 0 0 0.25rem 0;
            font-size: 0.85rem;
            text-transform: uppercase;
            color: var(--text-light);
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .stat-content p {
            margin: 0;
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--text-dark);
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .profile-wrapper {
                grid-template-columns: 1fr;
            }

            .profile-sidebar {
                position: static;
            }

            .sidebar-header {
                margin-bottom: 1.5rem;
            }

            .profile-sidebar nav {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 0.5rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .navbar div {
                gap: 1rem;
                font-size: 0.85rem;
            }

            .profile-main {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <h1><a href="index.php"><?= htmlspecialchars(SITE_NAME ?? 'Findmi') ?></a></h1>
        <div>
            <a href="dashboard.php"><i class="fas fa-list"></i> Annonces</a>
            <a href="historique_messages.php"><i class="fas fa-envelope"></i> Messages</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
        </div>
    </nav>

    <div class="container">
        <div class="profile-wrapper">
            <!-- SIDEBAR NAVIGATION -->
            <aside class="profile-sidebar">
                <div class="sidebar-header">
                    <div class="sidebar-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="sidebar-info">
                        <h3><?= htmlspecialchars(substr($utilisateur['nom'], 0, 20)) ?></h3>
                        <p><?= htmlspecialchars(substr($utilisateur['email'], 0, 25)) ?></p>
                    </div>
                </div>

                <nav role="navigation">
                    <button class="nav-btn active" data-section="infos">
                        <i class="fas fa-user-circle"></i>
                        Informations
                    </button>
                    <button class="nav-btn" data-section="password">
                        <i class="fas fa-lock"></i>
                        Mot de passe
                    </button>
                    <button class="nav-btn" data-section="activity">
                        <i class="fas fa-chart-line"></i>
                        Activité
                    </button>
                </nav>
            </aside>

            <!-- MAIN CONTENT -->
            <main class="profile-main">
                <!-- ALERTS -->
                <?php if (!empty($messages)): ?>
                    <div class="alert success">
                        <?php foreach ($messages as $msg): ?>
                            <p><i class="fas fa-check-circle"></i> <?= htmlspecialchars($msg) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                    <div class="alert error">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- SECTION: INFORMATIONS PERSONNELLES -->
                <section id="infos" class="profile-section active">
                    <div class="section-header">
                        <i class="fas fa-user-circle"></i>
                        <h2>Informations Personnelles</h2>
                    </div>

                    <form method="POST">
                        <input type="hidden" name="action" value="update_profile">
                        
                        <div class="form-group">
                            <label for="nom"><i class="fas fa-font"></i> Nom complet</label>
                            <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($utilisateur['nom'] ?? '') ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="email"><i class="fas fa-envelope"></i> Adresse email</label>
                            <input type="email" id="email" name="email" value="<?= htmlspecialchars($utilisateur['email'] ?? '') ?>" required>
                            <small>Utilisé pour les notifications et la récupération de compte</small>
                        </div>

                        <div class="form-group">
                            <label for="telephone"><i class="fas fa-phone"></i> Numéro de téléphone</label>
                            <input type="tel" id="telephone" name="telephone" value="<?= htmlspecialchars($utilisateur['telephone'] ?? '') ?>" required>
                            <small>Visible uniquement pour les autorités modératrices</small>
                        </div>

                        <div class="info-box">
                            <p><strong><i class="fas fa-calendar"></i> Inscrit depuis :</strong></p>
                            <p><?= date('d F Y', strtotime($utilisateur['date_inscription'])) ?></p>
                        </div>

                        <button type="submit" class="btn-submit">
                            <i class="fas fa-save"></i>
                            Enregistrer les modifications
                        </button>
                    </form>
                </section>

                <!-- SECTION: MOT DE PASSE -->
                <section id="password" class="profile-section">
                    <div class="section-header">
                        <i class="fas fa-lock"></i>
                        <h2>Changer le Mot de Passe</h2>
                    </div>

                    <form method="POST">
                        <input type="hidden" name="action" value="change_password">
                        
                        <div class="form-group">
                            <label for="ancien_mdp"><i class="fas fa-key"></i> Ancien mot de passe</label>
                            <input type="password" id="ancien_mdp" name="ancien_mdp" required>
                        </div>

                        <div class="form-group">
                            <label for="nouveau_mdp"><i class="fas fa-lock"></i> Nouveau mot de passe</label>
                            <input type="password" id="nouveau_mdp" name="nouveau_mdp" required>
                            <small>Minimum 6 caractères. Utilisez des lettres, chiffres et symboles</small>
                        </div>

                        <div class="form-group">
                            <label for="confirmer_mdp"><i class="fas fa-check-circle"></i> Confirmer le mot de passe</label>
                            <input type="password" id="confirmer_mdp" name="confirmer_mdp" required>
                        </div>

                        <button type="submit" class="btn-submit">
                            <i class="fas fa-shield-alt"></i>
                            Changer le mot de passe
                        </button>
                    </form>
                </section>

                <!-- SECTION: ACTIVITÉ -->
                <section id="activity" class="profile-section">
                    <div class="section-header">
                        <i class="fas fa-chart-line"></i>
                        <h2>Votre Activité</h2>
                    </div>

                    <div class="stats-grid">
                        <div class="stat-box">
                            <div class="stat-icon"><i class="fas fa-list-alt"></i></div>
                            <div class="stat-content">
                                <h4>Annonces</h4>
                                <p><?php 
                                    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM annonces WHERE id_utilisateur = ?");
                                    $stmt->execute([$id_utilisateur]);
                                    $count = $stmt->fetch()['count'];
                                    echo $count;
                                ?></p>
                            </div>
                        </div>

                        <div class="stat-box">
                            <div class="stat-icon"><i class="fas fa-envelope"></i></div>
                            <div class="stat-content">
                                <h4>Messages</h4>
                                <p><?php 
                                    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM messages WHERE id_utilisateur = ?");
                                    $stmt->execute([$id_utilisateur]);
                                    $count = $stmt->fetch()['count'];
                                    echo $count;
                                ?></p>
                            </div>
                        </div>

                        <div class="stat-box">
                            <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                            <div class="stat-content">
                                <h4>Approuvés</h4>
                                <p><?php 
                                    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM messages WHERE id_utilisateur = ? AND valide_par_admin = 1");
                                    $stmt->execute([$id_utilisateur]);
                                    $count = $stmt->fetch()['count'];
                                    echo $count;
                                ?></p>
                            </div>
                        </div>

                        <div class="stat-box">
                            <div class="stat-icon"><i class="fas fa-times-circle"></i></div>
                            <div class="stat-content">
                                <h4>Rejetés</h4>
                                <p><?php 
                                    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM messages WHERE id_utilisateur = ? AND est_rejete = 1");
                                    $stmt->execute([$id_utilisateur]);
                                    $count = $stmt->fetch()['count'];
                                    echo $count;
                                ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="info-box">
                        <p><i class="fas fa-info-circle"></i> <strong>Conseil :</strong> Consultez régulièrement vos messages et mettez à jour vos informations de contact.</p>
                    </div>
                </section>
            </main>
        </div>
    </div>

    <script>
        // Navigation entre les sections du profil
        document.querySelectorAll('.nav-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Désactiver tous les boutons et masquer toutes les sections
                document.querySelectorAll('.nav-btn').forEach(b => b.classList.remove('active'));
                document.querySelectorAll('.profile-section').forEach(s => s.classList.remove('active'));

                // Activer le bouton et afficher la section
                this.classList.add('active');
                const sectionId = this.getAttribute('data-section');
                document.getElementById(sectionId).classList.add('active');
            });
        });
    </script>

    <?php include 'footer.php'; ?>

</body>
</html>
