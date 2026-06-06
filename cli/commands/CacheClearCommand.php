<?php
// ============================================================================
// Copyright (C) 2026  DieOuwe — GPL-3.0-or-later
// ============================================================================
declare(strict_types=1);
namespace CommunityFusion\Cli\Commands;

final class CacheClearCommand
{
    public function handle(array $argv): void
    {
        $path = CF_ROOT . '/storage/cache';
        $count = 0;

        foreach (glob($path . '/*.cache') ?: [] as $file) {
            unlink($file);
            $count++;
        }

        // Twig cache
        foreach (glob($path . '/twig/*.php') ?: [] as $file) {
            unlink($file);
            $count++;
        }

        echo "✅ Cache gewist — {$count} bestanden verwijderd\n";
    }
}
