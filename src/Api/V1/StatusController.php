<?php
// ============================================================================
// Copyright (C) 2026  DieOuwe — GPL-3.0-or-later
// ============================================================================
declare(strict_types=1);
namespace CommunityFusion\Api\V1;

use CommunityFusion\Core\Request;
use CommunityFusion\Core\Response;
use CommunityFusion\Core\Database\Connection;

final class StatusController
{
    public function __construct(private readonly Connection $db) {}

    public function index(Request $request): Response
    {
        $dbOk = false;
        try { $this->db->fetchOne("SELECT 1"); $dbOk = true; } catch (\Throwable) {}

        return Response::json([
            'status'  => 'ok',
            'version' => CF_VERSION ?? '1.0.0',
            'php'     => PHP_VERSION,
            'db'      => $dbOk ? 'connected' : 'error',
            'time'    => date('c'),
        ]);
    }
}
