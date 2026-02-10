<?php
// Simple maintenance template. Included from core/db_connect.php when maintenance is active.
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo htmlspecialchars(defined('SITE_NAME') ? SITE_NAME : 'Findmi'); ?></title>
    <link rel="stylesheet" href="/css/maintenance.css">
</head>
<body class="maintenance-root">
    <div class="maintenance-card">
        <div class="maintenance-logo">
            <?php
            $logoPath = __DIR__ . '/images/logo.png';
            if (file_exists($logoPath)) {
                echo '<img src="/images/logo.png" alt="' . htmlspecialchars(defined('SITE_NAME') ? SITE_NAME : 'Findmi') . '">';
            } else {
                echo '<div class="site-name">' . htmlspecialchars(defined('SITE_NAME') ? SITE_NAME : 'Findmi') . '</div>';
            }
            ?>
        </div>
        <h1><?php echo htmlspecialchars(function_exists('t') ? t('maintenance_title') : 'Maintenance'); ?></h1>
        <p class="lead"><?php echo htmlspecialchars(function_exists('t') ? t('maintenance_message') : 'Le site est en maintenance, revenez plus tard.'); ?></p>
        <p class="contact"><?php echo htmlspecialchars(function_exists('t') ? t('maintenance_contact_label') : 'Contact:') . ' ' . htmlspecialchars(defined('SITE_EMAIL') ? SITE_EMAIL : 'contact@findmi.local'); ?></p>
        <p>
            <a class="btn" href="/"><?php echo htmlspecialchars(function_exists('t') ? t('maintenance_button_home') : 'Return to homepage'); ?></a>
        </p>
    </div>
</body>
</html>
