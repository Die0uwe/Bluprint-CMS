<?php

declare(strict_types=1);

/**
 * Community Fusion CMS — Front Controller
 * Enige publieke entry point. Alle requests komen hier binnen.
 */

define('CF_VERSION', '1.0.0');
define('CF_ROOT',    dirname(__DIR__));
define('CF_PUBLIC',  __DIR__);
define('CF_START',   microtime(true));

// Composer autoloader
require_once CF_ROOT . '/vendor/autoload.php';

// Laad environment variabelen
if (file_exists(CF_ROOT . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(CF_ROOT);
    $dotenv->load();
}

// Controleer of installer nog gedraaid moet worden
if (!file_exists(CF_ROOT . '/config/config.php') && is_dir(CF_ROOT . '/installer')) {
    header('Location: /installer/');
    exit;
}

// Bootstrap de applicatie
$app = require_once CF_ROOT . '/src/Core/Application.php';
$app->run();
