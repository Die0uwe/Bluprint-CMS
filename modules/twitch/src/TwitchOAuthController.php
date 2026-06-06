<?php
// ============================================================================
// Copyright (C) 2026  DieOuwe — GPL-3.0-or-later
// ============================================================================

declare(strict_types=1);

namespace CommunityFusion\Modules\Twitch;

use CommunityFusion\Core\Request;
use CommunityFusion\Core\Response;
use CommunityFusion\Core\Auth\AuthManager;
use CommunityFusion\Core\Database\Connection;

final class TwitchOAuthController
{
    public function __construct(
        private readonly AuthManager $auth,
        private readonly Connection  $db,
    ) {}

    public function redirect(Request $request): Response
    {
        if (!$this->auth->check()) {
            return Response::redirect('/login?redirect=/auth/twitch');
        }
        $oauth = $this->makeOAuthClient();
        return Response::redirect($oauth->buildRedirectUrl());
    }

    public function callback(Request $request): Response
    {
        if (!$this->auth->check()) return Response::redirect('/login');

        $code  = $request->query('code', '');
        $state = $request->query('state', '');

        if (empty($code)) return Response::redirect('/?error=twitch_cancelled');

        try {
            $oauth  = $this->makeOAuthClient();
            $result = $oauth->handleCallback($code, $state);

            $oauth->saveConnection(
                (int) $this->auth->id(),
                $result['user'],
                $result['tokens']
            );

            return Response::redirect('/profiel?twitch=connected');
        } catch (\RuntimeException $e) {
            error_log("Twitch OAuth fout: " . $e->getMessage());
            return Response::redirect('/?error=twitch_failed');
        }
    }

    private function makeOAuthClient(): TwitchOAuth
    {
        return new TwitchOAuth(
            db:           $this->db,
            clientId:     $this->getSetting('client_id'),
            clientSecret: $this->getSetting('client_secret'),
            redirectUri:  $this->getSetting('redirect_uri',
                ($_ENV['APP_URL'] ?? '') . '/auth/twitch/callback'),
            scopes:       ['user:read:email', 'user:read:follows'],
        );
    }

    private function getSetting(string $key, string $default = ''): string
    {
        $row = $this->db->fetchOne(
            "SELECT value FROM cf_settings WHERE `group` = 'twitch' AND `key` = ?",
            [$key]
        );
        return $row['value'] ?? $default;
    }
}
