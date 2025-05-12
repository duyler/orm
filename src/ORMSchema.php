<?php

declare(strict_types=1);

namespace Duyler\ORM;

readonly class ORMSchema
{
    public function __construct(
        private array $schema = [],
    ) {}

    public function toArray(): array
    {
        return $this->schema;
    }
}
