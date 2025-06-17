<?php

declare(strict_types=1);

namespace Duyler\ORM;

use Cycle\Database\Config\DriverConfig;

readonly class DBALConfig
{
    public function __construct(
        public string $default = 'default',
        /** @var class-string|null $logger */
        public ?string $logger = null,
        /** @var array<string, string> $aliases> */
        public array $aliases = [],
        /** @var array<string, array<string, string>> $databases */
        public array $databases = [],
        /** @var array<string, DriverConfig> $connections */
        public array $connections = [],
        public bool $autocommit = true,
    ) {}
}
