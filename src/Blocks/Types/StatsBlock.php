<?php
// ============================================================================
// Copyright (C) 2026  DieOuwe — GPL-3.0-or-later
// ============================================================================
declare(strict_types=1);
namespace CommunityFusion\Blocks\Types;
use CommunityFusion\Blocks\AbstractBlock;
use CommunityFusion\Core\Database\Connection;

/** Site-statistieken: leden, artikelen, pagina's */
final class StatsBlock extends AbstractBlock
{
    public function __construct(private readonly Connection $db) {}

    public function getSlug(): string { return 'site-stats'; }
    public function getName(): string { return 'Site Statistieken'; }

    public function getConfigSchema(): array
    {
        return [
            'show_users' => ['type' => 'boolean', 'label' => 'Leden tonen', 'default' => true],
            'show_news'  => ['type' => 'boolean', 'label' => 'Artikelen tonen', 'default' => true],
            'show_pages' => ['type' => 'boolean', 'label' => "Pagina's tonen", 'default' => false],
        ];
    }

    public function render(array $config, array $context = []): string
    {
        $stats = [];

        if ($config['show_users'] ?? true) {
            $r = $this->db->fetchOne("SELECT COUNT(*) as c FROM cf_users WHERE is_active=1 AND deleted_at IS NULL");
            $stats[] = ['icon' => '👥', 'label' => 'Leden', 'value' => $r['c'] ?? 0];
        }
        if ($config['show_news'] ?? true) {
            $r = $this->db->fetchOne("SELECT COUNT(*) as c FROM cf_news WHERE status='published' AND deleted_at IS NULL");
            $stats[] = ['icon' => '📰', 'label' => 'Artikelen', 'value' => $r['c'] ?? 0];
        }
        if ($config['show_pages'] ?? false) {
            $r = $this->db->fetchOne("SELECT COUNT(*) as c FROM cf_pages WHERE status='published' AND deleted_at IS NULL");
            $stats[] = ['icon' => '📄', 'label' => "Pagina's", 'value' => $r['c'] ?? 0];
        }

        $html = '<div class="cf-block-stats">';
        foreach ($stats as $s) {
            $val = number_format((int)$s['value']);
            $html .= <<<HTML
            <div class="cf-block-stat-item">
                <span class="cf-stat-icon">{$s['icon']}</span>
                <span class="cf-stat-value">{$val}</span>
                <span class="cf-stat-label">{$s['label']}</span>
            </div>
            HTML;
        }
        $html .= '</div>';
        return $html;
    }

    public function getCacheTtl(): int { return 600; }
}
