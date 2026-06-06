<?php
// ============================================================================
// Copyright (C) 2026  DieOuwe — GPL-3.0-or-later
// ============================================================================
declare(strict_types=1);
namespace CommunityFusion\Modules\Warcraft;

use CommunityFusion\Core\Application;
use CommunityFusion\Core\Module\ModuleInterface;
use CommunityFusion\Core\Block\BlockRegistry;
use CommunityFusion\Core\Database\Connection;
use CommunityFusion\Core\Cache\CacheManager;
use CommunityFusion\Core\Hook\HookManager;

final class WarcraftModule implements ModuleInterface
{
    private Application $app;
    public function getSlug(): string { return 'warcraft'; }

    public function boot(Application $app): void
    {
        $this->app  = $app;
        $db         = $app->make(Connection::class);
        $cache      = $app->make(CacheManager::class);
        $registry   = $app->make(BlockRegistry::class);
        $hooks      = $app->make(HookManager::class);
        $config     = $this->getConfig($db);

        $blizzard = new BlizzardApiClient(
            $cache,
            $config['client_id']     ?? '',
            $config['client_secret'] ?? '',
            $config['region']        ?? 'eu',
            $config['locale']        ?? 'en_GB',
        );

        // Registreer blocks
        $registry->register(new WowGuildRosterBlock($blizzard, $config));
        $registry->register(new WowMythicProgressBlock($blizzard, $config));
        $registry->register(new WowCharacterBlock($blizzard, $config));

        // Admin routes
        $hooks->addAction('router.routes', function($router) {
            $auth = ['CommunityFusion\Api\Middleware\AuthMiddleware'];
            $router->get('/wow',               'CommunityFusion\Modules\Warcraft\WarcraftController@index');
            $router->get('/wow/guild',         'CommunityFusion\Modules\Warcraft\WarcraftController@guild');
            $router->get('/wow/character/{name:[^/]+}', 'CommunityFusion\Modules\Warcraft\WarcraftController@character');
            $router->get('/admin/wow',         'CommunityFusion\Modules\Warcraft\WarcraftAdminController@index', $auth);
        });
    }

    public function install(): void {}
    public function uninstall(): void {}
    public function getBlocks(): array { return ['wow-guild-roster','wow-mythic-progress','wow-character']; }

    private function getConfig(Connection $db): array
    {
        try {
            $rows = $db->fetchAll("SELECT `key`, `value` FROM cf_settings WHERE `group` = 'warcraft'");
            $cfg  = [];
            foreach ($rows as $r) $cfg[$r['key']] = $r['value'];
            return $cfg;
        } catch (\Throwable) { return []; }
    }
}
