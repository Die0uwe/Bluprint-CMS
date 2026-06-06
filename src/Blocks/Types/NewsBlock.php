<?php
// ============================================================================
// Copyright (C) 2026  DieOuwe — GPL-3.0-or-later
// ============================================================================
declare(strict_types=1);
namespace CommunityFusion\Blocks\Types;
use CommunityFusion\Blocks\AbstractBlock;
use CommunityFusion\Core\Database\Connection;

/** Toont de laatste X nieuwsberichten als compacte lijst */
final class NewsBlock extends AbstractBlock
{
    public function __construct(private readonly Connection $db) {}

    public function getSlug(): string { return 'news-latest'; }
    public function getName(): string { return 'Laatste Nieuws'; }

    public function getConfigSchema(): array
    {
        return [
            'count'      => ['type' => 'integer', 'label' => 'Aantal artikelen', 'default' => 5, 'min' => 1, 'max' => 20],
            'show_image' => ['type' => 'boolean', 'label' => 'Afbeelding tonen', 'default' => false],
        ];
    }

    public function render(array $config, array $context = []): string
    {
        $count = max(1, min(20, (int) ($config['count'] ?? 5)));

        $items = $this->db->fetchAll(
            "SELECT slug, title, published_at FROM cf_news
             WHERE status = 'published' AND deleted_at IS NULL
             ORDER BY is_sticky DESC, published_at DESC
             LIMIT ?",
            [$count]
        );

        if (empty($items)) {
            return '<p class="cf-block-empty">Geen nieuws beschikbaar.</p>';
        }

        $html = '<ul class="cf-block-news-list">';
        foreach ($items as $item) {
            $slug  = htmlspecialchars($item['slug']);
            $title = htmlspecialchars($item['title']);
            $date  = $item['published_at'] ? date('d M', strtotime($item['published_at'])) : '';
            $html .= <<<HTML
            <li class="cf-block-news-item">
                <a href="/news/{$slug}">{$title}</a>
                <span class="cf-block-news-date">{$date}</span>
            </li>
            HTML;
        }
        $html .= '</ul>';
        $html .= '<a href="/news" class="cf-block-more">Alle artikelen →</a>';

        return $html;
    }

    public function getCacheTtl(): int { return 300; }
}
