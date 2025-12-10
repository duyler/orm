<?php

declare(strict_types=1);

namespace Duyler\ORM\Migration;

use Cycle\Database\DatabaseManager;
use Cycle\Migrations;
use RuntimeException;

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

        $answer = readline('Are you sure you want to make changes to the database schema? (y/n): ');

        if ('y' !== strtolower((string) $answer)) {
            throw new RuntimeException('Aborted!');
        }

        $migrations = $migrator->getMigrations();

        foreach ($migrations as $migration) {
            $migrator->run();
        }
    }
}
