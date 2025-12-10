[![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=duyler_orm&metric=alert_status)](https://sonarcloud.io/summary/new_code?id=duyler_orm)
[![Coverage](https://sonarcloud.io/api/project_badges/measure?project=duyler_orm&metric=coverage)](https://sonarcloud.io/summary/new_code?id=duyler_orm)
[![type-coverage](https://shepherd.dev/github/duyler/orm/coverage.svg)](https://shepherd.dev/github/duyler/event-bus)
[![psalm-level](https://shepherd.dev/github/duyler/orm/level.svg)](https://shepherd.dev/github/duyler/event-bus)

# Duyler Cycle ORM Integration

A declarative ORM integration package for [Duyler Framework](https://github.com/duyler/duyler) based on [CycleORM](https://cycle-orm.dev/). This package provides a clean, attribute-free approach to entity definition using a fluent Builder API.

## Features

- **Declarative Entity Builder** - Define entities without attributes in entity classes
- **Entity Behaviors** - Full support for CycleORM behaviors (timestamps, soft delete, optimistic lock, UUID generation, hooks)
- **Clean Domain Layer** - Keep your entities as pure POPOs (Plain Old PHP Objects)
- **Console Commands** - Built-in commands for migrations and fixtures
- **Type Safety** - Strict typing with 99%+ type coverage
- **Flexible Configuration** - Centralized entity configuration
- **Custom Repositories** - Easy integration of custom repository classes

## Installation

```bash
composer require duyler/orm
```

## Quick Start

### 1. Define Your Entity Class

Create a clean POPO without any ORM attributes:

```php
namespace App\Domain\Entity;

use DateTimeInterface;
use Ramsey\Uuid\UuidInterface;

class Product
{
    private UuidInterface $id;
    private string $title;
    private int $price;
    private DateTimeInterface $createdAt;
    private DateTimeInterface $updatedAt;
    
    // Getters and setters...
    
    public function getId(): UuidInterface
    {
        return $this->id;
    }
    
    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }
    
    // ... other methods
}
```

### 2. Declare Entity Using Builder

Configure your entity declaratively in `build/entities.php`:

```php
use Duyler\ORM\Build\Entity;

Entity::declare(Product::class)
    ->database('default')
    ->table('products')
    ->primaryKey('id')
    
    // Entity Behaviors
    ->uuid7('id')           // Auto-generate UUID v7
    ->createdAt()           // Auto-set creation timestamp
    ->updatedAt()           // Auto-update modification timestamp
    
    // Column mapping
    ->columns([
        'id' => 'id',
        'title' => 'title',
        'price' => 'price',
        'createdAt' => 'created_at',
        'updatedAt' => 'updated_at',
    ])
    
    // Type casting
    ->typecast([
        'id' => 'uuid',
        'title' => 'string',
        'price' => 'int',
        'createdAt' => 'datetime',
        'updatedAt' => 'datetime',
    ])
    
    ->typecastHandler([
        UuidTypecast::class,
    ]);
```

### 3. Use in Your Application

```php
use Cycle\ORM\ORMInterface;

// In your action handler
$product = new Product();
$product->setTitle('iPhone 15 Pro');
$product->setPrice(99900);

$orm->persist($product);
$orm->run();

// $product->id, $product->createdAt, $product->updatedAt 
// are set automatically by behaviors!
```

## Entity Builder API

### Basic Configuration

#### declare()

Start entity declaration:

```php
Entity::declare(Product::class)
```

#### database()

Specify database connection:

```php
->database('default')
```

#### table()

Define table name:

```php
->table('products')
```

#### primaryKey()

Set primary key column:

```php
->primaryKey('id')
```

#### columns()

Map entity properties to table columns:

```php
->columns([
    'propertyName' => 'column_name',
    'userId' => 'user_id',
])
```

#### typecast()

Define type casting for properties:

```php
->typecast([
    'id' => 'uuid',
    'price' => 'int',
    'isActive' => 'bool',
    'createdAt' => 'datetime',
])
```

#### typecastHandler()

Register custom typecast handlers:

```php
->typecastHandler([
    UuidTypecast::class,
    MoneyTypecast::class,
])
```

#### repository()

Specify custom repository class:

```php
->repository(ProductRepository::class)
```

### Relations

Define relationships between entities:

```php
->relations([
    'items' => [
        'type' => 'hasMany',
        'target' => OrderItem::class,
        'foreignKey' => 'order_id',
    ],
    'user' => [
        'type' => 'belongsTo',
        'target' => User::class,
    ],
])
```

## Entity Behaviors

Entity Behaviors provide automatic functionality for your entities without polluting domain classes with infrastructure code.

### Timestamps

#### createdAt()

Automatically set creation timestamp:

```php
Entity::declare(Product::class)
    ->createdAt()  // Uses 'createdAt' field by default
    // or with custom field name
    ->createdAt('created', 'created_at');
```

#### updatedAt()

Automatically update modification timestamp:

```php
Entity::declare(Product::class)
    ->updatedAt()  // Uses 'updatedAt' field by default
    // or with custom field and nullable option
    ->updatedAt('updated', 'updated_at', nullable: true);
```

### Soft Delete

Mark records as deleted instead of removing them from database:

```php
Entity::declare(Product::class)
    ->softDelete()  // Uses 'deletedAt' field by default
    // or with custom field name
    ->softDelete('removed', 'removed_at');
```

Usage:

```php
$orm->delete($product);  // Sets deletedAt instead of deleting
$orm->run();

// Soft-deleted entities are automatically excluded from queries
$products = $orm->getRepository(Product::class)->findAll();
```

### Optimistic Locking

Prevent race conditions during concurrent updates:

```php
Entity::declare(Order::class)
    ->optimisticLock(rule: 'increment')  // Auto-increment integer
    // Other strategies:
    // ->optimisticLock(rule: 'microtime')      // Microtime string
    // ->optimisticLock(rule: 'datetime')       // Datetime version
    // ->optimisticLock(rule: 'random-string')  // Random string
    // ->optimisticLock(rule: 'manual')         // Manual control
```

**Available lock strategies:**
- `increment` - Auto-incrementing integer (default for int fields)
- `microtime` - Microtime string (default for string fields)
- `datetime` - Datetime-based version (default for datetime fields)
- `random-string` - Random string generation
- `manual` - Manual version management

### UUID Generation

Automatically generate UUIDs for primary keys:

```php
Entity::declare(User::class)
    ->uuid7('id')  // UUID v7 - RECOMMENDED (time-sortable)
    // or
    ->uuid4('id')  // UUID v4 (random)
    // or
    ->uuid1('id')  // UUID v1 (time-based with MAC)
```

**Why UUID v7?**
- Time-sortable for better index performance
- No MAC address leakage
- Compatible with database indexes

### Custom Hooks

Execute custom logic on entity lifecycle events:

```php
use Cycle\ORM\Entity\Behavior\Event\Mapper\Command;

Entity::declare(Article::class)
    ->hook(
        callable: function (Command\OnCreate $event) {
            $entity = $event->entity;
            $entity->setSlug(Str::slug($entity->getTitle()));
        },
        events: Command\OnCreate::class,
    )
    // Multiple events
    ->hook(
        callable: fn(Command\OnUpdate $event) => Logger::log($event),
        events: [Command\OnUpdate::class, Command\OnDelete::class],
    );
```

**Available events:**
- `Command\OnCreate` - Before entity creation
- `Command\OnUpdate` - Before entity update
- `Command\OnDelete` - Before entity deletion

### Event Listeners

Add custom listeners with dependency injection support:

```php
Entity::declare(Product::class)
    ->eventListener(ProductAuditListener::class)
    // or with arguments
    ->eventListener(CustomListener::class, ['param' => 'value']);
```

Listener example:

```php
class ProductAuditListener
{
    public function __construct(
        private AuditService $auditService,
        private LoggerInterface $logger,
    ) {}
    
    public function __invoke(Command\OnUpdate $event): void
    {
        $this->auditService->logChanges(
            $event->entity,
            $event->state->getChanges()
        );
    }
}
```

### Generic Listeners

Add any CycleORM listener classes:

```php
use Cycle\ORM\Entity\Behavior\Listener;

Entity::declare(Product::class)
    ->listeners([
        Listener\CreatedAt::class,
        Listener\UpdatedAt::class,
        CustomListener::class,
    ]);
```

### Combining Behaviors

All behaviors can be combined in any order:

```php
Entity::declare(Order::class)
    ->database('default')
    ->table('orders')
    ->primaryKey('id')
    
    // UUID primary key
    ->uuid7('id')
    
    // Timestamps
    ->createdAt()
    ->updatedAt()
    
    // Soft delete
    ->softDelete()
    
    // Optimistic locking
    ->optimisticLock(rule: 'increment')
    
    // Custom hooks
    ->hook(
        fn(Command\OnCreate $e) => $e->entity->setStatus('new'),
        Command\OnCreate::class,
    )
    
    // Audit listener
    ->eventListener(OrderAuditListener::class)
    
    // Standard configuration
    ->columns([/* ... */])
    ->typecast([/* ... */])
    ->relations([/* ... */]);
```

## Working with ORM

### Retrieving Entities

```php
use Cycle\ORM\ORMInterface;

// Get repository
$repository = $orm->getRepository(Product::class);

// Find by primary key
$product = $repository->findByPK($id);

// Find one by criteria
$product = $repository->findOne(['slug' => 'iphone-15']);

// Find all
$products = $repository->findAll();

// Find with criteria
$products = $repository->findAll(['price' => ['>' => 1000]]);
```

### Creating Entities

```php
$product = new Product();
$product->setTitle('iPhone 15 Pro');
$product->setPrice(99900);

$orm->persist($product);
$orm->run();
```

### Updating Entities

```php
$product = $repository->findByPK($id);
$product->setPrice(89900);

$orm->persist($product);
$orm->run();
```

### Deleting Entities

```php
$product = $repository->findByPK($id);

$orm->delete($product);
$orm->run();
```

### Query Builder

```php
$products = $repository->select()
    ->where('price', '>', 1000)
    ->where('isActive', true)
    ->orderBy('createdAt', 'DESC')
    ->limit(10)
    ->fetchAll();
```

## Custom Repositories

### Define Repository Interface

```php
namespace App\Domain\Repository;

use App\Domain\Entity\Product;

interface ProductRepositoryInterface
{
    public function findBySlug(string $slug): ?Product;
    public function findActive(): array;
}
```

### Implement Repository

```php
namespace App\Infrastructure\Repository;

use Cycle\ORM\Select\Repository;
use App\Domain\Repository\ProductRepositoryInterface;

class ProductRepository extends Repository implements ProductRepositoryInterface
{
    public function findBySlug(string $slug): ?Product
    {
        return $this->findOne(['slug' => $slug]);
    }
    
    public function findActive(): array
    {
        return $this->select()
            ->where('isActive', true)
            ->fetchAll();
    }
}
```

### Register in Entity Declaration

```php
Entity::declare(Product::class)
    ->repository(ProductRepository::class)
    // ... other configuration
```

## Console Commands

### Migrations

#### Create Migration

```bash
./bin/do orm:migrations:generate
```

#### Apply Migrations

```bash
./bin/do orm:migrations:up
```

#### Rollback Last Migration

```bash
./bin/do orm:migrations:down
```

### Fixtures

#### Create Fixture

```php
namespace App\Fixtures;

use Cycle\ORM\ORMInterface;

class ProductFixture
{
    public function __construct(
        private ORMInterface $orm,
    ) {}
    
    public function load(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $product = new Product();
            $product->setTitle("Product $i");
            $product->setPrice(rand(1000, 10000));
            
            $this->orm->persist($product);
        }
        
        $this->orm->run();
    }
}
```

#### Load Fixtures

```bash
./bin/do orm:fixtures:load
```

## Database Configuration

Configure database connections in `config/db.php`:

```php
use Cycle\Database\Config\DatabaseConfig;
use Cycle\Database\Config\PostgresDriverConfig;
use Cycle\Database\Driver\Postgres\PostgresDriver;

return [
    DatabaseConfig::class => [
        'databases' => [
            'default' => [
                'connection' => 'postgres',
            ],
        ],
        'connections' => [
            'postgres' => new PostgresDriverConfig(
                connection: new PostgresDriver(
                    dsn: 'pgsql:host=localhost;dbname=myapp',
                    username: 'user',
                    password: 'pass',
                ),
            ),
        ],
    ],
];
```

## Real-World Example

Complete entity with all features:

```php
use Duyler\ORM\Build\Entity;
use Cycle\ORM\Entity\Behavior\Event\Mapper\Command;

Entity::declare(Order::class)
    ->database('default')
    ->table('orders')
    ->primaryKey('id')
    ->repository(OrderRepository::class)
    
    // Behaviors
    ->uuid7('id')
    ->createdAt('createdAt', 'created_at')
    ->updatedAt('updatedAt', 'updated_at')
    ->softDelete('deletedAt', 'deleted_at')
    ->optimisticLock('version', rule: 'increment')
    
    // Business logic hooks
    ->hook(
        callable: function (Command\OnCreate $event) {
            $order = $event->entity;
            $order->setOrderNumber(
                'ORD-' . date('Y') . '-' . str_pad($order->getId(), 6, '0', STR_PAD_LEFT)
            );
            $order->setStatus(OrderStatus::New);
        },
        events: Command\OnCreate::class,
    )
    
    // Validation on update
    ->hook(
        callable: function (Command\OnUpdate $event) {
            $order = $event->entity;
            if ($order->getTotal() < 0) {
                throw new InvalidOrderException('Order total cannot be negative');
            }
        },
        events: Command\OnUpdate::class,
    )
    
    // Audit logging
    ->eventListener(OrderAuditListener::class)
    
    // Column mapping
    ->columns([
        'id' => 'id',
        'orderNumber' => 'order_number',
        'userId' => 'user_id',
        'status' => 'status',
        'total' => 'total',
        'version' => 'version',
        'createdAt' => 'created_at',
        'updatedAt' => 'updated_at',
        'deletedAt' => 'deleted_at',
    ])
    
    // Type casting
    ->typecast([
        'id' => 'uuid',
        'orderNumber' => 'string',
        'userId' => 'uuid',
        'status' => 'string',
        'total' => 'int',
        'version' => 'int',
        'createdAt' => 'datetime',
        'updatedAt' => 'datetime',
        'deletedAt' => 'datetime',
    ])
    
    // Relations
    ->relations([
        'user' => [
            'type' => 'belongsTo',
            'target' => User::class,
        ],
        'items' => [
            'type' => 'hasMany',
            'target' => OrderItem::class,
            'foreignKey' => 'order_id',
        ],
    ])
    
    ->typecastHandler([
        UuidTypecast::class,
    ]);
```

## Benefits of Declarative Approach

### Clean Domain Layer

Your entities remain pure PHP objects without infrastructure concerns:

```php
// Clean POPO - no attributes, no ORM dependencies
class Product
{
    private UuidInterface $id;
    private string $title;
    private int $price;
    
    // Pure business logic
    public function applyDiscount(int $percent): void
    {
        $this->price = $this->price * (100 - $percent) / 100;
    }
}
```

### Centralized Configuration

All ORM configuration in one place:

```php
// build/entities.php
Entity::declare(Product::class)->...
Entity::declare(Order::class)->...
Entity::declare(User::class)->...
```

### Flexibility

Easy to change mapping without modifying entity classes:

```php
// Different mapping for the same entity
Entity::declare(Product::class)
    ->database('primary')
    ->table('products');

// vs

Entity::declare(Product::class)
    ->database('analytics')
    ->table('product_snapshots');
```

### Testability

Entities are easier to test without ORM dependencies:

```php
// Pure unit test - no database needed
public function test_apply_discount(): void
{
    $product = new Product();
    $product->setPrice(1000);
    $product->applyDiscount(10);
    
    $this->assertEquals(900, $product->getPrice());
}
```

## Best Practices

### 1. Use UUID v7 for Primary Keys

```php
// Recommended
->uuid7('id')

// Avoid UUID v4 (bad for index performance)
->uuid4('id')
```

### 2. Always Use Timestamps

```php
Entity::declare(Entity::class)
    ->createdAt()
    ->updatedAt()
```

### 3. Use Optimistic Lock for Critical Data

```php
// For financial operations, orders, inventory
Entity::declare(Order::class)
    ->optimisticLock(rule: 'increment')
```

### 4. Soft Delete for User Data

```php
// For GDPR compliance and data retention
Entity::declare(User::class)
    ->softDelete()
```

### 5. Keep Complex Logic in Event Listeners

```php
// Good: Separate class with DI support
->eventListener(ComplexAuditListener::class)

// Avoid: Complex closures in hooks
->hook(fn($e) => /* 100 lines of code */, /* ... */)
```

### 6. Behavior Order Matters

```php
// Recommended order
Entity::declare(Entity::class)
    ->uuid7('id')              // 1. UUID generation
    ->createdAt()              // 2. Timestamps
    ->updatedAt()
    ->softDelete()             // 3. Soft delete
    ->optimisticLock()         // 4. Concurrency control
    ->hook(/* ... */)          // 5. Custom hooks
    ->eventListener(/* ... */) // 6. Event listeners
    ->columns([/* ... */])     // 7. Standard config
    ->typecast([/* ... */])
```

## Troubleshooting

### Behaviors Not Working

**Problem:** `createdAt`, `updatedAt`, or other behaviors don't set values.

**Solution:** Ensure `EventDrivenCommandGenerator` is registered in your ORM configuration. This package handles this automatically.

### Optimistic Lock Exceptions

**Problem:** Getting `OptimisticLockException` on every update.

**Solutions:**
1. Ensure version field is in columns mapping
2. Use correct type for the lock strategy (`int` for `increment`)
3. Reload entity after save to get updated version

### UUID Not Generated

**Problem:** UUID field is null after persist.

**Solutions:**
1. Remove manual UUID generation from entity constructor
2. Set `nullable: false` in uuid behavior
3. Ensure `UuidTypecast` is registered

## Requirements

- PHP 8.2+
- Duyler Framework
- CycleORM 2.x
- cycle/entity-behavior 1.7+

## Testing

```bash
# Run tests
composer test

# Run static analysis
composer psalm

# Fix code style
composer cs-fix
```

## License

MIT

## Links

- [Duyler Framework](https://github.com/duyler/duyler)
- [CycleORM Documentation](https://cycle-orm.dev/)
- [Entity Behaviors](https://cycle-orm.dev/docs/entity-behaviors)
