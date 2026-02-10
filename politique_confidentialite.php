<?php
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php require_once 'core/db_connect.php'; ?>
    <title>Politique de Confidentialité — <?= htmlspecialchars(SITE_NAME ?? 'Findmi') ?></title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <main class="container" style="padding:2rem 1rem; max-width:900px; margin:0 auto;">
        <h1>Politique de Confidentialité</h1>

            <p>La présente politique décrit comment <?= htmlspecialchars(SITE_NAME ?? 'Findmi') ?> collecte, utilise, protège et partage les données personnelles des utilisateurs du site.</p>

        <section>
            <h2>1. Données collectées</h2>
            <ul>
                <li>Données fournies volontairement : nom, adresse email, numéro de téléphone, message (par formulaire de contact ou lors de la création d'une annonce).</li>
                <li>Données techniques : adresse IP, type de navigateur, pages consultées (logs).</li>
                <li>Cookies et trackers : voir la section dédiée.</li>
            </ul>
        </section>

        <section>
            <h2>2. Finalités du traitement</h2>
            <p>Nous utilisons les données pour :</p>
            <ul>
                <li>Permettre la communication avec les utilisateurs et répondre aux demandes.</li>
                <li>Gérer la publication d'annonces et le fonctionnement du service.</li>
                <li>Améliorer le site et analyser l'utilisation.</li>
            </ul>
        </section>

        <section>
            <h2>3. Cookies</h2>
            <p>Le site utilise des cookies pour améliorer l'expérience utilisateur et effectuer des statistiques. Vous pouvez configurer votre navigateur pour refuser les cookies, toutefois certaines fonctionnalités peuvent être altérées.</p>
        </section>

        <section>
            <h2>4. Partage et destinataires</h2>
            <p>Les données peuvent être partagées avec des prestataires techniques (hébergement, outils d'envoi d'emails). Findmi ne vend pas vos données à des tiers.</p>
        </section>

        <section>
            <h2>5. Sécurité</h2>
            <p>Findmi met en œuvre des mesures techniques et organisationnelles pour protéger vos données contre l'accès non autorisé, la divulgation ou la perte.</p>
        </section>

        <section>
            <h2>6. Durée de conservation</h2>
            <p>Les données sont conservées le temps nécessaire aux finalités indiquées (ex. : échanges de contact, gestion d'annonces). Les logs techniques sont conservés selon les obligations légales ou à des fins de sécurité.</p>
        </section>

        <section>
            <h2>7. Vos droits</h2>
            <p>Vous disposez d'un droit d'accès, de rectification, d'effacement, de limitation du traitement, d'opposition et de portabilité des données. Pour exercer ces droits, contactez-nous à <a href="mailto:contact@findmi.com">contact@findmi.com</a>.</p>
        </section>

        <section>
            <h2>8. Mineurs</h2>
            <p>Le site n'est pas destiné aux enfants mineurs. Nous vous demandons de ne pas communiquer de données personnelles concernant des mineurs sans le consentement parental approprié.</p>
        </section>

        <section>
            <h2>9. Modifications de la politique</h2>
            <p>Findmi peut mettre à jour cette politique. Les modifications sont publiées sur cette page avec la date de mise à jour.</p>
        </section>

        <p style="margin-top:1.5rem; font-size:0.95rem; color:#555;">Pour toute question relative à la protection des données, écrivez à <a href="mailto:<?= htmlspecialchars(SITE_EMAIL ?? 'contact@findmi.com') ?>"><?= htmlspecialchars(SITE_EMAIL ?? 'contact@findmi.com') ?></a>.</p>
    </main>

    <?php include 'footer.php'; ?>
</body>
</html>
