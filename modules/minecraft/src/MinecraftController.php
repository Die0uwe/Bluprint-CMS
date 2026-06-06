<?php
// ============================================================================
// Copyright (C) 2026  DieOuwe — GPL-3.0-or-later
// ============================================================================
declare(strict_types=1);
namespace CommunityFusion\Modules\Minecraft;

use CommunityFusion\Core\Request;
use CommunityFusion\Core\Response;
use CommunityFusion\Core\Database\Connection;
use CommunityFusion\Core\Cache\CacheManager;

final class MinecraftController
{
    public function __construct(
        private readonly Connection   $db,
        private readonly CacheManager $cache,
    ) {}

    public function index(Request $request): Response
    {
        $settings = [];
        try {
            $rows = $this->db->fetchAll("SELECT `key`,`value` FROM cf_settings WHERE `group`='minecraft'");
            foreach ($rows as $r) $settings[$r['key']] = $r['value'];
        } catch (\Throwable) {}

        $host = $settings['host'] ?? '';
        $port = (int)($settings['port'] ?? 25565);

        $status = null;
        if ($host) {
            $cacheKey = 'mc.status.' . md5($host . $port);
            $status = $this->cache->remember($cacheKey, 60, function() use ($host, $port) {
                $url = "https://api.mcsrvstat.us/3/{$host}:{$port}";
                $ch  = curl_init($url);
                curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 5]);
                $body = curl_exec($ch);
                $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                return $code === 200 ? json_decode($body, true) : null;
            });
        }

        ob_start();
        include __DIR__ . '/../templates/index.php';
        return Response::html(ob_get_clean());
    }
}
