<?php
// ============================================================================
// Copyright (C) 2026  DieOuwe — GPL-3.0-or-later
// ============================================================================
declare(strict_types=1);
namespace CommunityFusion\Modules\Guild;
use CommunityFusion\Blocks\AbstractBlock;
use CommunityFusion\Core\Database\Connection;

final class GuildRecruitmentBlock extends AbstractBlock
{
    public function __construct(private readonly Connection $db) {}
    public function getSlug(): string { return 'guild-recruitment'; }
    public function getName(): string { return 'Guild Recruitment'; }
    public function getConfigSchema(): array { return [
        'text' => ['type' => 'textarea', 'label' => 'Recruitment tekst'],
    ]; }

    public function render(array $config, array $context = []): string
    {
        $text = nl2br(htmlspecialchars($config['text'] ?? 'Wij zoeken nieuwe leden voor onze guild!'));
        $teams = $this->db->fetchAll(
            "SELECT name, type FROM cf_guild_teams WHERE is_recruiting = 1 ORDER BY priority DESC LIMIT 5"
        );
        $teamsHtml = '';
        foreach ($teams as $t) {
            $teamsHtml .= '<li>' . htmlspecialchars($t['name']) . ' <span style="color:var(--muted)">(' . ucfirst($t['type']) . ')</span></li>';
        }
        $listHtml = $teamsHtml ? "<ul style='margin:.5rem 0 .8rem 1rem;font-size:.85rem;'>{$teamsHtml}</ul>" : '';

        return <<<HTML
        <div class="cf-guild-recruitment">
            <p style="font-size:.875rem;color:var(--text-dim);margin-bottom:.7rem;">{$text}</p>
            {$listHtml}
            <a href="/guild/apply" class="cf-btn" style="width:100%;justify-content:center;font-size:.82rem;">✍️ Aanmeldingsformulier</a>
        </div>
        HTML;
    }

    public function getCacheTtl(): int { return 600; }
}
