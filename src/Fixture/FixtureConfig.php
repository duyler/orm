<?php

declare(strict_types=1);

namespace Duyler\ORM\Fixture;

readonly class FixtureConfig
{
    public function __construct(
        /** @var array<array-key, class-string<FixtureInterface>> $fixtures */
        public array $fixtures = [],
    ) {}
}
