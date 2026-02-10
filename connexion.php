<?php
// Démarrer la session
session_start();

// Si l'utilisateur est déjà connecté, on le redirige vers le tableau de bord
if (isset($_SESSION['id_utilisateur'])) {
    header('Location: tableau_de_bord.php');
    exit();
}

// Inclure la connexion à la BDD
require_once 'core/db_connect.php';

$errors = [];
$email = ''; // Pour pré-remplir le formulaire en cas d'erreur

// Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email)) {
        $errors[] = "L'adresse email est obligatoire.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'adresse email n'est pas valide.";
    }

    if (empty($password)) {
        $errors[] = "Le mot de passe est obligatoire.";
    }

    // Si pas d'erreurs de validation, on vérifie les identifiants
    if (empty($errors)) {
        // 1. Récupérer l'utilisateur correspondant à l'email
        $stmt = $pdo->prepare("SELECT id_utilisateur, email, mot_de_passe, telephone FROM utilisateurs WHERE email = ? AND est_actif = 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // 2. Vérifier si l'utilisateur existe ET si le mot de passe est correct
        if ($user && password_verify($password, $user['mot_de_passe'])) {
            // Le mot de passe est correct, on démarre la session

            // On régénère l'ID de session pour des raisons de sécurité
            session_regenerate_id(true);

            // On stocke les informations de l'utilisateur dans la session
            $_SESSION['id_utilisateur'] = $user['id_utilisateur'];
            $_SESSION['email_utilisateur'] = $user['email'];
            $_SESSION['telephone_utilisateur'] = $user['telephone'];

            // Redirection vers le tableau de bord
            header("Location: dashboard.php");
            exit();
        } else {
            // Identifiants incorrects ou compte inactif
            $errors[] = "L'email ou le mot de passe est incorrect.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Findmi</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        .bann {
            background: url('/images/logofindmi.jpg') no-repeat center center/cover;
            color: white;
            padding: 100px 20px;
            padding-top: 140px;
            text-align: center;
            margin-bottom: 30px;
        }

        .bann p {
            font-size: 1.5em;
            padding: 20px;
            background-color: rgba(37, 37, 37, 0.651);
            display: inline-block;
            border-radius: 5px;
        }

        .login-container {
            max-width: 400px;
            margin: 50px auto;
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .login-container h1 {
            text-align: center;
            color: #007BFF;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            color: #333;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .form-group button {
            width: 100%;
            padding: 12px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1em;
            font-weight: bold;
        }

        .form-group button:hover {
            background-color: #0056b3;
        }

        .register-link {
            text-align: center;
            margin-top: 20px;
        }

        .register-link a {
            color: #007BFF;
            text-decoration: none;
        }

        .register-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="nav-logo"><?= SITE_NAME ?? 'Findmi' ?></a> <!-- Bouton hamburger -->
            <button class="menu-toggle" id="menuToggle" aria-label="Ouvrir le menu">
                <i class="fas fa-bars"></i>
            </button>
            <ul class="nav-links" id="navLinks">
                <li><a href="index.php"><?= htmlspecialchars(t('home')) ?></a></li>
                <li><a href="recherche.php"><i class="fas fa-search"></i> <?= htmlspecialchars(t('search')) ?></a></li>
                <li><a href="connexion.php" class="user active"><?= htmlspecialchars(t('login')) ?> <i class="fa fa-user"></i></a></li>
                <li><a href="apropos.php"><?= htmlspecialchars(t('about')) ?></a></li>
                <li><a href="contact.php"><?= htmlspecialchars(t('contact')) ?></a></li>
            </ul>
        </div>
    </nav>

    <div class="bann">
        <p><?= htmlspecialchars(t('login_prompt')) ?></p>
    </div>

    <div class="login-container">
        <h2>Se connecter à <?= SITE_NAME ?? 'Findmi' ?></h2>

        <?php
        // Afficher le message de succès de l'inscription s'il existe
        if (isset($_SESSION['success_message'])) {
            echo '<div class="success">' . htmlspecialchars($_SESSION['success_message']) . '</div>';
            // On supprime le message pour ne pas le ré-afficher
            unset($_SESSION['success_message']);
        }

        // Afficher les erreurs s'il y en a
        if (!empty($errors)) {
            echo '<div class="errors">';
            foreach ($errors as $error) {
                echo '<p>' . htmlspecialchars($error) . '</p>';
            }
            echo '</div>';
        }
        ?>

        <form action="connexion.php" method="POST">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn">Se connecter</button>
        </form>
    </div>
    <div class="register-link">
        <p>Vous n'avez pas de compte ? <a href="inscription.php">Inscrivez-vous</a></p>
    </div>

    <?php include 'footer.php'; ?>

    <script src="/js/script.js"></script>
</body>

</html>