<?php
session_start();
require_once '../core/db_connect.php'; // On remonte d'un dossier avec ../

// Si l'autorité est déjà connectée, redirection
if (isset($_SESSION['id_autorite'])) {
    header('Location: index.php');
    exit();
}

$errors = [];
$email = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $errors[] = "L'email et le mot de passe sont obligatoires.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM autorites WHERE email = ? AND est_actif = 1");
        $stmt->execute([$email]);
        $autorite = $stmt->fetch();

        if ($autorite && password_verify($password, $autorite['mot_de_passe'])) {
            session_regenerate_id(true);
            $_SESSION['id_autorite'] = $autorite['id_autorite'];
            $_SESSION['nom_autorite'] = $autorite['nom_autorite'];
            header("Location: index.php");
            exit();
        } else {
            $errors[] = "Email ou mot de passe incorrect.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - Connexion</title>
    <!-- On réutilise le même style que pour les utilisateurs -->
    <link rel="stylesheet" href="../css/style.css">
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
    </style>
</head>

<body>

    <nav class="navbar">
        <div class="nav-container">
            <a href="/index.php" class="nav-logo">Findmi</a> <!-- Bouton hamburger -->
            <button class="menu-toggle" id="menuToggle" aria-label="Ouvrir le menu">
                <i class="fas fa-bars"></i>
            </button>
            <ul class="nav-links" id="navLinks">
                <li><a href="/index.php">Accueil</a></li>
                <li><a href="connexion.php" class="user active">Connexion <i class="fa fa-user"></i></a></li>
                <li><a href="parametres.php"><i class="fas fa-cog"></i></a></li>
            </ul>
        </div>
    </nav>
    <div class="bann">
        <p>Connectez-vous maintenant chers Admin!</p>
    </div>

    <div class="login-container">
        <h2>Espace Autorités - Findmi</h2>

        <?php if (!empty($errors)): ?>
            <div class="errors">
                <?php foreach ($errors as $error) {
                    echo '<p>' . htmlspecialchars($error) . '</p>';
                } ?>
            </div>
        <?php endif; ?>

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

    <?php include '../footer.php'; ?>

    <script src="/js/script.js"></script>
</body>

</html>