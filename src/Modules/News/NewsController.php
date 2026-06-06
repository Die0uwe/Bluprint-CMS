<?php
// ============================================================================
// Copyright (C) 2026  DieOuwe — GPL-3.0-or-later
// ============================================================================

declare(strict_types=1);

namespace CommunityFusion\Modules\News;

use CommunityFusion\Core\Request;
use CommunityFusion\Core\Response;
use CommunityFusion\Core\Template\ThemeManager;

final class NewsController
{
    private const PER_PAGE = 12;

    public function __construct(
        private readonly NewsRepository $repo,
        private readonly ThemeManager   $theme,
    ) {}

    public function index(Request $request): Response
    {
        $page    = max(1, (int) $request->query('page', 1));
        $offset  = ($page - 1) * self::PER_PAGE;
        $items   = $this->repo->getPublished(self::PER_PAGE, $offset);
        $total   = $this->repo->countPublished();
        $pages   = (int) ceil($total / self::PER_PAGE);

        $html = $this->theme->render('news/index.twig', [
            'page_title' => 'Nieuws',
            'news'       => $items,
            'pagination' => ['current' => $page, 'total' => $pages],
        ]);

        return Response::html($html);
    }

    public function show(Request $request): Response
    {
        $slug = $request->param('slug');
        $item = $this->repo->findBySlug($slug);

        if ($item === null) {
            return Response::html('<h1>404 — Artikel niet gevonden</h1>', 404);
        }

        $this->repo->incrementViews((int) $item['id']);

        $html = $this->theme->render('news/show.twig', [
            'page_title' => $item['title'],
            'article'    => $item,
        ]);

        return Response::html($html);
    }
}

// ╔══════════════════════════════════════════════════════════════════════╗
// ║  File: NewsController.php | Role: Core | Version: 1.0.0             ║
// ║  Created: 2026-06-06 | Status: New                                  ║
// ╚══════════════════════════════════════════════════════════════════════╝
