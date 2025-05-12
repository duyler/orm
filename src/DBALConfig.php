<?php

declare(strict_types=1);

namespace Duyler\ORM;

readonly class DBALConfig
{
    public function __construct(
        public string $default = 'default',
        public ?string $logger = null,
        public array $aliases = [],
        public array $databases = [],
        public array $connections = [],
    ) {}
}
