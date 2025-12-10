<?php

declare(strict_types=1);

namespace Duyler\ORM\Fixture;

use Cycle\ORM\ORMInterface;

interface FixtureInterface
{
    public function load(ORMInterface $orm): void;
}
