<?php
// ============================================================================
// Copyright (C) 2026  DieOuwe — GPL-3.0-or-later
// ============================================================================
declare(strict_types=1);
namespace CommunityFusion\Modules\Guild;

use CommunityFusion\Core\Request;
use CommunityFusion\Core\Response;
use CommunityFusion\Core\Database\Connection;
use CommunityFusion\Core\Auth\AuthManager;
use CommunityFusion\Core\Security\CsrfProtection;

final class GuildController
{
    public function __construct(
        private readonly Connection  $db,
        private readonly AuthManager $auth,
    ) {}

    public function index(Request $request): Response
    {
        $teams   = $this->db->fetchAll("SELECT * FROM cf_guild_teams ORDER BY type");
        $members = $this->db->fetchAll(
            "SELECT gm.*, gr.display_name as rank_name, gr.color as rank_color
             FROM cf_guild_members gm
             LEFT JOIN cf_guild_ranks gr ON gr.id = gm.rank_id
             WHERE gm.is_active = 1 ORDER BY gr.priority DESC LIMIT 20"
        );
        ob_start();
        include __DIR__ . '/../templates/index.php';
        return Response::html(ob_get_clean());
    }

    public function members(Request $request): Response
    {
        $rankFilter = $request->query('rank');
        $sql = "SELECT gm.*, gr.display_name as rank_name, gr.color as rank_color
                FROM cf_guild_members gm
                LEFT JOIN cf_guild_ranks gr ON gr.id = gm.rank_id
                WHERE gm.is_active = 1";
        $bindings = [];
        if ($rankFilter) {
            $sql .= " AND gm.rank_id = ?";
            $bindings[] = (int)$rankFilter;
        }
        $sql .= " ORDER BY gr.priority DESC, gm.character_name ASC";
        $members = $this->db->fetchAll($sql, $bindings);
        $ranks   = $this->db->fetchAll("SELECT * FROM cf_guild_ranks ORDER BY priority DESC");
        ob_start();
        include __DIR__ . '/../templates/members.php';
        return Response::html(ob_get_clean());
    }

    public function roster(Request $request): Response
    {
        return Response::redirect('/guild/members');
    }

    public function applyForm(Request $request): Response
    {
        $teams = $this->db->fetchAll("SELECT * FROM cf_guild_teams WHERE is_recruiting = 1 ORDER BY name");
        ob_start();
        include __DIR__ . '/../templates/apply.php';
        return Response::html(ob_get_clean());
    }

    public function apply(Request $request): Response
    {
        CsrfProtection::validateRequest();

        $data = $request->all();
        $errors = [];

        if (empty(trim($data['character_name'] ?? ''))) $errors[] = 'Karakternaam is verplicht.';
        if (empty($data['class'] ?? ''))                 $errors[] = 'Klasse is verplicht.';
        if (empty($data['spec'] ?? ''))                  $errors[] = 'Specialisatie is verplicht.';
        if (strlen($data['about'] ?? '') < 20)           $errors[] = 'Vertel minimaal 20 tekens over jezelf.';

        if (!empty($errors)) {
            $teams = $this->db->fetchAll("SELECT * FROM cf_guild_teams WHERE is_recruiting = 1");
            ob_start();
            include __DIR__ . '/../templates/apply.php';
            return Response::html(ob_get_clean(), 422);
        }

        $this->db->insert('guild_applications', [
            'user_id'        => $this->auth->id(),
            'character_name' => trim($data['character_name']),
            'class'          => $data['class'],
            'spec'           => $data['spec'],
            'item_level'     => !empty($data['item_level']) ? (int)$data['item_level'] : null,
            'about'          => trim($data['about']),
            'experience'     => trim($data['experience'] ?? ''),
            'team_id'        => !empty($data['team_id']) ? (int)$data['team_id'] : null,
        ]);

        return Response::redirect('/guild/apply?success=1');
    }
}
