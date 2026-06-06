<?php
// ============================================================================
// Copyright (C) 2026  DieOuwe — GPL-3.0-or-later
// ============================================================================
declare(strict_types=1);
namespace CommunityFusion\Modules\FiveM;

use CommunityFusion\Core\Request;
use CommunityFusion\Core\Response;
use CommunityFusion\Core\Database\Connection;
use CommunityFusion\Core\Cache\CacheManager;

final class FiveMController
{
    public function __construct(
        private readonly Connection   $db,
        private readonly CacheManager $cache,
    ) {}

    public function index(Request $request): Response
    {
        $settings = [];
        try {
            $rows = $this->db->fetchAll("SELECT `key`,`value` FROM cf_settings WHERE `group`='fivem'");
            foreach ($rows as $r) $settings[$r['key']] = $r['value'];
        } catch (\Throwable) {}

        $serverIp = $settings['server_ip'] ?? '';
        $data     = null;

        if ($serverIp) {
            $cacheKey = 'fivem.status.' . md5($serverIp);
            $data = $this->cache->remember($cacheKey, 45, function() use ($serverIp) {
                $ch = curl_init("http://{$serverIp}/info.json");
                curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 4]);
                $info = json_decode(curl_exec($ch), true);
                curl_close($ch);

                $ph = curl_init("http://{$serverIp}/players.json");
                curl_setopt_array($ph, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 4]);
                $players = json_decode(curl_exec($ph), true) ?? [];
                curl_close($ph);

                return $info ? ['info' => $info, 'players' => $players] : null;
            });
        }

        $players    = $data['players'] ?? [];
        $maxPlayers = (int)($settings['max_players'] ?? 64);

        ob_start();
        include __DIR__ . '/../templates/index.php';
        return Response::html(ob_get_clean());
    }
}
