<?php
// Step 1 — Server Requirements Check
// Geeft true terug als alles OK is, anders array van fouten

$checks = [
    'PHP >= 8.3' => PHP_VERSION_ID >= 80300,
    'PDO extensie' => extension_loaded('pdo'),
    'pdo_mysql extensie' => extension_loaded('pdo_mysql'),
    'mbstring extensie' => extension_loaded('mbstring'),
    'openssl extensie' => extension_loaded('openssl'),
    'json extensie' => extension_loaded('json'),
    'cURL extensie' => extension_loaded('curl'),
    'GD extensie' => extension_loaded('gd'),
    'config/ schrijfbaar' => is_writable(CF_ROOT . '/config') || mkdir(CF_ROOT . '/config', 0755, true),
    'storage/ schrijfbaar' => is_writable(CF_ROOT . '/storage'),
];

$failed = array_keys(array_filter($checks, fn($v) => !$v));

if (!empty($failed)) {
    return array_map(fn($f) => "Vereiste niet voldaan: {$f}", $failed);
}

InstallerCore::saveData('server_check', true);
return true;
