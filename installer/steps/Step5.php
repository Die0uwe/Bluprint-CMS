<?php
// Step 5 — Modules selecteren + Config schrijven + Afronden

$selectedModules = $_POST['modules'] ?? ['users', 'news', 'pages', 'settings'];
$db   = InstallerCore::getData('db');
$site = InstallerCore::getData('site');

// Schrijf config bestand
InstallerCore::writeConfig([
    'date'            => date('Y-m-d H:i:s'),
    'site_name_php'   => var_export($site['siteName'], true),
    'site_url_php'    => var_export($site['siteUrl'], true),
    'timezone_php'    => var_export($site['timezone'], true),
    'locale_php'      => var_export($site['locale'], true),
    'mail_php'        => var_export($site['mail'] ?: 'noreply@example.com', true),
    'db_host_php'     => var_export($db['host'], true),
    'db_port_php'     => (int) $db['port'],
    'db_name_php'     => var_export($db['name'], true),
    'db_user_php'     => var_export($db['user'], true),
    'db_pass_php'     => var_export($db['pass'], true),
]);

// Sla site settings op in DB
try {
    $dsn = "mysql:host={$db['host']};port={$db['port']};dbname={$db['name']};charset=utf8mb4";
    $pdo = new PDO($dsn, $db['user'], $db['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    $settings = [
        ['core', 'site_name', $site['siteName']],
        ['core', 'site_url',  $site['siteUrl']],
        ['core', 'default_locale', $site['locale']],
        ['core', 'timezone', $site['timezone']],
    ];

    $stmt = $pdo->prepare("INSERT INTO cf_settings (`group`,`key`,`value`) VALUES (?,?,?)
                           ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)");
    foreach ($settings as $s) $stmt->execute($s);

    // Hernoem installer map
    if (is_dir(CF_ROOT . '/installer') && !is_dir(CF_ROOT . '/installer.done')) {
        // Schrijf een lock-bestand i.p.v. hernoemen (veiliger)
        file_put_contents(CF_ROOT . '/installer/.installed', date('c'));
    }

} catch (PDOException $e) {
    return ['Fout bij opslaan instellingen: ' . $e->getMessage()];
}

session_destroy();
return true;
