<?php
// ============================================================================
// Copyright (C) 2026  DieOuwe — GPL-3.0-or-later
// ============================================================================
declare(strict_types=1);
namespace CommunityFusion\Modules\Ollama;

use CommunityFusion\Core\Request;
use CommunityFusion\Core\Response;
use CommunityFusion\Core\Database\Connection;
use CommunityFusion\Core\Cache\CacheManager;
use CommunityFusion\Core\Security\CsrfProtection;

final class OllamaAdminController
{
    public function __construct(
        private readonly Connection   $db,
        private readonly CacheManager $cache,
    ) {}

    public function index(Request $request): Response
    {
        $settings = [];
        $rows = $this->db->fetchAll("SELECT `key`,`value` FROM cf_settings WHERE `group`='ollama'");
        foreach ($rows as $r) $settings[$r['key']] = $r['value'];

        // Test verbinding
        $client    = $this->makeClient($settings);
        $available = $client->isAvailable();
        $models    = $available ? $client->listModels() : [];

        ob_start();
        include __DIR__ . '/../templates/admin.php';
        return Response::html(ob_get_clean());
    }

    public function save(Request $request): Response
    {
        CsrfProtection::validateRequest();

        $fields = ['host','default_model','timeout','system_prompt','open_webui_url'];
        foreach ($fields as $key) {
            $val = $request->input($key, '');
            $this->db->execute(
                "INSERT INTO cf_settings (`group`,`key`,`value`,`type`) VALUES ('ollama',?,?,'string')
                 ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)",
                [$key, $val]
            );
        }

        $this->cache->delete('ollama.config');
        return Response::redirect('/admin/ollama?saved=1');
    }

    private function makeClient(array $config): OllamaClient
    {
        return new OllamaClient(
            host:    $config['host']          ?? 'http://localhost:11434',
            model:   $config['default_model'] ?? 'llama3.2',
            timeout: (int)($config['timeout'] ?? 30),
        );
    }
}
