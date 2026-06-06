<?php
// ============================================================================
// Copyright (C) 2026  DieOuwe — GPL-3.0-or-later
// ============================================================================
declare(strict_types=1);
namespace CommunityFusion\Blocks\Types;
use CommunityFusion\Blocks\AbstractBlock;

/** Vrij HTML blok — alleen voor admins bedoeld */
final class HtmlBlock extends AbstractBlock
{
    public function getSlug(): string { return 'html'; }
    public function getName(): string { return 'HTML Blok'; }

    public function getConfigSchema(): array
    {
        return [
            'content' => ['type' => 'code', 'label' => 'HTML inhoud', 'required' => true],
        ];
    }

    public function render(array $config, array $context = []): string
    {
        // Geen escaping — admin-only, vertrouwde invoer
        return $config['content'] ?? '';
    }

    public function getCacheTtl(): int { return 1800; }
}
