<?php
declare(strict_types=1);
// Step 2 — Database Verbinding Testen + Schema Importeren

$host   = trim($_POST['db_host'] ?? '127.0.0.1');
$port   = (int) ($_POST['db_port'] ?? 3306);
$name   = trim($_POST['db_name'] ?? '');
$user   = trim($_POST['db_user'] ?? '');
$pass   = $_POST['db_pass'] ?? '';

if (empty($name) || empty($user)) {
    return ['Database naam en gebruiker zijn verplicht.'];
}

try {
    $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    // Schema importeren
    InstallerCore::importSchema($pdo);

    InstallerCore::saveData('db', compact('host','port','name','user','pass'));
    return true;

} catch (PDOException $e) {
    return ['Database verbinding mislukt: ' . $e->getMessage()];
}
