<?php
// ============================================================================
// Copyright (C) 2026  DieOuwe — GPL-3.0-or-later
// ============================================================================
declare(strict_types=1);
namespace CommunityFusion\Modules\Minecraft;
use CommunityFusion\Blocks\AbstractBlock;
use CommunityFusion\Core\Cache\CacheManager;

/**
 * Minecraft Server Status Block
 * Gebruikt de mcapi.us / mcsrvstat.us API (geen auth vereist).
 */
final class MinecraftStatusBlock extends AbstractBlock
{
    public function __construct(private readonly CacheManager $cache) {}
    public function getSlug(): string { return 'minecraft-status'; }
    public function getName(): string { return 'Minecraft Server Status'; }

    public function getConfigSchema(): array
    {
        return [
            'host'       => ['type' => 'string',  'label' => 'Server IP/Host', 'required' => true],
            'port'       => ['type' => 'integer', 'label' => 'Poort', 'default' => 25565],
            'show_players' => ['type' => 'boolean', 'label' => 'Online spelers tonen', 'default' => true],
            'show_motd'  => ['type' => 'boolean', 'label' => 'MOTD tonen', 'default' => true],
        ];
    }

    public function render(array $config, array $context = []): string
    {
        $host = $config['host'] ?? '';
        if (empty($host)) return '<p class="cf-block-empty">⚙️ Configureer server IP.</p>';

        $port = (int)($config['port'] ?? 25565);
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

        $hostSafe = htmlspecialchars($host);
        $online   = $status['online'] ?? false;

        if (!$online) {
            return <<<HTML
            <div class="cf-minecraft-block cf-mc-offline">
                <div class="cf-mc-header">
                    <span style="font-size:1.5rem;">🟫</span>
                    <div>
                        <div class="cf-mc-host">{$hostSafe}</div>
                        <div class="cf-mc-status offline">⚫ Offline</div>
                    </div>
                </div>
            </div>
            HTML;
        }

        $playersCurrent = (int)($status['players']['online'] ?? 0);
        $playersMax     = (int)($status['players']['max'] ?? 0);
        $version        = htmlspecialchars($status['version'] ?? '');
        $motd           = $config['show_motd'] ?? true
            ? '<div class="cf-mc-motd">' . htmlspecialchars(implode(' ', $status['motd']['clean'] ?? [])) . '</div>'
            : '';

        $playersHtml = '';
        if ($config['show_players'] ?? true) {
            $playerList = array_slice($status['players']['list'] ?? [], 0, 10);
            if (!empty($playerList)) {
                $names = array_map(fn($p) => '<span class="cf-mc-player">' . htmlspecialchars($p['name'] ?? $p) . '</span>', $playerList);
                $playersHtml = '<div class="cf-mc-players">' . implode('', $names) . '</div>';
            }
        }

        return <<<HTML
        <div class="cf-minecraft-block cf-mc-online">
            <div class="cf-mc-header">
                <span style="font-size:1.5rem;">🟫</span>
                <div>
                    <div class="cf-mc-host">{$hostSafe}</div>
                    <div class="cf-mc-status online">🟢 Online · {$version}</div>
                </div>
                <div class="cf-mc-players-count">
                    <span class="cf-mc-count">{$playersCurrent}</span>/<span>{$playersMax}</span>
                </div>
            </div>
            {$motd}
            {$playersHtml}
        </div>
        HTML;
    }

    public function getCacheTtl(): int { return 60; }
}
