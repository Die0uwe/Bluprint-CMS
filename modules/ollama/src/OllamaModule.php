<?php
// ============================================================================
// Copyright (C) 2026  DieOuwe — GPL-3.0-or-later
// ============================================================================
declare(strict_types=1);
namespace CommunityFusion\Modules\Ollama;

use CommunityFusion\Core\Application;
use CommunityFusion\Core\Module\ModuleInterface;
use CommunityFusion\Core\Block\BlockRegistry;
use CommunityFusion\Core\Database\Connection;
use CommunityFusion\Core\Cache\CacheManager;
use CommunityFusion\Core\Hook\HookManager;

final class OllamaModule implements ModuleInterface
{
    private Application $app;
    public function getSlug(): string { return 'ollama'; }

    public function boot(Application $app): void
    {
        $this->app = $app;
        $db        = $app->make(Connection::class);
        $cache     = $app->make(CacheManager::class);
        $registry  = $app->make(BlockRegistry::class);
        $hooks     = $app->make(HookManager::class);

        $client = $this->makeClient($db, $cache);

        // Registreer blocks
        $registry->register(new OllamaChatBlock($client, $this->getConfig($db)));
        $registry->register(new OllamaAssistantBlock($client, $this->getConfig($db)));

        // Routes
        $hooks->addAction('router.routes', function($router) {
            $auth = ['CommunityFusion\Api\Middleware\AuthMiddleware'];
            // Publieke chat API (gebruikt door de chat block via AJAX)
            $router->post('/api/ollama/chat',      'CommunityFusion\Modules\Ollama\OllamaApiController@chat');
            $router->post('/api/ollama/summarize', 'CommunityFusion\Modules\Ollama\OllamaApiController@summarize');
            $router->get('/api/ollama/models',     'CommunityFusion\Modules\Ollama\OllamaApiController@models');
            $router->get('/admin/ollama',          'CommunityFusion\Modules\Ollama\OllamaAdminController@index', $auth);
            $router->post('/admin/ollama/save',    'CommunityFusion\Modules\Ollama\OllamaAdminController@save',  $auth);
        });

        // Hook: analyseer guild aanmeldingen automatisch met AI
        $hooks->addAction('guild.application.created', function(array $app) use ($client, $db) {
            try {
                $analysis = $client->analyzeGuildApplication($app);
                if ($analysis) {
                    $db->execute(
                        "UPDATE cf_guild_applications SET review_note = CONCAT(COALESCE(review_note,''), '\n\n🤖 AI Analyse:\n', ?) WHERE id = ?",
                        [$analysis, $app['id']]
                    );
                }
            } catch (\Throwable) {}
        });

        // Sla de client op in de container voor gebruik door andere modules
        $app->getContainer()->instance(OllamaClient::class, $client);
    }

    public function install(): void {}
    public function uninstall(): void {}
    public function getBlocks(): array { return ['ollama-chat', 'ollama-assistant']; }

    private function makeClient(Connection $db, CacheManager $cache): OllamaClient
    {
        $config = $this->getConfig($db);
        return new OllamaClient(
            host:          $config['host']          ?? 'http://localhost:11434',
            model:         $config['default_model'] ?? 'llama3.2',
            timeout:       (int)($config['timeout'] ?? 30),
            cache:         $cache,
            openWebUiUrl:  $config['open_webui_url'] ?? '',
            openWebUiKey:  $config['open_webui_key'] ?? '',
        );
    }

    private function getConfig(Connection $db): array
    {
        try {
            $rows = $db->fetchAll("SELECT `key`,`value` FROM cf_settings WHERE `group`='ollama'");
            $cfg  = [];
            foreach ($rows as $r) $cfg[$r['key']] = $r['value'];
            return $cfg;
        } catch (\Throwable) { return []; }
    }
}
