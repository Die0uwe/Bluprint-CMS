<?php
// ============================================================================
// Copyright (C) 2026  DieOuwe — GPL-3.0-or-later
// ============================================================================
declare(strict_types=1);
namespace CommunityFusion\Core\Marketplace;

final class InstallResult
{
    public function __construct(
        public readonly bool   $success,
        public readonly string $slug,
        public readonly string $name,
        public readonly string $version,
        public readonly string $type,
        public readonly string $installPath,
        public readonly string $message = '',
    ) {}

    public function toArray(): array
    {
        return [
            'success'      => $this->success,
            'slug'         => $this->slug,
            'name'         => $this->name,
            'version'      => $this->version,
            'type'         => $this->type,
            'install_path' => $this->installPath,
            'message'      => $this->message,
        ];
    }
}
