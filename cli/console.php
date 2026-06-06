<?php

declare(strict_types=1);

/**
 * Community Fusion CMS — CLI Console
 * Gebruik: php cli/console.php <commando> [opties]
 *
 * Beschikbare commando's:
 *   queue:work          Verwerk queue jobs
 *   cache:clear         Verwijder alle cache
 *   migrate             Voer database migraties uit
 *   module:install      Installeer een module
 */

define('CF_ROOT',   dirname(__DIR__));
define('CF_PUBLIC', CF_ROOT . '/public');
define('CF_START',  microtime(true));

if (php_sapi_name() !== 'cli') {
    exit('Dit script mag alleen via CLI worden uitgevoerd.');
}

require_once CF_ROOT . '/vendor/autoload.php';

if (file_exists(CF_ROOT . '/.env')) {
    (Dotenv\Dotenv::createImmutable(CF_ROOT))->load();
}

$command = $argv[1] ?? 'help';

match ($command) {
    'queue:work'     => (new CommunityFusion\Cli\Commands\QueueWorkerCommand())->handle($argv),
    'cache:clear'    => (new CommunityFusion\Cli\Commands\CacheClearCommand())->handle($argv),
    'migrate'        => (new CommunityFusion\Cli\Commands\MigrateCommand())->handle($argv),
    'module:install' => (new CommunityFusion\Cli\Commands\ModuleInstallCommand())->handle($argv),
    default          => printHelp(),
};

function printHelp(): void
{
    echo <<<HELP
Community Fusion CMS — CLI Console v1.0

Gebruik: php cli/console.php <commando>

Commando's:
  queue:work [--queue=default] [--sleep=3]    Verwerk queue jobs
  cache:clear                                  Verwijder alle cache
  migrate                                      Voer DB migraties uit
  module:install <slug>                        Installeer een module

HELP;
}
