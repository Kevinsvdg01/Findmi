<?php
// core/db_connect.php

$host = 'localhost';        // Ou l'IP de votre serveur de BDD
$dbname = 'findmi_db';      // Le nom de la base de données que nous avons créée
$user = 'root';             // L'utilisateur de la BDD (par défaut 'root' sur XAMPP)
$pass = '';                 // Le mot de passe (par défaut vide sur XAMPP)

// Options de connexion PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Gérer les erreurs comme des exceptions
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Récupérer les résultats en tableau associatif
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Utiliser de vraies requêtes préparées
];

try {
    // Création de l'instance PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, $options);

    // Charger les paramètres globaux (table settings) si elle existe
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE 'settings'");
        if ($stmt && $stmt->fetchColumn()) {
            $site_settings = [];
            $stmt2 = $pdo->query("SELECT setting_key, setting_value FROM settings");
            if ($stmt2) {
                foreach ($stmt2->fetchAll(PDO::FETCH_ASSOC) as $row) {
                    $site_settings[$row['setting_key']] = $row['setting_value'];
                }
            }
            // Exposer quelques constantes courantes
            if (!defined('SITE_NAME')) define('SITE_NAME', $site_settings['site_name'] ?? 'Findmi');
            if (!defined('SITE_EMAIL')) define('SITE_EMAIL', $site_settings['site_email'] ?? 'contact@findmi.local');
            if (!defined('ITEMS_PER_PAGE')) define('ITEMS_PER_PAGE', intval($site_settings['items_per_page'] ?? 10));
            if (!defined('DEFAULT_LANGUAGE')) define('DEFAULT_LANGUAGE', $site_settings['default_language'] ?? 'fr');
            if (!defined('MAINTENANCE_MODE')) define('MAINTENANCE_MODE', ($site_settings['maintenance_mode'] ?? '0') === '1');
        }
    } catch (Exception $e) {
        // ignore errors when reading settings
    }

    // Charger le système de traduction (i18n)
    try {
        require_once __DIR__ . '/i18n.php';
    } catch (Exception $e) {
        // ignore
    }

    // Mode maintenance : activé par la base (MAINTENANCE_MODE) ou par fichier-flag .maintenance
    try {
        $maintenanceFlagFile = __DIR__ . '/../.maintenance';
        $maintenanceActive = (defined('MAINTENANCE_MODE') && MAINTENANCE_MODE) || file_exists($maintenanceFlagFile);

        if ($maintenanceActive) {
            $isCli = php_sapi_name() === 'cli';
            $requestUri = $_SERVER['REQUEST_URI'] ?? '';
            $isAdminArea = strpos($requestUri, '/admin/') === 0;
            $isAdminUser = isset($_SESSION['id_autorite']);

            if (!$isCli && !$isAdminArea && !$isAdminUser) {
                http_response_code(503);
                $maintenanceTemplate = __DIR__ . '/../maintenance.php';
                if (file_exists($maintenanceTemplate)) {
                    include $maintenanceTemplate;
                } else {
                    echo '<!doctype html><html><head><meta charset="utf-8"><title>' . htmlspecialchars(SITE_NAME ?? 'Findmi') . '</title></head><body style="font-family:Arial,Helvetica,sans-serif;padding:40px;text-align:center;">';
                    echo '<h1>' . htmlspecialchars(function_exists('t') ? t('maintenance_message') : 'Le site est en maintenance, revenez plus tard.') . '</h1>';
                    echo '</body></html>';
                }
                exit;
            }
        }
    } catch (Exception $e) {
        // ignore maintenance errors
    }

} catch (\PDOException $e) {
    // En cas d'erreur de connexion, on arrête tout et on affiche un message
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}