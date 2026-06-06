<?php
// ============================================================================
// Copyright (C) 2026  DieOuwe — GPL-3.0-or-later
// ============================================================================
declare(strict_types=1);
namespace CommunityFusion\Modules\Warcraft;

use CommunityFusion\Core\Request;
use CommunityFusion\Core\Response;
use CommunityFusion\Core\Database\Connection;
use CommunityFusion\Core\Cache\CacheManager;

final class WarcraftController
{
    private BlizzardApiClient $api;

    public function __construct(
        private readonly Connection   $db,
        private readonly CacheManager $cache,
    ) {
        $config   = $this->loadConfig();
        $this->api = new BlizzardApiClient(
            $cache,
            $config['client_id']     ?? '',
            $config['client_secret'] ?? '',
            $config['region']        ?? 'eu',
            $config['locale']        ?? 'en_GB',
        );
    }

    public function index(Request $request): Response
    {
        $config = $this->loadConfig();
        $realm  = $config['realm']      ?? '';
        $guild  = $config['guild_name'] ?? '';

        $guildInfo = ($realm && $guild) ? $this->api->getGuildInfo($realm, $guild) : null;
        $progress  = ($realm && $guild) ? $this->api->getRaidProgress($realm, $guild) : null;
        $roster    = ($realm && $guild) ? $this->api->getGuildRoster($realm, $guild) : null;

        ob_start();
        include __DIR__ . '/../templates/index.php';
        return Response::html(ob_get_clean());
    }

    public function guild(Request $request): Response
    {
        $config  = $this->loadConfig();
        $realm   = $config['realm'] ?? '';
        $guild   = $config['guild_name'] ?? '';
        $roster  = ($realm && $guild) ? $this->api->getGuildRoster($realm, $guild) : null;
        $members = $roster['members'] ?? [];

        ob_start();
        include __DIR__ . '/../templates/guild.php';
        return Response::html(ob_get_clean());
    }

    public function character(Request $request): Response
    {
        $config  = $this->loadConfig();
        $realm   = $config['realm'] ?? '';
        $name    = $request->param('name', '');
        $char    = $this->api->getCharacter($realm, $name);
        $equip   = $char ? $this->api->getCharacterEquipment($realm, $name) : null;
        $rio     = $char ? $this->api->getMythicPlusScore($realm, $name) : null;

        ob_start();
        include __DIR__ . '/../templates/character.php';
        return Response::html(ob_get_clean());
    }

    private function loadConfig(): array
    {
        try {
            $rows = $this->db->fetchAll("SELECT `key`,`value` FROM cf_settings WHERE `group`='warcraft'");
            $cfg  = [];
            foreach ($rows as $r) $cfg[$r['key']] = $r['value'];
            return $cfg;
        } catch (\Throwable) { return []; }
    }
}
