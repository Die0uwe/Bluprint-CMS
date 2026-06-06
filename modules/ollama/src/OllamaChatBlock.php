<?php
// ============================================================================
// Copyright (C) 2026  DieOuwe — GPL-3.0-or-later
// ============================================================================
declare(strict_types=1);
namespace CommunityFusion\Modules\Ollama;
use CommunityFusion\Blocks\AbstractBlock;

/**
 * Ollama Community Chat Block
 * Embed een AI chatbot in de sidebar.
 * Berichten worden via AJAX naar /api/ollama/chat gestuurd.
 */
final class OllamaChatBlock extends AbstractBlock
{
    public function __construct(
        private readonly OllamaClient $client,
        private readonly array        $config = [],
    ) {}

    public function getSlug(): string { return 'ollama-chat'; }
    public function getName(): string { return 'AI Community Chat (Ollama)'; }

    public function getConfigSchema(): array
    {
        return [
            'title'       => ['type' => 'string',  'label' => 'Titel', 'default' => '🤖 Community AI'],
            'placeholder' => ['type' => 'string',  'label' => 'Placeholder tekst', 'default' => 'Stel een vraag...'],
            'max_height'  => ['type' => 'integer', 'label' => 'Max hoogte (px)', 'default' => 400],
        ];
    }

    public function render(array $config, array $context = []): string
    {
        $available = $this->client->isAvailable();
        $title     = htmlspecialchars($config['title'] ?? '🤖 Community AI');
        $placeholder = htmlspecialchars($config['placeholder'] ?? 'Stel een vraag...', ENT_QUOTES);
        $maxH      = max(200, min(800, (int)($config['max_height'] ?? 400)));

        if (!$available) {
            return <<<HTML
            <div class="cf-ollama-chat cf-ollama-offline">
                <div class="cf-ollama-header">{$title}</div>
                <p class="cf-ollama-offline-msg">⚠️ AI niet beschikbaar. Zorg dat Ollama actief is.</p>
            </div>
            HTML;
        }

        $blockId = 'ollama-' . uniqid();

        return <<<HTML
        <div class="cf-ollama-chat" id="{$blockId}">
            <div class="cf-ollama-header">
                <span>{$title}</span>
                <span class="cf-ollama-status-dot" title="Ollama actief">🟢</span>
            </div>
            <div class="cf-ollama-messages" style="max-height:{$maxH}px" id="{$blockId}-msgs"></div>
            <div class="cf-ollama-input-row">
                <input class="cf-input cf-ollama-input" type="text"
                       id="{$blockId}-input"
                       placeholder="{$placeholder}"
                       autocomplete="off">
                <button class="cf-btn cf-ollama-send" onclick="ollamaChat('{$blockId}')">➤</button>
            </div>
        </div>
        <script>
        (function() {
          const history_{$blockId} = [];

          window.ollamaChat = window.ollamaChat || {};

          document.getElementById('{$blockId}-input').addEventListener('keydown', function(e) {
            if (e.key === 'Enter') ollamaChat('{$blockId}');
          });

          window['ollamaChat'] = function(id) {
            const input = document.getElementById(id + '-input');
            const msgs  = document.getElementById(id + '-msgs');
            const text  = input.value.trim();
            if (!text) return;

            input.value = '';
            history_{$blockId}.push({ role: 'user', content: text });

            // Toon user bericht
            msgs.innerHTML += '<div class="cf-ollama-msg cf-ollama-user"><span>' + escapeHtml(text) + '</span></div>';

            // Toon loading
            const loadId = 'load-' + Date.now();
            msgs.innerHTML += '<div class="cf-ollama-msg cf-ollama-ai" id="' + loadId + '"><span class="cf-ollama-typing">●●●</span></div>';
            msgs.scrollTop = msgs.scrollHeight;

            fetch('/api/ollama/chat', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
              body: JSON.stringify({ messages: history_{$blockId} })
            })
            .then(r => r.json())
            .then(data => {
              const reply = data.reply || 'Sorry, ik kon geen antwoord genereren.';
              history_{$blockId}.push({ role: 'assistant', content: reply });
              document.getElementById(loadId).innerHTML = '<span>' + escapeHtml(reply) + '</span>';
              msgs.scrollTop = msgs.scrollHeight;
            })
            .catch(() => {
              document.getElementById(loadId).innerHTML = '<span style="color:var(--error)">⚠️ Fout bij verbinding.</span>';
            });
          };

          function escapeHtml(t) {
            return t.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
          }
        })();
        </script>
        HTML;
    }

    public function getCacheTtl(): int { return 0; } // Nooit cachen
}
