<?php
// ============================================================================
// Copyright (C) 2026  DieOuwe — GPL-3.0-or-later
// ============================================================================
declare(strict_types=1);
namespace CommunityFusion\Modules\Guild;
use CommunityFusion\Blocks\AbstractBlock;
use CommunityFusion\Core\Database\Connection;

final class GuildInfoBlock extends AbstractBlock
{
    public function __construct(private readonly Connection $db) {}
    public function getSlug(): string { return 'guild-info'; }
    public function getName(): string { return 'Guild Info'; }
    public function getConfigSchema(): array { return []; }

    public function render(array $config, array $context = []): string
    {
        $totalMembers = $this->db->fetchOne("SELECT COUNT(*) as c FROM cf_guild_members WHERE is_active = 1")['c'] ?? 0;
        $pendingApps  = $this->db->fetchOne("SELECT COUNT(*) as c FROM cf_guild_applications WHERE status = 'pending'")['c'] ?? 0;
        $teams        = $this->db->fetchAll("SELECT name, type, is_recruiting FROM cf_guild_teams ORDER BY type");

        $teamsHtml = '';
        foreach ($teams as $team) {
            $name = htmlspecialchars($team['name']);
            $type = htmlspecialchars(ucfirst($team['type']));
            $rec  = $team['is_recruiting'] ? '<span class="cf-badge cf-badge-green">Recruiting</span>' : '';
            $teamsHtml .= "<div class='cf-guild-team-row'><span>{$name}</span><span class='cf-guild-team-type'>{$type}</span>{$rec}</div>";
        }

        return <<<HTML
        <div class="cf-guild-info">
            <div class="cf-guild-stats">
                <div class="cf-guild-stat"><span class="cf-guild-stat-val">{$totalMembers}</span><span class="cf-guild-stat-lbl">Leden</span></div>
                <div class="cf-guild-stat"><span class="cf-guild-stat-val">{$pendingApps}</span><span class="cf-guild-stat-lbl">Aanmeldingen</span></div>
            </div>
            {$teamsHtml}
            <a href="/guild/apply" class="cf-btn" style="width:100%;justify-content:center;margin-top:.8rem;font-size:.82rem;">⚔️ Aanmelden</a>
        </div>
        HTML;
    }

    public function getCacheTtl(): int { return 300; }
}
