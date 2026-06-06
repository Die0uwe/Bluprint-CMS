<?php
// ============================================================================
// Copyright (C) 2026  DieOuwe — GPL-3.0-or-later
// ============================================================================
declare(strict_types=1);
namespace CommunityFusion\Modules\Warcraft;

use CommunityFusion\Core\Request;
use CommunityFusion\Core\Response;
use CommunityFusion\Core\Database\Connection;
use CommunityFusion\Core\Security\CsrfProtection;

final class WarcraftAdminController
{
    public function __construct(private readonly Connection $db) {}

    public function index(Request $request): Response
    {
        if ($request->getMethod() === 'POST') {
            CsrfProtection::validateRequest();
            $fields = ['client_id','client_secret','region','realm','guild_name','locale'];
            foreach ($fields as $key) {
                $val = $request->input($key, '');
                $this->db->execute(
                    "INSERT INTO cf_settings (`group`,`key`,`value`,`type`) VALUES ('warcraft',?,?,'string')
                     ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)",
                    [$key, $val]
                );
            }
            return Response::redirect('/admin/wow?saved=1');
        }

        $settings = [];
        $rows = $this->db->fetchAll("SELECT `key`,`value` FROM cf_settings WHERE `group`='warcraft'");
        foreach ($rows as $r) $settings[$r['key']] = $r['value'];
        ob_start();
        include __DIR__ . '/../templates/admin.php';
        return Response::html(ob_get_clean());
    }
}
