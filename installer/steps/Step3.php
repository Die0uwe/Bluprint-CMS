<?php
declare(strict_types=1);
// Step 3 — Site Instellingen

$siteName = trim($_POST['site_name'] ?? '');
$siteUrl  = rtrim(trim($_POST['site_url'] ?? ''), '/');
$locale   = $_POST['locale'] ?? 'nl';
$timezone = $_POST['timezone'] ?? 'Europe/Amsterdam';
$mail     = trim($_POST['mail_from'] ?? '');

if (empty($siteName) || empty($siteUrl)) {
    return ['Sitenaam en URL zijn verplicht.'];
}

if (!filter_var($siteUrl, FILTER_VALIDATE_URL)) {
    return ['Geen geldige site URL (begin met https://).'];
}

InstallerCore::saveData('site', compact('siteName','siteUrl','locale','timezone','mail'));
return true;
