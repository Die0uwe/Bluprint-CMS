<?php
// ============================================================================
// Copyright (C) 2026  DieOuwe — GPL-3.0-or-later
// ============================================================================
declare(strict_types=1);
namespace CommunityFusion\Modules\Warcraft;
use CommunityFusion\Blocks\AbstractBlock;

final class WowMythicProgressBlock extends AbstractBlock
{
    public function __construct(
        private readonly BlizzardApiClient $api,
        private readonly array             $config = [],
    ) {}

    public function getSlug(): string { return 'wow-mythic-progress'; }
    public function getName(): string { return 'WoW Raid Progress'; }

    public function getConfigSchema(): array
    {
        return [
            'realm'      => ['type' => 'string', 'label' => 'Realm'],
            'guild_name' => ['type' => 'string', 'label' => 'Guild naam'],
        ];
    }

    public function render(array $config, array $context = []): string
    {
        $realm = $config['realm'] ?: ($this->config['realm'] ?? '');
        $guild = $config['guild_name'] ?: ($this->config['guild_name'] ?? '');

        if (empty($realm) || empty($guild)) {
            return '<p class="cf-block-empty">⚙️ Configureer realm en guild naam.</p>';
        }

        $data = $this->api->getRaidProgress($realm, $guild);

        if (!$data || empty($data['raid_progression'])) {
            return '<p class="cf-block-empty">🐉 Geen raid data beschikbaar via Raider.IO.</p>';
        }

        $guildSafe  = htmlspecialchars($guild);
        $progression = $data['raid_progression'];

        // Neem de eerste (meest recente) raid
        $latestRaid = array_key_first($progression);
        $prog       = $progression[$latestRaid] ?? [];
        $raidName   = htmlspecialchars(ucwords(str_replace('-', ' ', $latestRaid)));

        $nm  = (int)($prog['normal_bosses_killed'] ?? 0);
        $nmT = (int)($prog['total_bosses'] ?? 0);
        $hm  = (int)($prog['heroic_bosses_killed'] ?? 0);
        $mm  = (int)($prog['mythic_bosses_killed'] ?? 0);

        $nmPct  = $nmT > 0 ? round(($nm / $nmT) * 100) : 0;
        $hmPct  = $nmT > 0 ? round(($hm / $nmT) * 100) : 0;
        $mmPct  = $nmT > 0 ? round(($mm / $nmT) * 100) : 0;

        return <<<HTML
        <div class="cf-wow-progress">
            <div class="cf-wow-header">
                <span style="font-size:1.4rem;">🐉</span>
                <div>
                    <div class="cf-wow-guild-name">{$guildSafe}</div>
                    <div class="cf-wow-raid-name">{$raidName}</div>
                </div>
            </div>
            <div class="cf-wow-tiers">
                <div class="cf-wow-tier">
                    <span class="cf-wow-tier-label" style="color:#aad372">Normal</span>
                    <div class="cf-wow-bar-wrap"><div class="cf-wow-bar" style="width:{$nmPct}%;background:#aad372"></div></div>
                    <span class="cf-wow-tier-count">{$nm}/{$nmT}</span>
                </div>
                <div class="cf-wow-tier">
                    <span class="cf-wow-tier-label" style="color:#f48cba">Heroic</span>
                    <div class="cf-wow-bar-wrap"><div class="cf-wow-bar" style="width:{$hmPct}%;background:#f48cba"></div></div>
                    <span class="cf-wow-tier-count">{$hm}/{$nmT}</span>
                </div>
                <div class="cf-wow-tier">
                    <span class="cf-wow-tier-label" style="color:#c41e3a">Mythic</span>
                    <div class="cf-wow-bar-wrap"><div class="cf-wow-bar" style="width:{$mmPct}%;background:#c41e3a"></div></div>
                    <span class="cf-wow-tier-count">{$mm}/{$nmT}</span>
                </div>
            </div>
        </div>
        HTML;
    }

    public function getCacheTtl(): int { return 1800; }
}
