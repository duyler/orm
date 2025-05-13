<?php

declare(strict_types=1);

namespace Duyler\ORM\Migration;

use Cycle\Database\DatabaseManager;
use Cycle\Migrations;

final class MigrationUpCommandHandler
{
    public function __construct(
        private MigrationConfig $config,
        private DatabaseManager $dbal,
    ) {}

    public function __invoke(): void
    {
        $config = new Migrations\Config\MigrationConfig([
            'directory' => $this->config->directory,
            'table'     => $this->config->table,
            'safe'      => $this->config->safe,
        ]);

        $migrator = new Migrations\Migrator($config, $this->dbal, new Migrations\FileRepository($config));

        if (false === $migrator->isConfigured()) {
            $migrator->configure();
        }

        $migrator->run();
    }
}
