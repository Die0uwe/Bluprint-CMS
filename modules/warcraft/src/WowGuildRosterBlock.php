<?php
// ============================================================================
// Copyright (C) 2026  DieOuwe — GPL-3.0-or-later
// ============================================================================
declare(strict_types=1);
namespace CommunityFusion\Modules\Warcraft;
use CommunityFusion\Blocks\AbstractBlock;

final class WowGuildRosterBlock extends AbstractBlock
{
    public function __construct(
        private readonly BlizzardApiClient $api,
        private readonly array             $config = [],
    ) {}

    public function getSlug(): string { return 'wow-guild-roster'; }
    public function getName(): string { return 'WoW Guild Roster'; }

    public function getConfigSchema(): array
    {
        return [
            'realm'      => ['type' => 'string',  'label' => 'Realm (overschrijft module)'],
            'guild_name' => ['type' => 'string',  'label' => 'Guild naam (overschrijft module)'],
            'limit'      => ['type' => 'integer', 'label' => 'Max leden tonen', 'default' => 10],
            'min_rank'   => ['type' => 'integer', 'label' => 'Max rang (0=GM, 9=laagst)', 'default' => 9],
        ];
    }

    public function render(array $config, array $context = []): string
    {
        $realm = $config['realm'] ?: ($this->config['realm'] ?? '');
        $guild = $config['guild_name'] ?: ($this->config['guild_name'] ?? '');

        if (empty($realm) || empty($guild)) {
            return '<p class="cf-block-empty">⚙️ Configureer realm en guild naam in de WoW module instellingen.</p>';
        }

        $roster = $this->api->getGuildRoster($realm, $guild);

        if (!$roster || empty($roster['members'])) {
            return '<p class="cf-block-empty">🐉 Geen roster data beschikbaar. Controleer je API-credentials.</p>';
        }

        $limit   = max(1, min(50, (int)($config['limit'] ?? 10)));
        $minRank = (int)($config['min_rank'] ?? 9);
        $members = array_filter($roster['members'], fn($m) => ($m['rank'] ?? 99) <= $minRank);
        $members = array_slice(array_values($members), 0, $limit);

        $guildSafe = htmlspecialchars($guild);
        $html = <<<HTML
        <div class="cf-wow-roster">
            <div class="cf-wow-header">
                <img src="/assets/img/wow-icon.png" class="cf-wow-logo" alt="WoW" onerror="this.style.display='none'">
                <div>
                    <div class="cf-wow-guild-name">{$guildSafe}</div>
                    <div class="cf-wow-member-count"><?= count($roster['members']) ?> leden</div>
                </div>
            </div>
            <div class="cf-wow-member-list">
        HTML;

        $classColors = [
            'Death Knight'=>'#c41e3a','Demon Hunter'=>'#a330c9','Druid'=>'#ff7c0a',
            'Evoker'=>'#33937f','Hunter'=>'#aad372','Mage'=>'#3fc7eb','Monk'=>'#00ff98',
            'Paladin'=>'#f48cba','Priest'=>'#d4d4d4','Rogue'=>'#fff468',
            'Shaman'=>'#0070dd','Warlock'=>'#8788ee','Warrior'=>'#c69b3a',
        ];

        foreach ($members as $m) {
            $char      = $m['character'] ?? [];
            $name      = htmlspecialchars($char['name'] ?? 'Onbekend');
            $className = $char['playable_class']['name'] ?? '';
            $color     = htmlspecialchars($classColors[$className] ?? '#e2e8f0', ENT_QUOTES);
            $rank      = (int)($m['rank'] ?? 0);
            $level     = (int)($char['level'] ?? 0);
            $rankLabel = $rank === 0 ? '👑' : ($rank <= 2 ? '⭐' : '');

            $html .= <<<HTML
            <div class="cf-wow-member-row">
                <span class="cf-wow-char-name" style="color:{$color}">{$rankLabel}{$name}</span>
                <span class="cf-wow-char-meta">{$className}</span>
                <span class="cf-wow-char-level">Lv.{$level}</span>
            </div>
            HTML;
        }

        $html .= '</div><a href="/wow/guild" class="cf-block-more">Volledig roster →</a></div>';
        return $html;
    }

    public function getCacheTtl(): int { return 600; }
}
