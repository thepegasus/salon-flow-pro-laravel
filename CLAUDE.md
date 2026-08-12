# Project: SalonFlow Pro

## Stack
- **Framework:** Laravel 13.x
- **PHP:** 8.3+
- **Database:**  PostgreSQL
- **Testing:** PHPUnit 12.x (no Pest), Mockery, Faker
- **Code Style:** Laravel Pint (PSR-12)
- **Logging:** Laravel Pail
- **Pattern:** Repository Pattern
- **ACL:** spatie/laravel-permission (role-based)
 
## Quick Commands
```bash
composer setup          # Install, env, key, migrate, npm build
composer dev            # Start server + queue + pail + vite concurrently
composer test           # Clear config + run tests

./vendor/bin/pint --dirty --format agent   # Fix style on changed files
./vendor/bin/pint --format agent           # Fix all style issues

php artisan test --compact                              # Run all tests
php artisan test --compact tests/Feature/SomeTest.php   # Run one file
php artisan test --compact --filter=testName             # Run one test
php artisan migrate:fresh --seed

# ACL commands
php artisan db:seed --class=PermissionSeeder --no-interaction   # Re-seed permissions
php artisan permission:cache-reset --no-interaction             # Clear permission cache
```
## Tenant Isolation Rules — READ FIRST
- YOU MUST scope every Eloquent query through the tenant-aware global scope.
  Never query a tenant-scoped model without it, including in seeders,
  console commands, and queued jobs.
- YOU MUST NOT use `withoutGlobalScope()` on tenant-scoped models unless
  explicitly instructed for a specific admin-only context.
- New migrations touching tenant data require a `tenant_id` foreign key
  and a corresponding index. No exceptions.

---

## Core Principle

**Follow Laravel conventions first.** If Laravel has a documented way to do something, use it. Only deviate with clear justification. Use `php artisan make:*` commands to create new files. Check with `php artisan list` and `php artisan [command] --help` for available options. Always pass `--no-interaction` to Artisan commands.

---
 
## PHP Standards

### Type System
- Follow PSR-1, PSR-2, and PSR-12 strictly.
- Use typed properties over docblocks.
- Use PHP 8 constructor property promotion when all properties can be promoted.
- Specify return types on all methods, including `void`.
- Use short nullable notation: `?string` not `string|null`.
- Use explicit type hints for all method parameters.

```php
// GOOD
public function __construct(
    private OrderRepositoryInterface $orderRepository,
    private PaymentService $paymentService,
) {}

public function findBySlug(?string $slug): ?Order
{
    // ...
}

// BAD — untyped, no promotion
private OrderRepositoryInterface $orderRepository;

public function __construct(OrderRepositoryInterface $orderRepository)
{
    $this->orderRepository = $orderRepository;
}
```

### Docblocks
- Don't use docblocks for fully type-hinted methods unless a description is needed.
- Always import classnames — never use fully qualified names in docblocks.
- Use one-line docblocks when possible: `/** @var string */`
- For iterables, always specify key and value types:

```php
/** @return Collection<int, User> */
public function getActiveUsers(): Collection

/**
 * @param array<int, MyObject> $items
 * @param int $limit
 */
public function process(array $items, int $limit): void
```

- Use array shape notation for fixed keys:

```php
/**
 * @return array{
 *     first: SomeClass,
 *     second: SomeClass,
 * }
 */
```

### Control Flow
- **Happy path last** — handle error conditions first, success case last.
- **Avoid else** — use early returns instead.
- Always use curly braces, even for single-line bodies.
- Separate conditions: prefer multiple `if` statements over compound conditions.

```php
// GOOD — happy path last, no else
public function process(User $user): Order
{
    if (! $user) {
        return null;
    }

    if (! $user->isActive()) {
        throw new InactiveUserException();
    }

    return $this->orderRepository->createForUser($user);
}

// BAD — nested else
public function process(User $user): Order
{
    if ($user) {
        if ($user->isActive()) {
            return $this->orderRepository->createForUser($user);
        } else {
            throw new InactiveUserException();
        }
    } else {
        return null;
    }
}
```

### Ternary
```php
// Short — single line is fine
$name = $isFoo ? 'foo' : 'bar';

// Longer — each part on its own line
$result = $object instanceof Model
    ? $object->name
    : 'A default value';
```

### Strings
- Use string interpolation over concatenation: `"Hello {$user->name}"` not `'Hello ' . $user->name`.

### Enums & Constants
- Use `PascalCase` for enum values and class constants.

```php
enum OrderStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Completed = 'completed';
}

class Session
{
    public const SessionTokenHeader = 'X-Session-Token';
}
```

### Comments
- Be very critical about adding comments — code should be self-documenting.
- Use descriptive variable and function names instead of comments.
- Only add comments to explain **why** something non-obvious is done, never **what**.
- Never add comments to tests — test names should be descriptive enough.

```php
// BAD
// Get the failed checks for this site
$checks = $site->checks()->where('status', 'failed')->get();

// GOOD
$failedChecks = $site->checks()->where('status', 'failed')->get();
```

### Whitespace
- Add blank lines between statements for readability.
- Exception: sequences of equivalent single-line operations.
- No extra empty lines between `{}` brackets.
- Let code "breathe" — avoid cramped formatting.

### Traits
- One trait per line in `use` statements.

---

## Architecture Rules

### Directory Structure
```
salon-flow-pro-laravel/
├── app/
│   ├── Actions/                        # Single-purpose action classes
│   ├── Http/
│   │   ├── Controllers/               # Thin controllers (no business logic)
│   │   ├── Requests/                  # Form Request validation
│   │   └── Resources/                 # API Resources (if applicable)
│   ├── Models/                        # Eloquent models
│   ├── Repositories/
│   │   ├── Contracts/                 # Repository interfaces
│   │   └── Eloquent/                  # Eloquent implementations
│   ├── Services/                      # Business logic services
│   ├── Policies/                      # Authorization policies
│   └── Providers/                     # Service providers (repository bindings)
│
├── resources/
│   ├── admin/                         # ★ Admin panel design & assets
│   │   └── assets/
│   │       ├── css/
│   │       │   └── styles.css         # Custom admin styles
│   │       ├── js/
│   │       │   └── scripts.js         # Custom admin scripts
│   │       └── vendor/                # Third-party libs (committed, not CDN)
│   │           ├── bootstrap/
│   │           │   ├── css/bootstrap.min.css
│   │           │   └── js/bootstrap.bundle.min.js
│   │           ├── bootstrap-icons/
│   │           │   └── font/
│   │           │       ├── bootstrap-icons.min.css
│   │           │       └── fonts/
│   │           ├── chartjs/
│   │           │   └── chart.umd.min.js
│   │           └── fonts/
│   │
│   ├── views/
│   │   ├── admin/                     # Admin Blade templates
│   │   ├── components/                # Shared Blade components
│   │   └── layouts/                   # Layout templates
│   ├── css/
│   └── js/
│
├── tests/
│   ├── Unit/                          # Services, actions, repositories
│   ├── Feature/                       # HTTP request lifecycle (primary)
│   ├── Integration/                   # DB, APIs, queues
│   └── Regression/                    # Bug reproduction tests
│
├── composer.json
├── vite.config.js
└── CLAUDE.md
```

### Naming Conventions

| Thing              | Convention       | Example                              |
|--------------------|------------------|--------------------------------------|
| Classes            | PascalCase       | `UserController`, `OrderStatus`      |
| Methods/Variables  | camelCase        | `getUserName`, `$firstName`          |
| Routes (URLs)      | kebab-case       | `/open-source`, `/user-profile`      |
| Route names        | camelCase        | `->name('openSource')`               |
| Route parameters   | camelCase        | `{userId}`                           |
| Config files       | kebab-case       | `pdf-generator.php`                  |
| Config keys        | snake_case       | `chrome_path`                        |
| Artisan commands   | kebab-case       | `php artisan delete-old-records`     |
| DB tables/columns  | snake_case       | `order_items`, `user_id`             |
| Controllers        | Plural + suffix  | `PostsController`                    |
| Views              | camelCase        | `openSource.blade.php`               |
| Jobs               | Action-based     | `CreateUser`, `SendEmailNotification`|
| Events             | Tense-based      | `UserRegistering`, `UserRegistered`  |
| Listeners          | Action + Listener| `SendInvitationMailListener`         |
| Mailables          | Purpose + Mail   | `AccountActivatedMail`               |
| Resources          | Plural + Resource| `UsersResource`                      |
| Enums              | Descriptive name | `OrderStatus`, `BookingType`         |
| Permissions        | snake_case       | `module_name.view`, `module_name.create` |

### Controllers
- **Thin controllers** — they only:
  1. Check permission (`abort_unless` as first line)
  2. Receive the request (via Form Request)
  3. Delegate to a Service or Action
  4. Return a response or view
- Use **plural resource names**: `PostsController`, `OrdersController`.
- Stick to CRUD methods: `index`, `create`, `store`, `show`, `edit`, `update`, `destroy`.
- Extract new controllers for non-CRUD actions.
- Use method injection for dependencies.

```php
// GOOD
public function store(StoreOrderRequest $request, OrderService $service): RedirectResponse
{
    abort_unless(auth()->user()->can('orders.create'), 403);

    $order = $service->create($request->validated());

    return redirect()->route('orders.show', $order);
}
```

### Form Requests
- All input validation goes in a dedicated Form Request class.
- Name descriptively: `StoreOrderRequest`, `UpdateUserRequest`.
- Use array notation for rules (easier for custom rule classes):

```php
public function rules(): array
{
    return [
        'email' => ['required', 'email'],
        'name' => ['required', 'string', 'max:255'],
    ];
}
```

- Custom validation rules use snake_case.

### Services
- Contain business logic and orchestration.
- Inject repositories via constructor (interface, not concrete).
- Keep methods focused — one action per method.

```php
class OrderService
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
        private PaymentService $paymentService,
    ) {}

    public function create(array $data): Order
    {
        $order = $this->orderRepository->create($data);

        $this->paymentService->charge($order);

        return $order;
    }
}
```

### Actions
- Use for one-off operations that don't fit neatly in a service.
- One public method: `execute()` or `handle()`.
- Name clearly: `GenerateInvoicePdf`, `SyncUserPermissions`.

### Repository Pattern
- Define an **interface** in `App\Repositories\Contracts\`.
- Implement in `App\Repositories\Eloquent\`.
- Bind interface to implementation in a **ServiceProvider**.
- Models should never be queried directly in controllers or services.

```php
// Interface
namespace App\Repositories\Contracts;

interface OrderRepositoryInterface
{
    public function findById(int $id): ?Order;

    public function create(array $data): Order;

    /** @return Collection<int, Order> */
    public function getByUser(int $userId): Collection;
}

// Implementation
namespace App\Repositories\Eloquent;

class OrderRepository implements OrderRepositoryInterface
{
    public function __construct(private Order $model) {}

    public function findById(int $id): ?Order
    {
        return $this->model->find($id);
    }

    public function create(array $data): Order
    {
        return $this->model->create($data);
    }

    /** @return Collection<int, Order> */
    public function getByUser(int $userId): Collection
    {
        return $this->model->where('user_id', $userId)->get();
    }
}

// Bind in AppServiceProvider or RepositoryServiceProvider
$this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);
```

### Models
- Models contain only: relationships, scopes, casts, accessors/mutators.
- No business logic in models.
- Use mass assignment protection (`$fillable` or `$guarded`).
- When creating new models, create useful factories and seeders too.

### Routes
- URLs: kebab-case (`/open-source`)
- Route names: camelCase (`->name('openSource')`)
- Parameters: camelCase (`{userId}`)
- Use tuple notation: `[Controller::class, 'method']`
- Use named routes and the `route()` function for URL generation.
- **Every route must have `permission:` middleware** (see ACL section).

### Configuration
- Add service configs to `config/services.php` — don't create new config files without justification.
- Use `config()` helper — never `env()` outside config files.
- Config files: kebab-case. Config keys: snake_case.

### Artisan Commands
- Names: kebab-case (`delete-old-records`).
- Always provide feedback (`$this->comment('All ok!')`).
- Show progress for loops, summary at end.
- Put output BEFORE processing the item (easier debugging):

```php
$items->each(function (Item $item): void {
    $this->info("Processing item id {$item->id}...");
    $this->processItem($item);
});

$this->comment("Processed {$items->count()} items.");
```

---

## Blade & Frontend

### Blade Rules
- Blade templates are **presentation only**.
- No raw PHP blocks, no complex logic.
- Allowed: `@if`, `@foreach`, `@auth`, `@can`, `@canany`, simple conditionals.
- Forbidden: DB queries, service calls, heavy computations.
- Pass all data from the controller.
- Use `__()` function over `@lang` for translations.
- Indent with 4 spaces.
- No spaces after control structures:

```blade
@if($condition)
    Something
@endif
```

- **All action buttons must be wrapped in `@can` directives** (see ACL section).

### Components
- Reuse Blade components — never duplicate UI markup.
- Check for existing components before creating new ones.
- Shared components: `resources/views/components/`.
- Admin templates: `resources/views/admin/`.

### Admin vs Public Separation
- Admin views use **Bootstrap classes** and load assets from `resources/admin/assets/`.
- Public-facing views use **Tailwind** (via Vite) if applicable.
- Do not mix Bootstrap and Tailwind in the same template.

---

## Admin Panel Design (`resources/admin/`)

### Asset Architecture
The admin panel uses a **custom Bootstrap 5 UI**. All admin assets are self-contained under `resources/admin/assets/` and vendored into the repo.

### Vendored Libraries (Do NOT Update Without Discussion)

| Library            | Path                                             | Purpose              |
|--------------------|--------------------------------------------------|----------------------|
| Bootstrap 5        | `vendor/bootstrap/css/` + `vendor/bootstrap/js/` | Grid, layout, UI     |
| Bootstrap Icons    | `vendor/bootstrap-icons/font/`                   | Icon system          |
| Chart.js (UMD)     | `vendor/chartjs/chart.umd.min.js`                | Dashboard charts     |
| Custom Fonts       | `vendor/fonts/`                                  | Project typography   |

### Custom Files
- **`assets/css/styles.css`** — All custom admin styles. Never modify vendored CSS.
- **`assets/js/scripts.js`** — All custom admin JS. Chart.js init, Bootstrap triggers, dashboard interactivity.

### Admin Style Rules
- Use **Bootstrap utility classes** in admin Blade templates (not Tailwind).
- Custom styles go in `styles.css` only — never inline unless absolutely necessary.
- Use Bootstrap Icons (`bi bi-*`) for all admin icons.
- Organize `styles.css` with section comments.

### Chart.js Usage
- Initialize all charts in `scripts.js`.
- Pass data from Blade via `data-*` attributes or inline JSON — never hardcode data in JS.

```javascript
const ctx = document.getElementById('revenueChart');
const chartData = JSON.parse(ctx.dataset.chartData);
new Chart(ctx, { type: 'line', data: chartData, options: { ... } });
```

### Font Management
- Custom fonts in `vendor/fonts/`, referenced via `@font-face` in `styles.css`.
- Bootstrap Icons fonts in `vendor/bootstrap-icons/font/fonts/` — do not relocate.

---

## Database

### Migrations
- All schema changes go through migrations — **no manual DB changes ever**.
- Use `php artisan make:migration` to create them.
- **Only write `up()` methods** — do not write `down()` methods. Migrations are forward-only by design; to undo a schema change in a deployed environment, write a new corrective migration rather than rolling back.
- Use descriptive names: `create_orders_table`, `add_status_to_orders_table`.
- Use `snake_case` for all table names and column names.
- Add `->comment()` on tables and important columns.
- Define proper foreign keys with cascading behavior.
- Add indexes on columns used in `WHERE`, `ORDER BY`, and `JOIN` clauses.

```php
public function up(): void
{
    Schema::create('orders', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->string('status')->default('pending')->comment('pending|processing|completed|cancelled');
        $table->decimal('total_amount', 10, 2)->comment('Order total in base currency');
        $table->timestamps();
        $table->softDeletes();

        $table->index(['user_id', 'status']);
    });
}
```

### Relationships
- Always define both sides of a relationship.
- Use proper relationship types (hasMany, belongsTo, morphMany, etc.).

---

## Security

- Validate **all** input via Form Requests.
- Use **Policies** or **Gates** for authorization on every action.
- Policies use camelCase: `Gate::define('editPost', ...)`.
- Use CRUD words for policy methods, but `view` instead of `show`.
- Never expose secrets — all sensitive values in `.env`, accessed via `config()`.
- Use mass assignment protection (`$fillable` or `$guarded`) on all models.
- Sanitize outputs in Blade with `{{ }}` (escaped) — use `{!! !!}` only when explicitly safe.
- **Every route and controller action must enforce permissions** (see ACL section).

---

## Testing (Mandatory — All 4 Types Per Story)

> **NO story is complete until it has Unit, Feature, Integration, and Regression tests.**
> This is non-negotiable. A pull request missing any of the four test types will be rejected.

### Trivial Change Exception
The 4-type mandate does not apply to changes with **no new logic branch, query, or endpoint** — e.g. typo/copy fixes, comment or docblock edits, formatting-only changes, config default tweaks, or `.env`/`config/*.php` value changes. Any change touching a Service, Repository, Controller, Model behavior, migration, route, or Blade conditional is never trivial and requires the full 4 types. When in doubt, treat it as non-trivial.

### The 4 Mandatory Test Types

Every module/story MUST have all four:

| Test Type       | What It Covers                                    | Location                              |
|-----------------|---------------------------------------------------|---------------------------------------|
| **Unit**        | Services, Actions, Repositories (isolated, mocked)| `tests/Unit/{Module}/`                |
| **Feature**     | Full HTTP lifecycle — user-facing flows            | `tests/Feature/{Module}/`             |
| **Integration** | DB operations, queues, events, external APIs       | `tests/Integration/{Module}/`         |
| **Regression**  | Bug reproduction — proves a fix, prevents reoccurrence | `tests/Regression/{Module}/`      |

### ACL-Specific Tests (Mandatory)

Every module's **Feature tests** must include permission tests:

```php
// tests/Feature/ModuleName/ModuleNameAccessTest.php

public function test_admin_can_access_module_name_index(): void
{
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->get(route('moduleName.index'));

    $response->assertOk();
}

public function test_viewer_cannot_access_module_name_create(): void
{
    $viewer = User::factory()->create();
    $viewer->assignRole('viewer');

    $response = $this->actingAs($viewer)->get(route('moduleName.create'));

    $response->assertForbidden();
}

public function test_editor_cannot_delete_module_name(): void
{
    $editor = User::factory()->create();
    $editor->assignRole('editor');
    $item = ModuleName::factory()->create();

    $response = $this->actingAs($editor)->delete(route('moduleName.destroy', $item->id));

    $response->assertForbidden();
}

public function test_guest_is_redirected_to_login(): void
{
    $response = $this->get(route('moduleName.index'));

    $response->assertRedirect('/login');
}
```

### Test Directory Structure (Per Module)
```
tests/
├── Unit/
│   └── Orders/
│       ├── OrderServiceTest.php
│       ├── OrderRepositoryTest.php
│       └── CalculateOrderTotalActionTest.php
├── Feature/
│   └── Orders/
│       ├── CreateOrderTest.php
│       ├── UpdateOrderTest.php
│       ├── DeleteOrderTest.php
│       ├── ListOrdersTest.php
│       └── OrderAccessTest.php          # ★ ACL permission tests
├── Integration/
│   └── Orders/
│       ├── OrderDatabaseTest.php
│       ├── OrderPaymentGatewayTest.php
│       └── OrderNotificationQueueTest.php
└── Regression/
    └── Orders/
        ├── OrderDuplicateChargeFixTest.php
        └── OrderStatusRaceConditionFixTest.php
```

### Story Completion Checklist
Before marking any story as done, verify ALL of the following:

- [ ] **Unit tests** — Every Service, Action, and Repository method has tests. Dependencies are mocked via interfaces. Both success and exception paths are covered.
- [ ] **Feature tests** — Every controller endpoint / user flow is tested end-to-end. Covers: valid input, validation errors, unauthorized access, not found (404), and redirect/response assertions.
- [ ] **Feature tests (ACL)** — Permission tests for each role: admin can access all, editor cannot delete, viewer can only view, guest redirects to login.
- [ ] **Integration tests** — DB read/write operations verified with `assertDatabaseHas` / `assertDatabaseMissing`. Queue jobs dispatched and processed. Events fired and listeners triggered. External API calls tested (mocked at HTTP level, not service level).
- [ ] **Regression tests** — Every bug fix has a corresponding test that reproduces the original bug, then asserts the fix. Named descriptively to document the bug: `test_order_total_no_longer_doubles_on_retry()`.

### General Rules
- All tests are **PHPUnit classes** — no Pest. If you see Pest, convert it to PHPUnit.
- Use `php artisan make:test --phpunit {name}` to create tests. Pass `--unit` for unit tests.
- Cover all happy paths, failure paths, and edge cases.
- Use factories and seeders for test data — never hardcode IDs. Check if factories have custom states before manually setting up models.
- Follow the **arrange-act-assert** pattern in every test.
- Do NOT remove any tests or test files without approval.
- Do NOT create verification scripts when tests already cover the functionality.

### Running Tests
- Run the **minimal number of tests** using an appropriate filter before finalizing.
- After your related tests pass, ask if the full suite should be run.

```bash
php artisan test --compact                                    # All tests
php artisan test --compact tests/Feature/Orders/              # One module
php artisan test --compact tests/Feature/Orders/CreateOrderTest.php  # One file
php artisan test --compact --filter=test_user_can_create_order       # One test
```

### Naming Conventions
- Test directories: match module name (`Orders/`, `Users/`, `Payments/`).
- Test classes: descriptive + `Test` suffix (`OrderServiceTest`, `CreateOrderTest`).
- Test methods: `test_` prefix + descriptive snake_case (`test_authenticated_user_can_create_order`).
- Regression test methods: describe the bug (`test_order_total_no_longer_doubles_on_retry`).
- Test names should be descriptive enough that no comments are needed.

### Examples

```php
// ============================================================
// UNIT TEST — tests/Unit/Orders/OrderServiceTest.php
// Isolated business logic, all dependencies mocked
// ============================================================
class OrderServiceTest extends TestCase
{
    public function test_create_delegates_to_repository_and_charges_payment(): void
    {
        $repository = Mockery::mock(OrderRepositoryInterface::class);
        $repository->shouldReceive('create')
            ->once()
            ->with(['product_id' => 1, 'quantity' => 2])
            ->andReturn(new Order());

        $paymentService = Mockery::mock(PaymentService::class);
        $paymentService->shouldReceive('charge')->once();

        $service = new OrderService($repository, $paymentService);
        $order = $service->create(['product_id' => 1, 'quantity' => 2]);

        $this->assertInstanceOf(Order::class, $order);
    }

    public function test_create_throws_exception_for_invalid_product(): void
    {
        $repository = Mockery::mock(OrderRepositoryInterface::class);
        $repository->shouldReceive('create')->never();

        $paymentService = Mockery::mock(PaymentService::class);

        $service = new OrderService($repository, $paymentService);

        $this->expectException(InvalidProductException::class);
        $service->create(['product_id' => 999, 'quantity' => 2]);
    }
}

// ============================================================
// FEATURE TEST — tests/Feature/Orders/CreateOrderTest.php
// Full HTTP request lifecycle
// ============================================================
class CreateOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_order(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $response = $this->actingAs($user)->post('/orders', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_guest_cannot_create_order(): void
    {
        $response = $this->post('/orders', [
            'product_id' => 1,
            'quantity' => 2,
        ]);

        $response->assertRedirect('/login');
    }

    public function test_validation_rejects_missing_product(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/orders', [
            'quantity' => 2,
        ]);

        $response->assertSessionHasErrors('product_id');
    }
}

// ============================================================
// INTEGRATION TEST — tests/Integration/Orders/OrderDatabaseTest.php
// Real DB, queues, events — no mocking at service level
// ============================================================
class OrderDatabaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_persists_with_correct_relationships(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 49.99]);

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'total_amount' => 99.98,
        ]);

        $this->assertDatabaseHas('orders', ['id' => $order->id]);
        $this->assertTrue($order->user->is($user));
        $this->assertTrue($order->product->is($product));
    }

    public function test_order_creation_dispatches_notification_job(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $service = app(OrderService::class);

        $service->create([
            'user_id' => $user->id,
            'product_id' => Product::factory()->create()->id,
            'quantity' => 1,
        ]);

        Queue::assertPushed(SendOrderConfirmationJob::class);
    }
}

// ============================================================
// REGRESSION TEST — tests/Regression/Orders/OrderDuplicateChargeFixTest.php
// Reproduces a specific bug, proves it's fixed
// ============================================================
class OrderDuplicateChargeFixTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Bug: Submitting the create order form twice in rapid succession
     * caused the payment to be charged twice. Fixed in commit abc123.
     */
    public function test_order_total_no_longer_doubles_on_retry(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 50.00]);

        $payload = [
            'product_id' => $product->id,
            'quantity' => 1,
        ];

        $this->actingAs($user)->post('/orders', $payload);
        $this->actingAs($user)->post('/orders', $payload);

        $this->assertDatabaseCount('orders', 1);
    }
}
```

---

## Code Style (Pint)

After modifying any PHP file, **always run Pint** before finalizing:

```bash
./vendor/bin/pint --dirty --format agent   # Changed files only
./vendor/bin/pint --format agent           # All files
```

Never run `--test` mode — just fix directly.

---

## Git & Workflow
- Branch naming: `feature/`, `bugfix/`, `hotfix/` prefixes.
- Commit messages: imperative mood (`Add order creation`, not `Added...`).
- Run `composer test` and `./vendor/bin/pint --dirty --format agent` before every commit.
- Do not change application dependencies without approval.
- Do not create new base folders without approval.
- Only create documentation files if explicitly requested.

---

## Vite & Frontend Build

If a frontend change isn't reflected in the browser, it likely means one of these needs to run:

```bash
npm run build       # One-time production build
npm run dev         # Dev server with HMR
composer run dev    # Full dev stack (server + queue + pail + vite)
```

If you encounter `Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest`, run `npm run build`.

---

## New Module Complete Checklist

When creating ANY new module, verify everything:

### Architecture
- [ ] Model with `$fillable`, relationships, casts
- [ ] Factory and seeder
- [ ] Migration (with indexes, foreign keys, comments)
- [ ] Repository interface in `Contracts/`
- [ ] Repository implementation in `Eloquent/`
- [ ] Repository bound in ServiceProvider
- [ ] Service class with business logic
- [ ] Form Requests (`Store` + `Update`)
- [ ] Thin controller (delegates to service)

### ACL (Non-Negotiable)
- [ ] 4 permissions in `PermissionSeeder` (view, create, edit, delete)
- [ ] Permissions assigned to roles
- [ ] Routes wrapped with `permission:` middleware
- [ ] Controller methods have `abort_unless` as first line
- [ ] Menu entry in `config/menu.php` with `permission` key
- [ ] Blade buttons wrapped in `@can`
- [ ] Seeder run + cache cleared

### Views
- [ ] Blade templates in `resources/views/admin/`
- [ ] Bootstrap classes (not Tailwind) for admin
- [ ] Bootstrap Icons (`bi bi-*`)
- [ ] Action buttons wrapped in `@can`

### Testing (Non-Negotiable)
- [ ] Unit tests (services, repositories, actions)
- [ ] Feature tests (HTTP lifecycle + ACL permission tests)
- [ ] Integration tests (DB, queues, events)
- [ ] Regression tests (if fixing bugs)
- [ ] All tests pass: `php artisan test --compact`

### Final
- [ ] Pint run: `./vendor/bin/pint --dirty --format agent`
- [ ] `composer test` passes

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Always use `search-docs` before making code changes. Do not skip this step. It returns version-specific docs based on installed packages automatically.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.
- To check environment variables, read the `.env` file directly.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== phpunit/core rules ===

# PHPUnit

- This application uses PHPUnit for testing. All tests must be written as PHPUnit classes. Use `php artisan make:test --phpunit {name}` to create a new test.
- If you see a test using "Pest", convert it to PHPUnit.
- Every time a test has been updated, run that singular test.
- When the tests relating to your feature are passing, ask the user if they would like to also run the entire test suite to make sure everything is still passing.
- Tests should cover all happy paths, failure paths, and edge cases.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files; these are core to the application.

## Running Tests

- Run the minimal number of tests, using an appropriate filter, before finalizing.
- To run all tests: `php artisan test --compact`.
- To run all tests in a file: `php artisan test --compact tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `php artisan test --compact --filter=testName` (recommended after making a change to a related file).

</laravel-boost-guidelines>
