<?php

declare(strict_types=1);

namespace Duyler\ORM\Provider;

use Cycle\ORM\Schema;
use Duyler\DI\ContainerService;
use Duyler\DI\Provider\AbstractProvider;
use Duyler\ORM\ORMSchema;
use Override;

class SchemaProvider extends AbstractProvider
{
    public function __construct(private ORMSchema $schema) {}

    #[Override]
    public function factory(ContainerService $containerService): ?object
    {
        return new Schema($this->schema->toArray());
    }
}
