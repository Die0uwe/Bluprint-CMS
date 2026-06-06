<?php
// ============================================================================
// Copyright (C) 2026  DieOuwe — GPL-3.0-or-later
// ============================================================================
declare(strict_types=1);
namespace CommunityFusion\Modules\FiveM;

use CommunityFusion\Core\Application;
use CommunityFusion\Core\Module\ModuleInterface;
use CommunityFusion\Core\Block\BlockRegistry;
use CommunityFusion\Core\Cache\CacheManager;
use CommunityFusion\Core\Hook\HookManager;

final class FiveMModule implements ModuleInterface
{
    public function getSlug(): string { return 'fivem'; }

    public function boot(Application $app): void
    {
        $cache    = $app->make(CacheManager::class);
        $registry = $app->make(BlockRegistry::class);
        $registry->register(new FiveMStatusBlock($cache));
        $app->make(HookManager::class)->addAction('router.routes', function($router) {
            $router->get('/fivem', 'CommunityFusion\Modules\FiveM\FiveMController@index');
        });
    }

    public function install(): void {}
    public function uninstall(): void {}
    public function getBlocks(): array { return ['fivem-status']; }
}
