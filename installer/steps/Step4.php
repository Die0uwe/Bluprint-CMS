<?php
// Step 4 — Admin Account Aanmaken

$username = trim($_POST['username'] ?? '');
$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm  = $_POST['password_confirm'] ?? '';

$errors = [];
if (strlen($username) < 3)  $errors[] = 'Gebruikersnaam moet minimaal 3 tekens zijn.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Ongeldig e-mailadres.';
if (strlen($password) < 8)  $errors[] = 'Wachtwoord moet minimaal 8 tekens zijn.';
if ($password !== $confirm)  $errors[] = 'Wachtwoorden komen niet overeen.';

if (!empty($errors)) return $errors;

$db   = InstallerCore::getData('db');
$hash = password_hash($password, PASSWORD_ARGON2ID);

try {
    $dsn = "mysql:host={$db['host']};port={$db['port']};dbname={$db['name']};charset=utf8mb4";
    $pdo = new PDO($dsn, $db['user'], $db['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    // Super admin gebruiker aanmaken
    $pdo->prepare("INSERT INTO cf_users (username, email, password_hash, display_name, is_active, is_verified, email_verified_at)
                   VALUES (?, ?, ?, ?, 1, 1, NOW())
                   ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash)")
        ->execute([$username, $email, $hash, $username]);

    $userId = $pdo->lastInsertId() ?: 1;

    // super_admin rol toewijzen (id=1)
    $pdo->prepare("INSERT IGNORE INTO cf_user_roles (user_id, role_id) VALUES (?, 1)")
        ->execute([$userId]);

    InstallerCore::saveData('admin', compact('username','email'));
    return true;

} catch (PDOException $e) {
    return ['Admin aanmaken mislukt: ' . $e->getMessage()];
}
