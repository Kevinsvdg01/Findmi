<?php
$pageTitle = "Contact - Findmi";
$errors = [];
$success = false;

$name = $email = $subject = $message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($name)) $errors[] = "Veuillez renseigner votre nom.";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Adresse email invalide.";
    if (empty($subject)) $errors[] = "Veuillez indiquer un sujet.";
    if (empty($message)) $errors[] = "Le message ne peut pas être vide.";

    if (empty($errors)) {
        $to = "contact@findmi.com";
        $headers = "From: Findmi <no-reply@findmi.com>\r\n";
        $headers .= "Reply-To: $email\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

        $body = "Nom : $name\n";
        $body .= "Email : $email\n\n";
        $body .= "Message :\n$message";

        if (mail($to, $subject, $body, $headers)) {
            $success = true;
            $name = $email = $subject = $message = "";
        } else {
            $errors[] = "Une erreur est survenue lors de l’envoi du message.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title><?= $pageTitle ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f7f9fc;
            margin: 0;
            color: #333;
        }

        /* HERO */
        .contact-hero {
            background: linear-gradient(135deg, #4f46e5, #6366f1);
            color: white;
            padding: 70px 20px;
            text-align: center;
        }

        .contact-hero h1 {
            font-size: 2.4rem;
            margin-bottom: 10px;
        }

        /* CONTAINER */
        .contact-container {
            max-width: 900px;
            margin: -40px auto 60px;
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.08);
        }

        /* ALERTS */
        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 0.95rem;
        }

        .alert.success {
            background: #e6f9f0;
            color: #0f5132;
        }

        .alert.error {
            background: #fdecea;
            color: #842029;
        }

        /* FORM */
        .contact-form .form-group {
            margin-bottom: 20px;
        }

        .contact-form label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
        }

        .contact-form input,
        .contact-form textarea {
            width: 100%;
            padding: 12px 14px;
            border: 1.8px solid #e0e3eb;
            border-radius: 10px;
            font-size: 0.95rem;
            transition: border-color 0.3s;
        }

        .contact-form input:focus,
        .contact-form textarea:focus {
            outline: none;
            border-color: #4f46e5;
        }

        .contact-form textarea {
            min-height: 140px;
            resize: vertical;
        }

        /* BUTTON */
        .contact-form button {
            background: linear-gradient(135deg, #4f46e5, #6366f1);
            color: white;
            border: none;
            padding: 14px 26px;
            border-radius: 12px;
            font-size: 1rem;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .contact-form button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(79, 70, 229, 0.4);
        }

        /* INFO */
        .contact-info {
            margin-top: 40px;
            background: #f7f9fc;
            padding: 25px;
            border-radius: 12px;
        }

        .contact-info h3 {
            margin-bottom: 15px;
            color: #4f46e5;
        }

        .contact-info p {
            margin: 8px 0;
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
                <li><a href="apropos.php">À propos</a></li>
                <li><a href="contact.php" class="active">Contact</a></li>

                <?php if (isset($_SESSION['id_utilisateur'])): ?>
                    <li><a href="profil.php">Profil</a></li>
                    <li><a href="logout.php" class="btn-logout">Déconnexion</a></li>
                <?php else: ?>
                    <li><a href="connexion.php" class="btn-login">Connexion</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <section class="contact-hero">
        <h1>Contactez-nous</h1>
        <p>
            Une question, une suggestion ou un besoin d’assistance ?
            L’équipe Findmi est à votre écoute.
        </p>
    </section>

    <section class="contact-container">

        <?php if ($success): ?>
            <div class="alert success">
                <i class="fas fa-check-circle"></i>
                Votre message a bien été envoyé. Nous vous répondrons rapidement.
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert error">
                <?php foreach ($errors as $error): ?>
                    <p><i class="fas fa-circle-exclamation"></i> <?= $error ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="contact-form">
            <div class="form-group">
                <label>Nom complet</label>
                <input type="text" name="name" value="<?= htmlspecialchars($name) ?>">
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($email) ?>">
            </div>

            <div class="form-group">
                <label>Sujet</label>
                <input type="text" name="subject" value="<?= htmlspecialchars($subject) ?>">
            </div>

            <div class="form-group">
                <label>Message</label>
                <textarea name="message"><?= htmlspecialchars($message) ?></textarea>
            </div>

            <button type="submit">
                <i class="fas fa-paper-plane"></i>
                Envoyer le message
            </button>
        </form>

        <div class="contact-info">
            <h3>Informations de contact</h3>
            <p><i class="fas fa-envelope"></i> contact@findmi.com</p>
            <p><i class="fas fa-phone"></i> +226 XX XX XX XX</p>
            <p><i class="fas fa-location-dot"></i> Burkina Faso</p>
        </div>

    </section>

    <?php include 'footer.php'; ?>

</body>

</html>