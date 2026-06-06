<?php
// ============================================================================
// Copyright (C) 2026  DieOuwe — GPL-3.0-or-later
// ============================================================================

declare(strict_types=1);

namespace CommunityFusion\Modules\Twitch;

use CommunityFusion\Blocks\AbstractBlock;

/**
 * Twitch Stream Embed Block
 * Embed de Twitch player direct op de pagina.
 */
final class TwitchStreamBlock extends AbstractBlock
{
    public function __construct(private readonly array $moduleConfig = []) {}

    public function getSlug(): string { return 'twitch-stream'; }
    public function getName(): string { return 'Twitch Stream Embed'; }

    public function getConfigSchema(): array
    {
        return [
            'channel' => ['type' => 'string',  'label' => 'Twitch kanaalnaam'],
            'height'  => ['type' => 'integer', 'label' => 'Hoogte (px)', 'default' => 360],
            'chat'    => ['type' => 'boolean', 'label' => 'Chat naast stream', 'default' => false],
            'muted'   => ['type' => 'boolean', 'label' => 'Gedempt starten',   'default' => true],
        ];
    }

    public function render(array $config, array $context = []): string
    {
        $channel = htmlspecialchars(
            $config['channel'] ?: ($this->moduleConfig['channel_name'] ?? ''),
            ENT_QUOTES
        );

        if (empty($channel)) {
            return '<p style="color:var(--muted);font-size:.85rem;">⚠️ Geen Twitch kanaal ingesteld.</p>';
        }

        $height = max(200, min(800, (int) ($config['height'] ?? 360)));
        $muted  = ($config['muted'] ?? true) ? '&muted=true' : '';
        $parent = htmlspecialchars(parse_url($_ENV['APP_URL'] ?? 'localhost', PHP_URL_HOST) ?? 'localhost', ENT_QUOTES);

        if (!empty($config['chat'])) {
            return <<<HTML
            <div class="cf-twitch-embed-wrap" style="display:grid;grid-template-columns:2fr 1fr;gap:.5rem;height:{$height}px;">
                <iframe
                    src="https://player.twitch.tv/?channel={$channel}&parent={$parent}{$muted}"
                    frameborder="0" allowfullscreen scrolling="no"
                    style="width:100%;height:100%;border-radius:8px 0 0 8px;">
                </iframe>
                <iframe
                    src="https://www.twitch.tv/embed/{$channel}/chat?parent={$parent}&darkpopout"
                    frameborder="0"
                    style="width:100%;height:100%;border-radius:0 8px 8px 0;">
                </iframe>
            </div>
            HTML;
        }

        return <<<HTML
        <div class="cf-twitch-embed-wrap">
            <iframe
                src="https://player.twitch.tv/?channel={$channel}&parent={$parent}{$muted}"
                height="{$height}"
                width="100%"
                frameborder="0"
                allowfullscreen
                scrolling="no"
                style="border-radius:8px;">
            </iframe>
        </div>
        HTML;
    }

    public function getCacheTtl(): int { return 0; } // Live embed, nooit cachen
}
