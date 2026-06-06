<?php
// ============================================================================
// Copyright (C) 2026  DieOuwe — GPL-3.0-or-later
// ============================================================================
declare(strict_types=1);
namespace CommunityFusion\Blocks\Types;
use CommunityFusion\Blocks\AbstractBlock;
use CommunityFusion\Core\Security\CsrfProtection;

/** Login formulier / welkom-bericht als ingelogd */
final class LoginBlock extends AbstractBlock
{
    public function getSlug(): string { return 'login'; }
    public function getName(): string { return 'Login Blok'; }
    public function getConfigSchema(): array { return []; }

    public function render(array $config, array $context = []): string
    {
        $user = $context['user'] ?? null;

        if ($user) {
            $name = htmlspecialchars($user['display_name'] ?? $user['username']);
            return <<<HTML
            <div class="cf-block-login cf-block-login--logged-in">
                <div class="cf-block-login-avatar">👤</div>
                <div>
                    <strong>{$name}</strong>
                    <div class="cf-block-login-links">
                        <a href="/profiel">Profiel</a> ·
                        <a href="/admin">Admin</a> ·
                        <a href="/logout">Uitloggen</a>
                    </div>
                </div>
            </div>
            HTML;
        }

        $csrf = CsrfProtection::field();
        return <<<HTML
        <form class="cf-block-login" method="POST" action="/login">
            {$csrf}
            <input class="cf-input" type="text" name="identifier" placeholder="Gebruikersnaam / e-mail">
            <input class="cf-input" type="password" name="password" placeholder="Wachtwoord">
            <button type="submit" class="cf-btn" style="width:100%;justify-content:center;margin-top:.5rem;">Inloggen</button>
            <a href="/register" class="cf-block-login-register">Nog geen account? Registreer hier</a>
        </form>
        HTML;
    }

    public function getCacheTtl(): int { return 0; } // Nooit cachen — user-afhankelijk
}
