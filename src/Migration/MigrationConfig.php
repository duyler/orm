<?php

declare(strict_types=1);

namespace Duyler\ORM\Migration;

readonly class MigrationConfig
{
    public function __construct(
        public string $directory,
        public string $table,
        public bool $safe,
    ) {}
}
