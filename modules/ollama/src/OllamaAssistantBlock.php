<?php
// ============================================================================
// Copyright (C) 2026  DieOuwe — GPL-3.0-or-later
// ============================================================================
declare(strict_types=1);
namespace CommunityFusion\Modules\Ollama;
use CommunityFusion\Blocks\AbstractBlock;

final class OllamaAssistantBlock extends AbstractBlock
{
    public function __construct(
        private readonly OllamaClient $client,
        private readonly array        $config = [],
    ) {}

    public function getSlug(): string { return 'ollama-assistant'; }
    public function getName(): string { return 'AI Assistent (Ollama)'; }

    public function getConfigSchema(): array
    {
        return [
            'mode'  => ['type' => 'select', 'label' => 'Modus',
                        'options' => ['recruitment', 'tips', 'welcome'], 'default' => 'welcome'],
            'title' => ['type' => 'string', 'label' => 'Titel', 'default' => '🤖 AI Tip van de dag'],
        ];
    }

    public function render(array $config, array $context = []): string
    {
        $title = htmlspecialchars($config['title'] ?? '🤖 AI Tip van de dag');
        $mode  = $config['mode'] ?? 'welcome';

        if (!$this->client->isAvailable()) {
            return '<p class="cf-block-empty">🤖 AI niet beschikbaar.</p>';
        }

        try {
            $content = match ($mode) {
                'recruitment' => $this->client->generateRecruitmentPost([
                    'name'        => $this->config['guild_name'] ?? 'Onze Guild',
                    'looking_for' => 'enthousiaste spelers',
                    'schedule'    => 'Woensdag + Donderdag 20:00-23:00',
                    'extra'       => '',
                ]),
                'tips' => $this->client->generate(
                    'Geef één korte, praktische WoW gaming tip voor vandaag. Max 2 zinnen.',
                    'Je bent een ervaren WoW-speler. Geef een nuttige en concrete tip.'
                ),
                default => $this->client->generate(
                    'Schrijf een korte welkomstboodschap voor een gaming community website. Max 2 zinnen.',
                    'Je bent een enthousiaste community manager. Antwoord in het Nederlands.'
                ),
            };
        } catch (\Throwable $e) {
            $content = 'AI assistent tijdelijk niet beschikbaar.';
        }

        $contentSafe = nl2br(htmlspecialchars($content));

        return <<<HTML
        <div class="cf-ollama-assistant">
            <div class="cf-ollama-header">{$title}</div>
            <div class="cf-ollama-content">{$contentSafe}</div>
            <div class="cf-ollama-powered">Powered by Ollama 🤖</div>
        </div>
        HTML;
    }

    public function getCacheTtl(): int { return 3600; } // 1u — tip van de dag
}
