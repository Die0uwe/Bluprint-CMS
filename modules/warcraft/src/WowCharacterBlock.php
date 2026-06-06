<?php
// ============================================================================
// Copyright (C) 2026  DieOuwe — GPL-3.0-or-later
// ============================================================================
declare(strict_types=1);
namespace CommunityFusion\Modules\Warcraft;
use CommunityFusion\Blocks\AbstractBlock;

final class WowCharacterBlock extends AbstractBlock
{
    public function __construct(
        private readonly BlizzardApiClient $api,
        private readonly array             $config = [],
    ) {}

    public function getSlug(): string { return 'wow-character'; }
    public function getName(): string { return 'WoW Character'; }

    public function getConfigSchema(): array
    {
        return [
            'realm'     => ['type' => 'string', 'label' => 'Realm'],
            'character' => ['type' => 'string', 'label' => 'Karakternaam', 'required' => true],
            'show_rio'  => ['type' => 'boolean', 'label' => 'Raider.IO score tonen', 'default' => true],
        ];
    }

    public function render(array $config, array $context = []): string
    {
        $realm = $config['realm'] ?: ($this->config['realm'] ?? '');
        $char  = $config['character'] ?? '';

        if (empty($realm) || empty($char)) {
            return '<p class="cf-block-empty">⚙️ Configureer realm en karakternaam.</p>';
        }

        $data = $this->api->getCharacter($realm, $char);
        if (!$data) {
            return "<p class='cf-block-empty'>🐉 Karakter '{$char}' niet gevonden.</p>";
        }

        $name      = htmlspecialchars($data['name'] ?? $char);
        $realm_s   = htmlspecialchars($data['realm']['name'] ?? $realm);
        $level     = (int)($data['level'] ?? 0);
        $className = htmlspecialchars($data['character_class']['name'] ?? '');
        $specName  = htmlspecialchars($data['active_spec']['name'] ?? '');
        $ilvl      = (int)($data['average_item_level'] ?? 0);
        $equippedIlvl = (int)($data['equipped_item_level'] ?? 0);
        $faction   = $data['faction']['name'] ?? '';
        $factionColor = strtolower($faction) === 'horde' ? '#c41e3a' : '#3fc7eb';
        $factionIcon  = strtolower($faction) === 'horde' ? '🔴' : '🔵';

        $classColors = ['Death Knight'=>'#c41e3a','Demon Hunter'=>'#a330c9','Druid'=>'#ff7c0a',
            'Evoker'=>'#33937f','Hunter'=>'#aad372','Mage'=>'#3fc7eb','Monk'=>'#00ff98',
            'Paladin'=>'#f48cba','Priest'=>'#d4d4d4','Rogue'=>'#fff468',
            'Shaman'=>'#0070dd','Warlock'=>'#8788ee','Warrior'=>'#c69b3a'];
        $classColor = htmlspecialchars($classColors[$data['character_class']['name'] ?? ''] ?? '#e2e8f0', ENT_QUOTES);

        // Raider.IO score
        $rioHtml = '';
        if ($config['show_rio'] ?? true) {
            $rio = $this->api->getMythicPlusScore($realm, $char);
            if ($rio) {
                $score = (int)($rio['mythic_plus_scores_by_season'][0]['scores']['all'] ?? 0);
                if ($score > 0) {
                    $scoreColor = $score >= 3000 ? '#ff8000' : ($score >= 2000 ? '#a335ee' : ($score >= 1000 ? '#0070dd' : '#1eff00'));
                    $rioHtml = "<div class='cf-wow-rio' style='color:{$scoreColor}'>🔑 Raider.IO Score: <strong>{$score}</strong></div>";
                }
            }
        }

        return <<<HTML
        <div class="cf-wow-character">
            <div class="cf-wow-char-header">
                <div>
                    <div class="cf-wow-char-name-large" style="color:{$classColor}">{$name}</div>
                    <div class="cf-wow-char-subtitle" style="color:var(--muted)">{$specName} {$className} — {$realm_s}</div>
                    <div class="cf-wow-char-subtitle">{$factionIcon} {$faction} · Level {$level}</div>
                </div>
            </div>
            <div class="cf-wow-char-stats">
                <div class="cf-wow-char-stat">
                    <span class="cf-wow-stat-val" style="color:var(--gold)">{$equippedIlvl}</span>
                    <span class="cf-wow-stat-lbl">Equipped iLvl</span>
                </div>
                <div class="cf-wow-char-stat">
                    <span class="cf-wow-stat-val">{$ilvl}</span>
                    <span class="cf-wow-stat-lbl">Avg iLvl</span>
                </div>
            </div>
            {$rioHtml}
            <a href="/wow/character/{$char}" class="cf-block-more">Volledig profiel →</a>
        </div>
        HTML;
    }

    public function getCacheTtl(): int { return 300; }
}
