<?php
// ============================================================================
// Copyright (C) 2026  DieOuwe — GPL-3.0-or-later
// ============================================================================
declare(strict_types=1);
namespace CommunityFusion\Modules\Ollama;

use CommunityFusion\Core\Cache\CacheManager;

/**
 * OllamaClient
 *
 * PHP client voor de Ollama REST API (http://localhost:11434).
 * Ondersteunt: generate, chat (multi-turn), embeddings, model list.
 *
 * Ollama API docs: https://github.com/ollama/ollama/blob/main/docs/api.md
 *
 * Open WebUI integratie:
 *   Open WebUI (ghcr.io/open-webui/open-webui:v0.9.2) biedt een
 *   ChatGPT-achtige UI bovenop Ollama én ondersteunt de OpenAI-compatibele
 *   API (/api/chat/completions). Dit maakt het mogelijk om vanuit Blueprint CMS
 *   óf direct Ollama te benaderen óf via Open WebUI (voor meer controle,
 *   gebruikersbeheer, model-switching en RAG). Beide endpoints zijn ondersteund.
 */
final class OllamaClient
{
    private const DEFAULT_PORT = 11434;

    public function __construct(
        private readonly string       $host    = 'http://localhost:11434',
        private readonly string       $model   = 'llama3.2',
        private readonly int          $timeout = 30,
        private readonly ?CacheManager $cache  = null,
        private readonly string       $openWebUiUrl = '',
        private readonly string       $openWebUiKey = '',
    ) {}

    // ─── GENERATE (enkelvoudige prompt) ───────────────────────────────────

    /**
     * Stuur een prompt en ontvang een antwoord.
     * Geen streaming — wacht op volledig antwoord.
     *
     * @throws \RuntimeException als Ollama niet bereikbaar is
     */
    public function generate(
        string  $prompt,
        string  $systemPrompt = '',
        ?string $model        = null,
        array   $options      = [],
    ): string {
        $payload = [
            'model'  => $model ?? $this->model,
            'prompt' => $prompt,
            'stream' => false,
        ];

        if (!empty($systemPrompt)) {
            $payload['system'] = $systemPrompt;
        }

        if (!empty($options)) {
            $payload['options'] = $options; // temperature, top_p, etc.
        }

        $response = $this->post('/api/generate', $payload);
        return $response['response'] ?? '';
    }

    // ─── CHAT (multi-turn conversatie) ────────────────────────────────────

    /**
     * Chat met gespreksgeschiedenis.
     *
     * $messages = [
     *   ['role' => 'user',      'content' => 'Hallo!'],
     *   ['role' => 'assistant', 'content' => 'Hoi! Hoe kan ik helpen?'],
     *   ['role' => 'user',      'content' => 'Wat is WoW?'],
     * ]
     */
    public function chat(
        array   $messages,
        string  $systemPrompt = '',
        ?string $model        = null,
        array   $options      = [],
    ): string {
        $payload = [
            'model'    => $model ?? $this->model,
            'messages' => $messages,
            'stream'   => false,
        ];

        if (!empty($systemPrompt)) {
            array_unshift($payload['messages'], [
                'role'    => 'system',
                'content' => $systemPrompt,
            ]);
        }

        if (!empty($options)) {
            $payload['options'] = $options;
        }

        $response = $this->post('/api/chat', $payload);
        return $response['message']['content'] ?? '';
    }

    // ─── EMBEDDINGS ───────────────────────────────────────────────────────

    /**
     * Genereer een embedding vector voor tekst.
     * Handig voor semantisch zoeken.
     *
     * @return float[]
     */
    public function embed(string $text, ?string $model = null): array
    {
        $response = $this->post('/api/embed', [
            'model' => $model ?? $this->model,
            'input' => $text,
        ]);
        return $response['embeddings'][0] ?? [];
    }

    // ─── MODEL MANAGEMENT ─────────────────────────────────────────────────

    /**
     * Geef een lijst van beschikbare modellen terug.
     *
     * @return array<int, array{name: string, size: int, modified_at: string}>
     */
    public function listModels(): array
    {
        try {
            $response = $this->get('/api/tags');
            return $response['models'] ?? [];
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Controleer of Ollama bereikbaar is.
     */
    public function isAvailable(): bool
    {
        try {
            $ch = curl_init($this->host . '/api/tags');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 3,
                CURLOPT_CONNECTTIMEOUT => 2,
            ]);
            $code = null;
            curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            return $code === 200;
        } catch (\Throwable) {
            return false;
        }
    }

    // ─── OPEN WEBUI INTEGRATIE ────────────────────────────────────────────

    /**
     * Stuur een chat request via Open WebUI (OpenAI-compatible API).
     * Open WebUI v0.9.2 ondersteunt:
     *   POST /api/chat/completions (OpenAI-compatible)
     *   GET  /api/models           (beschikbare modellen)
     *
     * Gebruik dit als Open WebUI geconfigureerd is voor extra functies:
     * - Gebruikersbeheer
     * - RAG (Retrieval Augmented Generation)
     * - Model-switching via UI
     * - Conversation history
     */
    public function chatViaOpenWebUI(
        array   $messages,
        string  $systemPrompt = '',
        ?string $model        = null,
    ): string {
        if (empty($this->openWebUiUrl)) {
            // Fallback naar directe Ollama
            return $this->chat($messages, $systemPrompt, $model);
        }

        if (!empty($systemPrompt)) {
            array_unshift($messages, ['role' => 'system', 'content' => $systemPrompt]);
        }

        $payload = [
            'model'    => $model ?? $this->model,
            'messages' => $messages,
            'stream'   => false,
        ];

        $headers = ['Content-Type: application/json'];
        if (!empty($this->openWebUiKey)) {
            $headers[] = 'Authorization: Bearer ' . $this->openWebUiKey;
        }

        $url = rtrim($this->openWebUiUrl, '/') . '/api/chat/completions';
        $ch  = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => $headers,
        ]);

        $body   = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($status !== 200) {
            // Fallback naar directe Ollama
            return $this->chat($messages, '', $model);
        }

        $data = json_decode($body, true);
        return $data['choices'][0]['message']['content'] ?? '';
    }

    // ─── BLUEPRINT CMS SPECIFIEKE AI FUNCTIES ────────────────────────────

    /**
     * Genereer een samenvatting van een nieuwsartikel.
     */
    public function summarizeNews(string $content, string $title = '', int $maxWords = 80): string
    {
        $cacheKey = 'ollama.summary.' . md5($content);
        if ($this->cache) {
            $cached = $this->cache->get($cacheKey);
            if ($cached) return $cached;
        }

        $prompt = "Maak een korte samenvatting van maximaal {$maxWords} woorden van dit nieuwsartikel.\n\n";
        if ($title) $prompt .= "Titel: {$title}\n\n";
        $prompt .= "Artikel:\n" . substr($content, 0, 3000) . "\n\nSamenvatting:";

        $result = $this->generate($prompt, 'Je bent een redacteur die neutrale, beknopte samenvattingen schrijft. Antwoord alleen in het Nederlands.');

        if ($this->cache && $result) {
            $this->cache->set($cacheKey, $result, 3600);
        }

        return $result;
    }

    /**
     * Beoordeel een guild aanmelding en geef een eerste analyse.
     */
    public function analyzeGuildApplication(array $application): string
    {
        $prompt = <<<PROMPT
Analyseer deze guild aanmelding voor een World of Warcraft guild.
Geef een korte beoordeling (max 3 zinnen) op basis van:
- Klasse en specialisatie
- Item level
- Motivatie
- Ervaring

Aanmelding:
Karakter: {$application['character_name']}
Klasse/Spec: {$application['class']} - {$application['spec']}
Item level: {$application['item_level']}
Motivatie: {$application['about']}
Ervaring: {$application['experience']}

Geef alleen de beoordeling, geen extra uitleg.
PROMPT;

        return $this->generate(
            $prompt,
            'Je bent een ervaren WoW guild officer die aanmeldingen beoordeelt. Wees eerlijk maar vriendelijk. Antwoord in het Nederlands.'
        );
    }

    /**
     * Genereer community chatbot antwoord.
     * Contextueel: weet van de guild, het spel, en de community.
     */
    public function communityChat(
        array  $messages,
        string $guildName   = '',
        string $gameName    = 'World of Warcraft',
    ): string {
        $systemPrompt = "Je bent een vriendelijke community bot voor {$guildName}. ";
        $systemPrompt .= "De community speelt voornamelijk {$gameName}. ";
        $systemPrompt .= "Beantwoord vragen over de guild, het spel en de community in het Nederlands. ";
        $systemPrompt .= "Wees behulpzaam, positief en beknopt. Gebruik geen markdown in je antwoorden.";

        return $this->chat($messages, $systemPrompt);
    }

    /**
     * Genereer recruitment post voor de guild.
     */
    public function generateRecruitmentPost(array $guildInfo): string
    {
        $prompt = "Schrijf een aantrekkelijke guild recruitment post voor Discord of Reddit. ";
        $prompt .= "Guild naam: " . ($guildInfo['name'] ?? 'Onbekend') . ". ";
        $prompt .= "Zoekt: " . ($guildInfo['looking_for'] ?? 'nieuwe leden') . ". ";
        $prompt .= "Raid schema: " . ($guildInfo['schedule'] ?? 'nog te bepalen') . ". ";
        $prompt .= "Extra info: " . ($guildInfo['extra'] ?? '') . ". ";
        $prompt .= "Maximaal 150 woorden. Enthousiastisch en uitnodigend.";

        return $this->generate($prompt, 'Je bent een copywriter voor gaming communities. Schrijf in het Nederlands.');
    }

    // ─── HTTP HELPERS ─────────────────────────────────────────────────────

    private function post(string $path, array $data): array
    {
        $url = rtrim($this->host, '/') . $path;
        $ch  = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_POSTFIELDS     => json_encode($data),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        ]);

        $body   = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error  = curl_error($ch);
        curl_close($ch);

        if ($error || $status === 0) {
            throw new \RuntimeException("Ollama niet bereikbaar op {$this->host}: {$error}");
        }

        if ($status !== 200) {
            throw new \RuntimeException("Ollama API fout HTTP {$status}: " . substr($body, 0, 300));
        }

        return json_decode($body, true, flags: JSON_THROW_ON_ERROR);
    }

    private function get(string $path): array
    {
        $url = rtrim($this->host, '/') . $path;
        $ch  = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 8,
        ]);
        $body   = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($status !== 200) {
            throw new \RuntimeException("Ollama GET {$path} mislukt: HTTP {$status}");
        }

        return json_decode($body, true, flags: JSON_THROW_ON_ERROR);
    }
}

// ╔══════════════════════════════════════════════════════════════════════╗
// ║  File: OllamaClient.php | Role: Core | Version: 1.0.0               ║
// ║  Created: 2026-06-06 | Status: New                                  ║
// ║  Notes: Ollama REST API + Open WebUI v0.9.2 integratie              ║
// ║  Open WebUI: ghcr.io/open-webui/open-webui:v0.9.2-cuda             ║
// ║  Created by Dieouwe — www.slayeralliance.com                         ║
// ╚══════════════════════════════════════════════════════════════════════╝
