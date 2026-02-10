<?php
// core/i18n.php - simple translation loader
// Attends que DEFAULT_LANGUAGE soit défini (sinon prend 'fr')
$__i18n_lang = defined('DEFAULT_LANGUAGE') ? DEFAULT_LANGUAGE : ($_SESSION['lang'] ?? 'fr');
$__i18n_lang = in_array($__i18n_lang, ['fr','en']) ? $__i18n_lang : 'fr';

$__i18n_messages = [];
$langFile = __DIR__ . "/../lang/{$__i18n_lang}.php";
if (file_exists($langFile)) {
    $__i18n_messages = include $langFile;
}

if (!function_exists('t')) {
    function t(string $key, array $placeholders = []) {
        global $__i18n_messages;
        $msg = $__i18n_messages[$key] ?? $key;
        foreach ($placeholders as $k => $v) {
            $msg = str_replace('{' . $k . '}', $v, $msg);
        }
        return $msg;
    }
}
