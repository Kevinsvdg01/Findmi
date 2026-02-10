<?php
session_start();
require_once __DIR__ . '/../core/db_connect.php';

// Vérifier si l'utilisateur est connecté en tant qu'autorité/admin
if (!isset($_SESSION['id_autorite'])) {
    header('Location: connexion.php');
    exit;
}

// Créer la table settings si nécessaire
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
        setting_key VARCHAR(100) NOT NULL PRIMARY KEY,
        setting_value TEXT NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
} catch (Exception $e) {
    // ignore
}

// Charger les paramètres existants
$settings = [];
$stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
if ($stmt) {
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
}

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}

// Valeurs par défaut
$defaults = [
    'site_name' => $settings['site_name'] ?? 'Findmi',
    'site_email' => $settings['site_email'] ?? 'contact@findmi.local',
    'items_per_page' => $settings['items_per_page'] ?? '10',
    'maintenance_mode' => $settings['maintenance_mode'] ?? '0',
    'allow_registration' => $settings['allow_registration'] ?? '1',
    'notify_admin_new_message' => $settings['notify_admin_new_message'] ?? '1',
    'default_language' => $settings['default_language'] ?? 'fr'
];

// Traiter la soumission du formulaire
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors[] = 'Jeton CSRF invalide.';
    } else {
        // Récupérer et valider les données
        $site_name = trim($_POST['site_name'] ?? '');
        $site_email = trim($_POST['site_email'] ?? '');
        $items_per_page = intval($_POST['items_per_page'] ?? 10);
        $maintenance_mode = isset($_POST['maintenance_mode']) ? '1' : '0';
        $allow_registration = isset($_POST['allow_registration']) ? '1' : '0';
        $notify_admin_new_message = isset($_POST['notify_admin_new_message']) ? '1' : '0';
        $default_language = in_array($_POST['default_language'] ?? 'fr', ['fr','en']) ? $_POST['default_language'] : 'fr';

        if ($site_name === '') $errors[] = 'Le nom du site est requis.';
        if (!filter_var($site_email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email de contact invalide.';
        if ($items_per_page < 1 || $items_per_page > 200) $errors[] = 'Éléments par page doit être entre 1 et 200.';

        if (empty($errors)) {
            // Upsert des paramètres
            $pairs = [
                'site_name' => $site_name,
                'site_email' => $site_email,
                'items_per_page' => (string)$items_per_page,
                'maintenance_mode' => $maintenance_mode,
                'allow_registration' => $allow_registration,
                'notify_admin_new_message' => $notify_admin_new_message,
                'default_language' => $default_language
            ];

            $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
            foreach ($pairs as $k => $v) {
                $stmt->execute([$k, $v]);
            }

            $_SESSION['success'] = 'Paramètres mis à jour avec succès';
            // Regénérer le token CSRF
            $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
            // Recharger les valeurs
            header('Location: parametres.php');
            exit;
        }
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paramètres - Admin</title>
    <link rel="stylesheet" href="admin_style.css">
</head>
<body>
    <div class="admin-container">
        <h1>Paramètres du site</h1>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $err): ?>
                    <p><?= htmlspecialchars($err) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
        <?php endif; ?>

        <form method="POST" class="settings-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

            <div class="form-group">
                <label for="site_name">Nom du site</label>
                <input type="text" id="site_name" name="site_name" required value="<?= htmlspecialchars($defaults['site_name']) ?>">
            </div>

            <div class="form-group">
                <label for="site_email">Email de contact</label>
                <input type="email" id="site_email" name="site_email" required value="<?= htmlspecialchars($defaults['site_email']) ?>">
            </div>

            <div class="form-group">
                <label for="items_per_page">Éléments par page</label>
                <input type="number" id="items_per_page" name="items_per_page" min="1" max="200" value="<?= htmlspecialchars($defaults['items_per_page']) ?>">
            </div>

            <div class="form-group">
                <label><input type="checkbox" name="allow_registration" <?= $defaults['allow_registration'] === '1' ? 'checked' : '' ?>> Autoriser l'inscription des utilisateurs</label>
            </div>

            <div class="form-group">
                <label><input type="checkbox" name="notify_admin_new_message" <?= $defaults['notify_admin_new_message'] === '1' ? 'checked' : '' ?>> Notifier l'admin des nouveaux messages</label>
            </div>

            <div class="form-group">
                <label><input type="checkbox" name="maintenance_mode" <?= $defaults['maintenance_mode'] === '1' ? 'checked' : '' ?>> Mode maintenance (site indisponible aux visiteurs)</label>
            </div>

            <div class="form-group">
                <label for="default_language">Langue par défaut</label>
                <select id="default_language" name="default_language">
                    <option value="fr" <?= $defaults['default_language'] === 'fr' ? 'selected' : '' ?>>Français</option>
                    <option value="en" <?= $defaults['default_language'] === 'en' ? 'selected' : '' ?>>English</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary"><?= htmlspecialchars(t('save')) ?></button>
            <a href="index.php" class="btn" style="margin-left:10px;"><?= htmlspecialchars(t('back')) ?></a>
        </form>
    </div>
</body>
</html>