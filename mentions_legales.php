<?php
require_once 'core/db_connect.php';
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mentions Légales — Findmi</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <main class="container" style="padding:2rem 1rem; max-width:900px; margin:0 auto;">
        <h1>Mentions Légales</h1>

        <section>
            <h2>Éditeur du site</h2>
            <p>Site : <?= htmlspecialchars(SITE_NAME ?? 'Findmi') ?><br>
            Adresse : Ouagadougou, Burkina Faso<br>
            Email : <?= htmlspecialchars(SITE_EMAIL ?? 'contact@findmi.com') ?></p>
        </section>

        <section>
            <h2>Responsable de la publication</h2>
            <p>Le directeur de la publication du site est l'équipe <?= htmlspecialchars(SITE_NAME ?? 'Findmi') ?>.</p>
        </section>

        <section>
            <h2>Hébergement</h2>
            <p>Le site est hébergé par votre fournisseur d'hébergement. Pour toute question relative à l'hébergement, contactez-nous à <?= htmlspecialchars(SITE_EMAIL ?? 'contact@findmi.com') ?>.</p>
        </section>

        <section>
            <h2>Propriété intellectuelle</h2>
            <p>Tous les contenus présents sur le site (textes, images, logos, marques, icônes, etc.) sont protégés par le droit d'auteur et la propriété intellectuelle. Toute reproduction, représentation ou adaptation, totale ou partielle, est strictement interdite sans autorisation écrite préalable de <?= htmlspecialchars(SITE_NAME ?? 'Findmi') ?>.</p>
        </section>

        <section>
            <h2>Limitation de responsabilité</h2>
            <p>Findmi met en œuvre tous les moyens raisonnables pour fournir des informations fiables et à jour. Toutefois, Findmi ne saurait garantir l'exactitude, la complétude ou l'actualité des informations publiées. L'utilisation du site se fait sous la seule responsabilité de l'utilisateur.</p>
        </section>

        <section>
            <h2>Liens externes</h2>
            <p>Le site peut contenir des liens vers des sites tiers. Findmi n'exerce aucun contrôle sur ces sites et décline toute responsabilité quant à leur contenu ou leur politique de confidentialité.</p>
        </section>

        <section>
            <h2>Modification des mentions</h2>
            <p>Findmi se réserve le droit de modifier les présentes mentions légales à tout moment. Les changements prendront effet dès leur publication sur le site.</p>
        </section>

        <p style="margin-top:1.5rem; font-size:0.95rem; color:#555;">Pour toute question concernant ces mentions légales, contactez-nous à <a href="mailto:<?= htmlspecialchars(SITE_EMAIL ?? 'contact@findmi.com') ?>"><?= htmlspecialchars(SITE_EMAIL ?? 'contact@findmi.com') ?></a>.</p>
    </main>

    <?php include 'footer.php'; ?>
</body>
</html>
