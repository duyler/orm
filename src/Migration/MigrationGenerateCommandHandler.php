<?php

declare(strict_types=1);

namespace Duyler\ORM\Migration;

use Cycle\Database\DatabaseManager;
use Cycle\ORM\Schema;
use Cycle\Schema\Generator\Migrations\NameBasedOnChangesGenerator;
use Cycle\Schema\Generator\Migrations\Strategy\MultipleFilesStrategy;
use Cycle\Schema\Registry;
use Cycle\Schema\Definition\Entity;
use Cycle\Schema\Generator\Migrations\GenerateMigrations;
use Cycle\Migrations\Config;
use Cycle\Migrations\Migrator;
use Cycle\Migrations\FileRepository;

/**
 * @psalm-suppress MixedArgument
 */
class MigrationGenerateCommandHandler
{
    public function __construct(
        private MigrationConfig $config,
        private DatabaseManager $dbal,
        private Schema $schema,
    ) {}

    public function __invoke()
    {
        $config = new Config\MigrationConfig([
            'directory' => $this->config->directory,
            'table'     => $this->config->table,
            'safe'      => $this->config->safe,
        ]);

        $migrator = new Migrator($config, $this->dbal, new FileRepository($config));

        if (false === $migrator->isConfigured()) {
            $migrator->configure();
        }

        $registry = new Registry($this->dbal);

        /** @var array $entitySchema */
        foreach ($this->schema->toArray() as $name => $entitySchema) {
            $entity = new Entity();
            $entity->setSchema($entitySchema);
            $entity->setRepository($entitySchema[Schema::REPOSITORY] ?? null);
            $entity->setClass($entitySchema[Schema::ENTITY]);
            $entity->setDatabase($entitySchema[Schema::DATABASE] ?? null);
            $entity->setTableName($entitySchema[Schema::TABLE]);
            $entity->setTypecast($entitySchema[Schema::TYPECAST] ?? null);
            $entity->setMapper($entitySchema[Schema::MAPPER] ?? null);

            $registry->register($entity);
        }

        $generator = new GenerateMigrations(
            $migrator->getRepository(),
            $migrator->getConfig(),
            new MultipleFilesStrategy($migrator->getConfig(), new NameBasedOnChangesGenerator()),
        );

        $generator->run($registry);
    }
}
