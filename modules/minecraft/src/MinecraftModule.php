<?php
// ============================================================================
// Copyright (C) 2026  DieOuwe — GPL-3.0-or-later
// ============================================================================
declare(strict_types=1);
namespace CommunityFusion\Modules\Minecraft;

use CommunityFusion\Core\Application;
use CommunityFusion\Core\Module\ModuleInterface;
use CommunityFusion\Core\Block\BlockRegistry;
use CommunityFusion\Core\Database\Connection;
use CommunityFusion\Core\Cache\CacheManager;
use CommunityFusion\Core\Hook\HookManager;

final class MinecraftModule implements ModuleInterface
{
    public function getSlug(): string { return 'minecraft'; }

    public function boot(Application $app): void
    {
        $db = $app->make(Connection::class);
        $cache = $app->make(CacheManager::class);
        $app->make(BlockRegistry::class)->register(new MinecraftStatusBlock($cache));
        $app->make(HookManager::class)->addAction('router.routes', function($router) {
            $router->get('/minecraft', 'CommunityFusion\Modules\Minecraft\MinecraftController@index');
        });
    }

    public function install(): void {}
    public function uninstall(): void {}
    public function getBlocks(): array { return ['minecraft-status']; }
}
