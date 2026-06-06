<?php
// ============================================================================
// Copyright (C) 2026  DieOuwe — GPL-3.0-or-later
// ============================================================================

declare(strict_types=1);

namespace CommunityFusion\Modules\Pages;

use CommunityFusion\Core\Request;
use CommunityFusion\Core\Response;
use CommunityFusion\Core\Template\ThemeManager;
use CommunityFusion\Modules\News\NewsRepository;

final class PageController
{
    public function __construct(
        private readonly PageRepository  $pages,
        private readonly ThemeManager    $theme,
        private readonly NewsRepository  $news,
    ) {}

    /** Homepage */
    public function home(Request $request): Response
    {
        $latestNews = $this->news->getPublished(6);
        $menuPages  = $this->pages->getMenuPages();

        $html = $this->theme->render('home.twig', [
            'page_title'  => 'Home',
            'latest_news' => $latestNews,
            'menu_pages'  => $menuPages,
        ]);

        return Response::html($html);
    }

    /** Statische pagina */
    public function show(Request $request): Response
    {
        $slug = $request->param('slug');
        $page = $this->pages->findBySlug($slug);

        if ($page === null) {
            return Response::html('<h1>404 — Pagina niet gevonden</h1>', 404);
        }

        $template = $page['template'] ?? 'default';
        $html     = $this->theme->render("pages/{$template}.twig", [
            'page_title' => $page['title'],
            'page'       => $page,
        ]);

        return Response::html($html);
    }
}

// ╔══════════════════════════════════════════════════════════════════════╗
// ║  File: PageController.php | Role: Core | Version: 1.0.0             ║
// ║  Created: 2026-06-06 | Status: New                                  ║
// ╚══════════════════════════════════════════════════════════════════════╝
