<?php

declare(strict_types=1);

namespace Duyler\ORM;

use Cycle\Database\Config\DriverConfig;
use Cycle\Database\DatabaseManager;
use Cycle\Database\DatabaseProviderInterface;
use Cycle\Database\LoggerFactoryInterface;
use Duyler\Builder\Loader\LoaderServiceInterface;
use Duyler\Builder\Loader\PackageLoaderInterface;
use Cycle\Database;
use Cycle\Database\Config;
use Duyler\Console\CommandCollector;
use Duyler\DI\ContainerInterface;
use Duyler\EventBus\Build\Action;
use Duyler\EventBus\Build\SharedService;
use Duyler\EventBus\Contract\State\StateHandlerInterface;
use Duyler\ORM\Build\Entity;
use Duyler\ORM\Fixture\FixtureLoadCommandHandler;
use Duyler\ORM\Migration\MigrationDownCommandHandler;
use Duyler\ORM\Migration\MigrationUpCommandHandler;
use Duyler\ORM\State\InitORMStateHandler;
use Override;

class Loader implements PackageLoaderInterface
{
    public function __construct(
        private ContainerInterface $container,
        private DBALConfig $dbalConfig,
        private SchemaCollector $schemaCollector,
        private CommandCollector $commandCollector,
    ) {}

    #[Override]
    public function load(LoaderServiceInterface $loaderService): void
    {
        /** @var DriverConfig $connection */
        foreach ($this->dbalConfig->connections as $connection) {
            $connection->reconnect = true;
        }

        /** @var LoggerFactoryInterface $logger */
        $logger = null !== $this->dbalConfig->logger ? $this->container->get($this->dbalConfig->logger) : null;

        $dbal = new Database\DatabaseManager(
            config: new Config\DatabaseConfig([
                'default' => $this->dbalConfig->default,
                'aliases' => $this->dbalConfig->aliases,
                'databases' => $this->dbalConfig->databases,
                'connections' => $this->dbalConfig->connections,
            ]),
            loggerFactory: $logger,
        );

        $this->container->set($dbal);
        $this->container->bind([
            DatabaseProviderInterface::class => DatabaseManager::class,
        ]);

        $loaderService->addSharedService(
            new SharedService(
                class: DatabaseManager::class,
                service: $dbal,
            ),
        );

        $loaderService->addSharedService(
            new SharedService(
                class: SchemaCollector::class,
                service: $this->schemaCollector,
            ),
        );

        /** @var StateHandlerInterface $initORMStateHandler */
        $initORMStateHandler = $this->container->get(InitORMStateHandler::class);

        $loaderService->addStateHandler($initORMStateHandler);

        new Entity($this->schemaCollector);

        $loaderService->addAction(
            new Action(
                id: 'ORM.MigrationsUp',
                handler: MigrationUpCommandHandler::class,
            ),
        );

        $loaderService->addAction(
            new Action(
                id: 'ORM.MigrationsDown',
                handler: MigrationDownCommandHandler::class,
            ),
        );

        $loaderService->addAction(
            new Action(
                id: 'ORM.FixturesLoad',
                handler: FixtureLoadCommandHandler::class,
            ),
        );

        $this->commandCollector->add('orm:migrations:up', 'ORM.MigrationsUp');
        $this->commandCollector->add('orm:migrations:down', 'ORM.MigrationsDown');
        $this->commandCollector->add('orm:fixtures:load', 'ORM.FixturesLoad');
    }
}
