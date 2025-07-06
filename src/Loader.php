<?php

declare(strict_types=1);

namespace Duyler\ORM;

use Cycle\Database;
use Cycle\Database\Config;
use Cycle\Database\DatabaseManager;
use Cycle\Database\DatabaseProviderInterface;
use Cycle\Database\LoggerFactoryInterface;
use Cycle\ORM\Collection\IlluminateCollectionFactory;
use Cycle\ORM\EntityManager;
use Cycle\ORM\EntityManagerInterface;
use Cycle\ORM\Factory;
use Cycle\ORM\FactoryInterface;
use Cycle\ORM\ORM;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\Schema;
use Duyler\Builder\Loader\LoaderServiceInterface;
use Duyler\Builder\Loader\PackageLoaderInterface;
use Duyler\Console\CommandCollector;
use Duyler\DI\ContainerInterface;
use Duyler\EventBus\Build\Action;
use Duyler\EventBus\Build\SharedService;
use Duyler\ORM\Build\Entity;
use Duyler\ORM\Fixture\FixtureLoadCommandHandler;
use Duyler\ORM\Migration\MigrationDownCommandHandler;
use Duyler\ORM\Migration\MigrationGenerateCommandHandler;
use Duyler\ORM\Migration\MigrationUpCommandHandler;
use Duyler\ORM\Provider\SchemaProvider;
use Duyler\ORM\State\FlushUOWStateHandler;
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
    public function beforeLoadBuild(LoaderServiceInterface $loaderService): void
    {
        foreach ($this->dbalConfig->connections as $connection) {
            $connection->reconnect = true;
        }

        $loaderService->addSharedService(
            new SharedService(
                class: SchemaCollector::class,
                service: $this->schemaCollector,
            ),
        );

        /** @var FlushUOWStateHandler $commitUOWStateHandler */
        $commitUOWStateHandler = $this->container->get(FlushUOWStateHandler::class);

        $loaderService->addStateHandler($commitUOWStateHandler);

        new Entity($this->schemaCollector);

        $loaderService->addAction(
            new Action(
                id: 'ORM.MigrationsGenerate',
                handler: MigrationGenerateCommandHandler::class,
            ),
        );

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

        $this->commandCollector->add('orm:migrations:generate', 'ORM.MigrationsGenerate');
        $this->commandCollector->add('orm:migrations:up', 'ORM.MigrationsUp');
        $this->commandCollector->add('orm:migrations:down', 'ORM.MigrationsDown');
        $this->commandCollector->add('orm:fixtures:load', 'ORM.FixturesLoad');
    }

    #[Override]
    public function afterLoadBuild(LoaderServiceInterface $loaderService): void
    {
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

        $schema = new Schema($this->schemaCollector->getSchema()->toArray());

        $factory = new Factory(
            dbal: $dbal,
            defaultCollectionFactory: new IlluminateCollectionFactory(),
        );

        $orm = new ORM($factory, $schema);

        $em = new EntityManager($orm);

        $this->container->set($orm);
        $this->container->set($em);
        $this->container->set($factory);
        $this->container->bind([
            FactoryInterface::class => Factory::class,
            ORMInterface::class => ORM::class,
            EntityManagerInterface::class => EntityManager::class,
        ]);

        $loaderService->addSharedService(
            new SharedService(
                class: ORM::class,
                service: $orm,
                bind: [
                    ORMInterface::class => ORM::class,
                ],
            ),
        );

        $loaderService->addSharedService(
            new SharedService(
                class: EntityManager::class,
                service: $em,
                bind: [
                    EntityManagerInterface::class => EntityManager::class,
                ],
            ),
        );

        $loaderService->addSharedService(
            new SharedService(
                class: Factory::class,
                service: $factory,
                bind: [
                    FactoryInterface::class => Factory::class,
                ],
            ),
        );

        $loaderService->addSharedService(
            new SharedService(
                class: Schema::class,
                service: $schema,
                bind: [
                    Schema::class => SchemaProvider::class,
                ],
            ),
        );

        $em->clean(true);
    }
}
