<?php
// ============================================================================
// Copyright (C) 2026  DieOuwe — GPL-3.0-or-later
// ============================================================================

declare(strict_types=1);

namespace CommunityFusion\Modules\Twitch;

use CommunityFusion\Core\Auth\OAuth\OAuthClient;

/**
 * Twitch OAuth2 Client (Authorization Code Flow)
 * API: https://api.twitch.tv/helix
 * Auth: https://id.twitch.tv/oauth2
 */
final class TwitchOAuth extends OAuthClient
{
    private const AUTH_BASE = 'https://id.twitch.tv/oauth2';
    private const API_BASE  = 'https://api.twitch.tv/helix';

    public function getProviderSlug(): string { return 'twitch'; }

    public function getAuthorizationUrl(string $state): string
    {
        return self::AUTH_BASE . '/authorize?' . http_build_query([
            'client_id'     => $this->clientId,
            'redirect_uri'  => $this->redirectUri,
            'response_type' => 'code',
            'scope'         => implode(' ', $this->scopes ?: ['user:read:email', 'user:read:follows']),
            'state'         => $state,
        ]);
    }

    protected function getTokenEndpoint(): string { return self::AUTH_BASE . '/token'; }
    protected function getUserEndpoint(): string  { return self::API_BASE  . '/users'; }
    protected function getGrantType(): string     { return 'authorization_code'; }
    protected function extractUserId(array $user): string { return $user['id']; }

    // Twitch user endpoint geeft data in data[0]
    protected function fetchUser(string $accessToken): array
    {
        $response = $this->get(self::API_BASE . '/users', [
            'Authorization: Bearer ' . $accessToken,
            'Client-Id: ' . $this->clientId,
        ]);
        return $response['data'][0] ?? [];
    }

    // ─── TWITCH-SPECIFIEKE API CALLS ──────────────────────────────────────

    /**
     * Controleer of een kanaal live is.
     */
    public function isLive(string $appToken, string $channelName): ?array
    {
        try {
            $data = $this->get(
                self::API_BASE . '/streams?user_login=' . urlencode($channelName),
                ['Authorization: Bearer ' . $appToken, 'Client-Id: ' . $this->clientId]
            );
            return $data['data'][0] ?? null; // null = offline
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Haal kanaal info op (followers, views, bio).
     */
    public function getChannel(string $appToken, string $channelName): ?array
    {
        try {
            $users = $this->get(
                self::API_BASE . '/users?login=' . urlencode($channelName),
                ['Authorization: Bearer ' . $appToken, 'Client-Id: ' . $this->clientId]
            );
            $user = $users['data'][0] ?? null;
            if (!$user) return null;

            // Followers count via separate endpoint
            $followers = $this->get(
                self::API_BASE . '/channels/followers?broadcaster_id=' . $user['id'],
                ['Authorization: Bearer ' . $appToken, 'Client-Id: ' . $this->clientId]
            );
            $user['follower_count'] = $followers['total'] ?? 0;
            return $user;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Haal een App Access Token op (Client Credentials flow).
     * Wordt gebruikt voor publieke API calls (geen user nodig).
     */
    public function getAppToken(): string
    {
        $data = $this->post(self::AUTH_BASE . '/token', [
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type'    => 'client_credentials',
        ]);
        return $data['access_token'] ?? '';
    }
}

// ╔══════════════════════════════════════════════════════════════════════╗
// ║  File: TwitchOAuth.php | Role: Core | Version: 1.0.0                ║
// ║  Created: 2026-06-06 | Status: New                                  ║
// ╚══════════════════════════════════════════════════════════════════════╝
