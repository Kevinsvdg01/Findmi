<?php
// Démarrer une session pour pouvoir utiliser les variables de session (pour les messages)
session_start();

// Inclure le fichier de connexion à la BDD
require_once 'core/db_connect.php';

// Initialiser un tableau pour stocker les erreurs
$errors = [];

// Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Récupérer et nettoyer les données du formulaire
    $email = trim($_POST['email']);
    $telephone = trim($_POST['telephone']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    // 2. Valider les données
    if (empty($email)) {
        $errors[] = "L'adresse email est obligatoire.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'adresse email n'est pas valide.";
    }

    if (empty($telephone)) {
        $errors[] = "Le numéro de téléphone est obligatoire.";
    } // On pourrait ajouter une validation plus stricte pour le format +226...

    if (empty($password)) {
        $errors[] = "Le mot de passe est obligatoire.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Le mot de passe doit contenir au moins 6 caractères.";
    } elseif ($password !== $password_confirm) {
        $errors[] = "Les mots de passe ne correspondent pas.";
    }

    // 3. Vérifier si l'email ou le téléphone n'existent pas déjà
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id_utilisateur FROM utilisateurs WHERE email = ? OR telephone = ?");
        $stmt->execute([$email, $telephone]);
        if ($stmt->fetch()) {
            $errors[] = "Un compte existe déjà avec cet email ou ce numéro de téléphone.";
        }
    }

    // 4. Si pas d'erreurs, insérer en BDD
    if (empty($errors)) {
        // Hachage du mot de passe pour la sécurité
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Préparation de la requête d'insertion
        $sql = "INSERT INTO utilisateurs (email, mot_de_passe, telephone) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);

        // Exécution de la requête
        if ($stmt->execute([$email, $hashed_password, $telephone])) {
            // Message de succès et redirection
            $_SESSION['success_message'] = "Votre compte a été créé avec succès ! Vous pouvez maintenant vous connecter.";
            header("Location: connexion.php");
            exit();
        } else {
            $errors[] = "Une erreur est survenue lors de la création de votre compte.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Findmi</title>
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
            max-width: 500px;
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
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="nav-logo">Findmi</a> <!-- Bouton hamburger -->
            <button class="menu-toggle" id="menuToggle" aria-label="Ouvrir le menu">
                <i class="fas fa-bars"></i>
            </button>
            <ul class="nav-links" id="navLinks">
                <li><a href="index.php">Accueil</a></li>
                <li><a href="recherche.php"><i class="fas fa-search"></i>Rechercher</a></li>
                <li><a href="inscription.php" class="user active">Inscription <i class="fa fa-user"></i></a></li>
                <li><a href="apropos.php">A Propos</a></li>
                <li><a href="contact.php">Contact</a></li>
            </ul>
        </div>
    </nav>

    <div class="bann">
        <p>Inscrivez-vous maintenant !</p>
    </div>

    <div class="login-container">

        <?php
        // Afficher les erreurs s'il y en a
        if (!empty($errors)) {
            echo '<div class="errors">';
            foreach ($errors as $error) {
                echo '<p>' . htmlspecialchars($error) . '</p>';
            }
            echo '</div>';
        }
        ?>

        <h1>Connexion</h1>

        <form action="inscription.php" method="POST">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="telephone">Téléphone</label>
                <input type="tel" id="telephone" name="telephone" required placeholder="+226:">
            </div>
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="password_confirm">Confirmer le mot de passe</label>
                <input type="password" id="password_confirm" name="password_confirm" required>
            </div>
            <button type="submit" class="btn">S'inscrire</button>
        </form>
        <div class="register-link">
            <p>Vous avez déjà un compte ? <a href="connexion.php">Connectez-vous</a></p>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script src="/js/script.js"></script>
</body>

</html>