<?php

declare(strict_types=1);

namespace Duyler\ORM\State;

use Cycle\Database\DatabaseManager;
use Cycle\ORM\Collection\IlluminateCollectionFactory;
use Cycle\ORM\EntityManager;
use Cycle\ORM\EntityManagerInterface;
use Cycle\ORM\Factory;
use Cycle\ORM\FactoryInterface;
use Cycle\ORM\ORM;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\Schema;
use Duyler\DI\ContainerInterface;
use Duyler\EventBus\Build\SharedService;
use Duyler\EventBus\Contract\State\MainBeginStateHandlerInterface;
use Duyler\EventBus\State\Service\StateMainBeginService;
use Duyler\EventBus\State\StateContext;
use Duyler\ORM\Provider\SchemaProvider;
use Duyler\ORM\SchemaCollector;
use Override;

class InitORMStateHandler implements MainBeginStateHandlerInterface
{
    public function __construct(
        private SchemaCollector $schemaCollector,
        private DatabaseManager $databaseManager,
        private ContainerInterface $container,
    ) {}

    #[Override]
    public function handle(StateMainBeginService $stateService, StateContext $context): void
    {
        $schema = new Schema($this->schemaCollector->getSchema()->toArray());

        $factory = new Factory(
            dbal: $this->databaseManager,
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

        $stateService->addSharedService(
            new SharedService(
                class: ORM::class,
                service: $orm,
                bind: [
                    ORMInterface::class => ORM::class,
                ],
            ),
        );

        $stateService->addSharedService(
            new SharedService(
                class: EntityManager::class,
                service: $em,
                bind: [
                    EntityManagerInterface::class => EntityManager::class,
                ],
            ),
        );

        $stateService->addSharedService(
            new SharedService(
                class: Factory::class,
                service: $factory,
                bind: [
                    FactoryInterface::class => Factory::class,
                ],
            ),
        );

        $stateService->addSharedService(
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
