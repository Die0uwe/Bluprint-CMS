<?php
// ============================================================================
// Copyright (C) 2026  DieOuwe — GPL-3.0-or-later
// ============================================================================
declare(strict_types=1);
namespace CommunityFusion\Modules\Guild;
use CommunityFusion\Blocks\AbstractBlock;
use CommunityFusion\Core\Database\Connection;

final class GuildMembersBlock extends AbstractBlock
{
    public function __construct(private readonly Connection $db) {}
    public function getSlug(): string { return 'guild-members'; }
    public function getName(): string { return 'Guild Leden'; }

    public function getConfigSchema(): array
    {
        return [
            'limit'      => ['type' => 'integer', 'label' => 'Max leden', 'default' => 10],
            'show_rank'  => ['type' => 'boolean', 'label' => 'Rang tonen', 'default' => true],
            'show_class' => ['type' => 'boolean', 'label' => 'Class tonen', 'default' => true],
            'show_ilvl'  => ['type' => 'boolean', 'label' => 'Item Level tonen', 'default' => true],
        ];
    }

    public function render(array $config, array $context = []): string
    {
        $limit = max(1, min(50, (int)($config['limit'] ?? 10)));

        $members = $this->db->fetchAll(
            "SELECT gm.*, gr.display_name as rank_name, gr.color as rank_color
             FROM cf_guild_members gm
             LEFT JOIN cf_guild_ranks gr ON gr.id = gm.rank_id
             WHERE gm.is_active = 1
             ORDER BY gr.priority DESC, gm.character_name ASC
             LIMIT ?",
            [$limit]
        );

        if (empty($members)) {
            return '<p class="cf-block-empty">Geen guild leden gevonden.</p>';
        }

        $wowClassIcons = [
            'Death Knight' => '🩸', 'Demon Hunter' => '👁️', 'Druid' => '🌿',
            'Evoker' => '🐉', 'Hunter' => '🏹', 'Mage' => '🔵', 'Monk' => '☯️',
            'Paladin' => '⚔️', 'Priest' => '✨', 'Rogue' => '🗡️',
            'Shaman' => '⚡', 'Warlock' => '🟣', 'Warrior' => '🛡️',
        ];

        $html = '<div class="cf-guild-members">';
        foreach ($members as $m) {
            $name       = htmlspecialchars($m['character_name']);
            $rankName   = htmlspecialchars($m['rank_name'] ?? '');
            $rankColor  = htmlspecialchars($m['rank_color'] ?? '#64748b', ENT_QUOTES);
            $class      = htmlspecialchars($m['class'] ?? '');
            $classIcon  = $wowClassIcons[$m['class'] ?? ''] ?? '⚔️';
            $ilvl       = $m['item_level'] ? (int)$m['item_level'] : null;

            $rankHtml  = ($config['show_rank'] ?? true) && $rankName
                ? "<span class='cf-guild-rank' style='color:{$rankColor}'>{$rankName}</span>"
                : '';
            $classHtml = ($config['show_class'] ?? true) && $class
                ? "<span class='cf-guild-class'>{$classIcon} {$class}</span>"
                : '';
            $ilvlHtml  = ($config['show_ilvl'] ?? true) && $ilvl
                ? "<span class='cf-guild-ilvl'>⚙️ {$ilvl}</span>"
                : '';

            $html .= <<<HTML
            <div class="cf-guild-member-row">
                <span class="cf-guild-name">{$name}</span>
                <div class="cf-guild-member-meta">{$classHtml}{$rankHtml}{$ilvlHtml}</div>
            </div>
            HTML;
        }
        $html .= '</div>';
        $html .= '<a href="/guild/members" class="cf-block-more">Volledig roster →</a>';
        return $html;
    }

    public function getCacheTtl(): int { return 300; }
}
