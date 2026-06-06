<?php
// ============================================================================
// Copyright (C) 2026  DieOuwe — GPL-3.0-or-later
// ============================================================================

declare(strict_types=1);

namespace CommunityFusion\Modules\Twitch;

use CommunityFusion\Blocks\AbstractBlock;
use CommunityFusion\Core\Database\Connection;
use CommunityFusion\Core\Cache\CacheManager;

/**
 * Twitch Live Status Block
 * Toont live/offline status + viewer count + game.
 */
final class TwitchLiveBlock extends AbstractBlock
{
    public function __construct(
        private readonly Connection   $db,
        private readonly CacheManager $cache,
        private readonly array        $moduleConfig = [],
    ) {}

    public function getSlug(): string { return 'twitch-live'; }
    public function getName(): string { return 'Twitch Live Status'; }

    public function getConfigSchema(): array
    {
        return [
            'channel'     => ['type' => 'string',  'label' => 'Twitch kanaalnaam'],
            'show_viewer' => ['type' => 'boolean', 'label' => 'Kijkers tonen', 'default' => true],
            'show_game'   => ['type' => 'boolean', 'label' => 'Game tonen',    'default' => true],
        ];
    }

    public function render(array $config, array $context = []): string
    {
        $channel = $config['channel'] ?: ($this->moduleConfig['channel_name'] ?? '');
        if (empty($channel)) {
            return '<p style="color:var(--muted);font-size:.85rem;">⚠️ Geen Twitch kanaal ingesteld.</p>';
        }

        $cacheKey = "twitch.live." . strtolower($channel);
        $stream   = $this->cache->remember($cacheKey, 90, function() use ($channel) {
            return $this->fetchLiveStatus($channel);
        });

        $channelUrl  = "https://twitch.tv/" . urlencode($channel);
        $channelSafe = htmlspecialchars($channel);

        if ($stream === null) {
            // OFFLINE
            return <<<HTML
            <div class="cf-twitch-block cf-twitch-offline">
                <div class="cf-twitch-header">
                    <span class="cf-twitch-logo">📺</span>
                    <div>
                        <a href="{$channelUrl}" target="_blank" rel="noopener" class="cf-twitch-channel">{$channelSafe}</a>
                        <div class="cf-twitch-status offline">⚫ Offline</div>
                    </div>
                </div>
            </div>
            HTML;
        }

        // LIVE
        $title       = htmlspecialchars($stream['title'] ?? '');
        $gameName    = htmlspecialchars($stream['game_name'] ?? '');
        $viewers     = number_format((int)($stream['viewer_count'] ?? 0));
        $thumbUrl    = str_replace(['{width}', '{height}'], ['440', '248'],
                        $stream['thumbnail_url'] ?? '');
        $thumbSafe   = htmlspecialchars($thumbUrl, ENT_QUOTES);

        $viewerHtml = ($config['show_viewer'] ?? true)
            ? "<span class='cf-twitch-viewers'>👁️ {$viewers}</span>"
            : '';
        $gameHtml   = ($config['show_game'] ?? true) && $gameName
            ? "<div class='cf-twitch-game'>🎮 {$gameName}</div>"
            : '';

        return <<<HTML
        <div class="cf-twitch-block cf-twitch-live">
            <a href="{$channelUrl}" target="_blank" rel="noopener">
                <div class="cf-twitch-thumb-wrap">
                    <img src="{$thumbSafe}" alt="{$channelSafe} stream" class="cf-twitch-thumb" loading="lazy">
                    <span class="cf-twitch-live-badge">🔴 LIVE</span>
                </div>
            </a>
            <div class="cf-twitch-info">
                <div class="cf-twitch-header">
                    <span class="cf-twitch-logo">📺</span>
                    <div>
                        <a href="{$channelUrl}" target="_blank" rel="noopener" class="cf-twitch-channel">{$channelSafe}</a>
                        <div class="cf-twitch-status live">🔴 LIVE {$viewerHtml}</div>
                    </div>
                </div>
                <div class="cf-twitch-title">{$title}</div>
                {$gameHtml}
            </div>
        </div>
        HTML;
    }

    private function fetchLiveStatus(string $channel): ?array
    {
        try {
            $clientId     = $this->moduleConfig['client_id'] ?? '';
            $clientSecret = $this->moduleConfig['client_secret'] ?? '';
            if (empty($clientId) || empty($clientSecret)) return null;

            // Haal app token op
            $ch = curl_init('https://id.twitch.tv/oauth2/token');
            curl_setopt_array($ch, [
                CURLOPT_POST           => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 5,
                CURLOPT_POSTFIELDS     => http_build_query([
                    'client_id'     => $clientId,
                    'client_secret' => $clientSecret,
                    'grant_type'    => 'client_credentials',
                ]),
            ]);
            $tokenData = json_decode(curl_exec($ch), true);
            curl_close($ch);

            $appToken = $tokenData['access_token'] ?? '';
            if (empty($appToken)) return null;

            // Haal stream op
            $ch = curl_init('https://api.twitch.tv/helix/streams?user_login=' . urlencode($channel));
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 5,
                CURLOPT_HTTPHEADER     => [
                    'Authorization: Bearer ' . $appToken,
                    'Client-Id: ' . $clientId,
                ],
            ]);
            $streamData = json_decode(curl_exec($ch), true);
            curl_close($ch);

            return $streamData['data'][0] ?? null;

        } catch (\Throwable) {
            return null;
        }
    }

    public function getCacheTtl(): int { return 90; }
}
