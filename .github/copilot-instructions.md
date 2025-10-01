# Shop66 Ledger - GitHub Copilot Coding Instructions

Shop66 Ledger is a Laravel 11-based financial management application for retail stores. It features role-based access control, AI-powered document processing (OCR + LLM), comprehensive dashboards, and multi-store financial tracking.

**ALWAYS reference these instructions first and fallback to search or bash commands only when you encounter unexpected information that does not match the info here.**

## Working Effectively

### Bootstrap, Build, and Test the Repository

**Prerequisites Check:**
- Ensure PHP 8.3+ is available: `php --version`
- Ensure Composer 2.8+ is available: `composer --version`
- Ensure Node.js 20+ and NPM 10+ are available: `node --version && npm --version`

**Setup Commands (run in order):**
```bash
cd shop66-ledger-app
composer install --no-interaction
npm install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan storage:link
```

**Build Commands:**
- `npm run build` -- Production build takes ~1.3 seconds. NEVER CANCEL. Set timeout to 60+ seconds.
- `composer install --no-interaction --optimize-autoloader` -- Production dependencies take ~4 seconds. NEVER CANCEL.

**Test Commands:**
- `php artisan test` -- Takes ~0.4 seconds. Tests basic functionality with 2 example tests.
- `./vendor/bin/pint` -- Code formatting, takes ~0.3 seconds.
- `./vendor/bin/pint --test` -- Check formatting without fixing.

**Database Operations:**
- `php artisan migrate` -- Fresh migration takes ~0.5 seconds, creates 24 tables. NEVER CANCEL.
- `php artisan migrate:fresh` -- Reset and re-run all migrations.
- `php artisan migrate:status` -- Check migration status.

### Run the Application

**Development Server:**
- `php artisan serve --host=0.0.0.0 --port=8000` -- Starts Laravel on port 8000.
- Access at `http://localhost:8000` -- Returns Laravel welcome page.
- API endpoints available at `/api/*` (see routes below).

**Asset Development:**
- `npm run dev` -- Will fail in CI environments (expected behavior).
- Set `LARAVEL_BYPASS_ENV_CHECK=1` if you need to run Vite in CI.
- `npm run build` for production assets.

**Queue Workers (when needed):**
- `php artisan queue:work` -- Process background jobs.
- `php artisan horizon` -- Laravel Horizon for queue monitoring.

## Validation

**ALWAYS run through these validation steps after making any changes:**

1. **Build Validation:**
   ```bash
   composer install --no-interaction
   npm install
   npm run build
   ```

2. **Code Quality Validation:**
   ```bash
   ./vendor/bin/pint --test  # Check formatting
   ./vendor/bin/pint         # Fix formatting
   ```

3. **Database Validation:**
   ```bash
   php artisan migrate:fresh  # Reset DB
   php artisan migrate:status # Verify 24 migrations
   ```

4. **Test Validation:**
   ```bash
   php artisan test  # Should pass 2 tests
   ```

5. **Application Validation:**
   ```bash
   php artisan serve --host=0.0.0.0 --port=8000 &
   sleep 3
   curl -I http://localhost:8000  # Should return 200 OK
   kill %1  # Stop server
   ```

6. **Manual Application Testing:**
   - Start server: `php artisan serve`
   - Visit `http://localhost:8000` -- Should show Laravel welcome page
   - Test API endpoint: `curl -X POST http://localhost:8000/api/auth/login` -- Should return 422 (validation error, expected)
   - Check routes: `php artisan route:list | head -10` -- Should show API routes

**CRITICAL Build Timings (NEVER CANCEL):**
- Composer install: ~60 seconds (full), ~4 seconds (production)
- NPM install: ~18 seconds
- NPM build: ~1.3 seconds (set timeout to 60+ seconds)
- Database migration: ~0.5 seconds (set timeout to 120+ seconds)
- Tests: ~0.4 seconds
- Code formatting: ~0.3 seconds

## Common Tasks

### Key Project Structure
```
shop66-ledger-app/
├── app/
│   ├── Http/Controllers/Api/    # API controllers (11 resource controllers)
│   ├── Models/                  # Eloquent models
│   ├── Services/Documents/      # Document processing services
│   └── Providers/              # Service providers
├── database/
│   └── migrations/             # 24 database migration files
├── routes/
│   ├── api.php                 # API routes (auth + resource routes)
│   └── web.php                 # Web routes (welcome page)
├── composer.json               # PHP dependencies (126 packages)
├── package.json                # Node.js dependencies (179 packages)
└── phpunit.xml                 # Test configuration
```

### API Endpoints (Laravel Sanctum Auth Required)
```
POST /api/auth/login           # User authentication
POST /api/auth/logout          # User logout
GET  /api/auth/me              # Current user info

# Store-scoped resources
GET|POST    /api/stores                      # Store management
GET|POST    /api/stores/{store}/users        # Store user assignments
GET|POST    /api/stores/{store}/categories   # Category management
GET|POST    /api/stores/{store}/vendors      # Vendor management
GET|POST    /api/stores/{store}/customers    # Customer management
GET|POST    /api/stores/{store}/items        # Item catalog
GET|POST    /api/stores/{store}/accounts     # Account management
GET|POST    /api/stores/{store}/budgets      # Budget management
GET|POST    /api/stores/{store}/transactions # Transaction management
GET|POST    /api/stores/{store}/documents    # Document processing
```

### Laravel Commands Reference
```bash
php artisan list                    # Show all commands
php artisan tinker                  # Interactive REPL
php artisan route:list              # Show all routes
php artisan migrate:status          # Migration status
php artisan cache:clear             # Clear application cache
php artisan config:cache            # Cache configuration
php artisan queue:work              # Process queued jobs
php artisan horizon                 # Start Horizon (queue monitor)
php artisan storage:link            # Create storage symlink
```

### Technology Stack Details
- **Backend:** Laravel 11.46.0, PHP 8.3+
- **Database:** SQLite (dev), MySQL/PostgreSQL (production)
- **Frontend:** Vite 6.3.6, Tailwind CSS 3.4.13
- **Key Packages:** 
  - spatie/laravel-permission (RBAC)
  - yajra/laravel-datatables-oracle (DataTables)
  - laravel/horizon (Queue monitoring)
  - laravel/sanctum (API auth)
  - barryvdh/laravel-dompdf (PDF generation)
  - intervention/image (Image processing)

### Known Issues and Workarounds
- **Migration files:** Fixed double backslash syntax errors in use statements
- **Vite dev server:** Will not run in CI environments (set LARAVEL_BYPASS_ENV_CHECK=1 if needed)
- **Composer warnings:** Ignore ambiguous class resolution warnings for League\Flysystem
- **Package deprecation:** phpoffice/phpexcel is deprecated, use phpoffice/phpspreadsheet for new features

### Development Workflow Best Practices
1. **Always run tests before committing:** `php artisan test`
2. **Always format code:** `./vendor/bin/pint`
3. **Check routes after adding controllers:** `php artisan route:list`
4. **Clear caches after config changes:** `php artisan cache:clear`
5. **Use database transactions for financial operations**
6. **Scope all queries by store_id for multi-tenancy**
7. **Use queues for document processing:** `php artisan queue:work`

### File Locations for Common Changes
- **API Controllers:** `app/Http/Controllers/Api/`
- **Models:** `app/Models/`
- **Migrations:** `database/migrations/`
- **Routes:** `routes/api.php`, `routes/web.php`
- **Services:** `app/Services/Documents/`
- **Config:** `config/` (database, filesystem, queue, etc.)
- **Tests:** `tests/Feature/`, `tests/Unit/`

### Quick Command Reference
```bash
# Setup new environment
composer install && npm install && cp .env.example .env && php artisan key:generate && touch database/database.sqlite && php artisan migrate && php artisan storage:link

# Reset everything
rm database/database.sqlite && touch database/database.sqlite && php artisan migrate:fresh

# Full build and test cycle
composer install --optimize-autoloader && npm run build && ./vendor/bin/pint && php artisan test

# Start development server with queue worker
php artisan serve & php artisan queue:work
```

## Success Criteria for Changes
- **All tests pass:** `php artisan test`
- **Code is formatted:** `./vendor/bin/pint --test` shows no issues
- **Application starts:** Server responds with 200 OK on `http://localhost:8000`
- **API endpoints accessible:** Routes listed in `php artisan route:list`
- **Database is current:** `php artisan migrate:status` shows all migrations run
- **Assets build successfully:** `npm run build` completes without errors
- **Manual validation completed:** Welcome page displays, API returns expected responses

Always validate your changes work end-to-end by starting the server and testing actual user scenarios, not just unit tests.

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to enhance the user's satisfaction building Laravel applications.

## Foundational Context
This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.3.25
- filament/filament (FILAMENT) - v3
- laravel/framework (LARAVEL) - v11
- laravel/prompts (PROMPTS) - v0
- laravel/sanctum (SANCTUM) - v4
- livewire/livewire (LIVEWIRE) - v3
- laravel/mcp (MCP) - v0
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- phpunit/phpunit (PHPUNIT) - v11
- tailwindcss (TAILWINDCSS) - v3


## Conventions
- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts
- Do not create verification scripts or tinker when tests cover that functionality and prove it works. Unit and feature tests are more important.

## Application Structure & Architecture
- Stick to existing directory structure - don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling
- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Replies
- Be concise in your explanations - focus on what's important rather than explaining obvious details.

## Documentation Files
- You must only create documentation files if explicitly requested by the user.


=== boost rules ===

## Laravel Boost
- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan
- Use the `list-artisan-commands` tool when you need to call an Artisan command to double check the available parameters.

## URLs
- Whenever you share a project URL with the user you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain / IP, and port.

## Tinker / Debugging
- You should use the `tinker` tool when you need to execute PHP to debug code or query Eloquent models directly.
- Use the `database-query` tool when you only need to read from the database.

## Reading Browser Logs With the `browser-logs` Tool
- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)
- Boost comes with a powerful `search-docs` tool you should use before any other approaches. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation specific for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- The 'search-docs' tool is perfect for all Laravel related packages, including Laravel, Inertia, Livewire, Filament, Tailwind, Pest, Nova, Nightwatch, etc.
- You must use this tool to search for Laravel-ecosystem documentation before falling back to other approaches.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic based queries to start. For example: `['rate limiting', 'routing rate limiting', 'routing']`.
- Do not add package names to queries - package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax
- You can and should pass multiple queries at once. The most relevant results will be returned first.

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit"
3. Quoted Phrases (Exact Position) - query="infinite scroll" - Words must be adjacent and in that order
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit"
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms


=== php rules ===

## PHP

- Always use curly braces for control structures, even if it has one line.

### Constructors
- Use PHP 8 constructor property promotion in `__construct()`.
    - <code-snippet>public function __construct(public GitHub $github) { }</code-snippet>
- Do not allow empty `__construct()` methods with zero parameters.

### Type Declarations
- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

<code-snippet name="Explicit Return Types and Method Params" lang="php">
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
</code-snippet>

## Comments
- Prefer PHPDoc blocks over comments. Never use comments within the code itself unless there is something _very_ complex going on.

## PHPDoc Blocks
- Add useful array shape type definitions for arrays when appropriate.

## Enums
- Typically, keys in an Enum should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.


=== filament/core rules ===

## Filament
- Filament is used by this application, check how and where to follow existing application conventions.
- Filament is a Server-Driven UI (SDUI) framework for Laravel. It allows developers to define user interfaces in PHP using structured configuration objects. It is built on top of Livewire, Alpine.js, and Tailwind CSS.
- You can use the `search-docs` tool to get information from the official Filament documentation when needed. This is very useful for Artisan command arguments, specific code examples, testing functionality, relationship management, and ensuring you're following idiomatic practices.
- Utilize static `make()` methods for consistent component initialization.

### Artisan
- You must use the Filament specific Artisan commands to create new files or components for Filament. You can find these with the `list-artisan-commands` tool, or with `php artisan` and the `--help` option.
- Inspect the required options, always pass `--no-interaction`, and valid arguments for other options when applicable.

### Filament's Core Features
- Actions: Handle doing something within the application, often with a button or link. Actions encapsulate the UI, the interactive modal window, and the logic that should be executed when the modal window is submitted. They can be used anywhere in the UI and are commonly used to perform one-time actions like deleting a record, sending an email, or updating data in the database based on modal form input.
- Forms: Dynamic forms rendered within other features, such as resources, action modals, table filters, and more.
- Infolists: Read-only lists of data.
- Notifications: Flash notifications displayed to users within the application.
- Panels: The top-level container in Filament that can include all other features like pages, resources, forms, tables, notifications, actions, infolists, and widgets.
- Resources: Static classes that are used to build CRUD interfaces for Eloquent models. Typically live in `app/Filament/Resources`.
- Schemas: Represent components that define the structure and behavior of the UI, such as forms, tables, or lists.
- Tables: Interactive tables with filtering, sorting, pagination, and more.
- Widgets: Small component included within dashboards, often used for displaying data in charts, tables, or as a stat.

### Relationships
- Determine if you can use the `relationship()` method on form components when you need `options` for a select, checkbox, repeater, or when building a `Fieldset`:

<code-snippet name="Relationship example for Form Select" lang="php">
Forms\Components\Select::make('user_id')
    ->label('Author')
    ->relationship('author')
    ->required(),
</code-snippet>


## Testing
- It's important to test Filament functionality for user satisfaction.
- Ensure that you are authenticated to access the application within the test.
- Filament uses Livewire, so start assertions with `livewire()` or `Livewire::test()`.

### Example Tests

<code-snippet name="Filament Table Test" lang="php">
    livewire(ListUsers::class)
        ->assertCanSeeTableRecords($users)
        ->searchTable($users->first()->name)
        ->assertCanSeeTableRecords($users->take(1))
        ->assertCanNotSeeTableRecords($users->skip(1))
        ->searchTable($users->last()->email)
        ->assertCanSeeTableRecords($users->take(-1))
        ->assertCanNotSeeTableRecords($users->take($users->count() - 1));
</code-snippet>

<code-snippet name="Filament Create Resource Test" lang="php">
    livewire(CreateUser::class)
        ->fillForm([
            'name' => 'Howdy',
            'email' => 'howdy@example.com',
        ])
        ->call('create')
        ->assertNotified()
        ->assertRedirect();

    assertDatabaseHas(User::class, [
        'name' => 'Howdy',
        'email' => 'howdy@example.com',
    ]);
</code-snippet>

<code-snippet name="Testing Multiple Panels (setup())" lang="php">
    use Filament\Facades\Filament;

    Filament::setCurrentPanel('app');
</code-snippet>

<code-snippet name="Calling an Action in a Test" lang="php">
    livewire(EditInvoice::class, [
        'invoice' => $invoice,
    ])->callAction('send');

    expect($invoice->refresh())->isSent()->toBeTrue();
</code-snippet>


=== filament/v3 rules ===

## Filament 3

## Version 3 Changes To Focus On
- Resources are located in `app/Filament/Resources/` directory.
- Resource pages (List, Create, Edit) are auto-generated within the resource's directory - e.g., `app/Filament/Resources/PostResource/Pages/`.
- Forms use the `Forms\Components` namespace for form fields.
- Tables use the `Tables\Columns` namespace for table columns.
- A new `Filament\Forms\Components\RichEditor` component is available.
- Form and table schemas now use fluent method chaining.
- Added `php artisan filament:optimize` command for production optimization.
- Requires implementing `FilamentUser` contract for production access control.


=== laravel/core rules ===

## Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using the `list-artisan-commands` tool.
- If you're creating a generic PHP class, use `artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Database
- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

### Model Creation
- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `list-artisan-commands` to check the available options to `php artisan make:model`.

### APIs & Eloquent Resources
- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

### Controllers & Validation
- Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

### Queues
- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

### Authentication & Authorization
- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

### URL Generation
- When generating links to other pages, prefer named routes and the `route()` function.

### Configuration
- Use environment variables only in configuration files - never use the `env()` function directly outside of config files. Always use `config('app.name')`, not `env('APP_NAME')`.

### Testing
- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] <name>` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

### Vite Error
- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.


=== laravel/v11 rules ===

## Laravel 11

- Use the `search-docs` tool to get version specific documentation.
- Laravel 11 brought a new streamlined file structure which this project now uses.

### Laravel 11 Structure
- No middleware files in `app/Http/Middleware/`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- **No app\Console\Kernel.php** - use `bootstrap/app.php` or `routes/console.php` for console configuration.
- **Commands auto-register** - files in `app/Console/Commands/` are automatically available and do not require manual registration.

### Database
- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 11 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models
- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.

### New Artisan Commands
- List Artisan commands using Boost's MCP tool, if available. New commands available in Laravel 11:
    - `php artisan make:enum`
    - `php artisan make:class`
    - `php artisan make:interface`


=== livewire/core rules ===

## Livewire Core
- Use the `search-docs` tool to find exact version specific documentation for how to write Livewire & Livewire tests.
- Use the `php artisan make:livewire [Posts\\CreatePost]` artisan command to create new components
- State should live on the server, with the UI reflecting it.
- All Livewire requests hit the Laravel backend, they're like regular HTTP requests. Always validate form data, and run authorization checks in Livewire actions.

## Livewire Best Practices
- Livewire components require a single root element.
- Use `wire:loading` and `wire:dirty` for delightful loading states.
- Add `wire:key` in loops:

    ```blade
    @foreach ($items as $item)
        <div wire:key="item-{{ $item->id }}">
            {{ $item->name }}
        </div>
    @endforeach
    ```

- Prefer lifecycle hooks like `mount()`, `updatedFoo()` for initialization and reactive side effects:

<code-snippet name="Lifecycle hook examples" lang="php">
    public function mount(User $user) { $this->user = $user; }
    public function updatedSearch() { $this->resetPage(); }
</code-snippet>


## Testing Livewire

<code-snippet name="Example Livewire component test" lang="php">
    Livewire::test(Counter::class)
        ->assertSet('count', 0)
        ->call('increment')
        ->assertSet('count', 1)
        ->assertSee(1)
        ->assertStatus(200);
</code-snippet>


    <code-snippet name="Testing a Livewire component exists within a page" lang="php">
        $this->get('/posts/create')
        ->assertSeeLivewire(CreatePost::class);
    </code-snippet>


=== livewire/v3 rules ===

## Livewire 3

### Key Changes From Livewire 2
- These things changed in Livewire 2, but may not have been updated in this application. Verify this application's setup to ensure you conform with application conventions.
    - Use `wire:model.live` for real-time updates, `wire:model` is now deferred by default.
    - Components now use the `App\Livewire` namespace (not `App\Http\Livewire`).
    - Use `$this->dispatch()` to dispatch events (not `emit` or `dispatchBrowserEvent`).
    - Use the `components.layouts.app` view as the typical layout path (not `layouts.app`).

### New Directives
- `wire:show`, `wire:transition`, `wire:cloak`, `wire:offline`, `wire:target` are available for use. Use the documentation to find usage examples.

### Alpine
- Alpine is now included with Livewire, don't manually include Alpine.js.
- Plugins included with Alpine: persist, intersect, collapse, and focus.

### Lifecycle Hooks
- You can listen for `livewire:init` to hook into Livewire initialization, and `fail.status === 419` for the page expiring:

<code-snippet name="livewire:load example" lang="js">
document.addEventListener('livewire:init', function () {
    Livewire.hook('request', ({ fail }) => {
        if (fail && fail.status === 419) {
            alert('Your session expired');
        }
    });

    Livewire.hook('message.failed', (message, component) => {
        console.error(message);
    });
});
</code-snippet>


=== pint/core rules ===

## Laravel Pint Code Formatter

- You must run `vendor/bin/pint --dirty` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test`, simply run `vendor/bin/pint` to fix any formatting issues.


=== phpunit/core rules ===

## PHPUnit Core

- This application uses PHPUnit for testing. All tests must be written as PHPUnit classes. Use `php artisan make:test --phpunit <name>` to create a new test.
- If you see a test using "Pest", convert it to PHPUnit.
- Every time a test has been updated, run that singular test.
- When the tests relating to your feature are passing, ask the user if they would like to also run the entire test suite to make sure everything is still passing.
- Tests should test all of the happy paths, failure paths, and weird paths.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files, these are core to the application.

### Running Tests
- Run the minimal number of tests, using an appropriate filter, before finalizing.
- To run all tests: `php artisan test`.
- To run all tests in a file: `php artisan test tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `php artisan test --filter=testName` (recommended after making a change to a related file).


=== tailwindcss/core rules ===

## Tailwind Core

- Use Tailwind CSS classes to style HTML, check and use existing tailwind conventions within the project before writing your own.
- Offer to extract repeated patterns into components that match the project's conventions (i.e. Blade, JSX, Vue, etc..)
- Think through class placement, order, priority, and defaults - remove redundant classes, add classes to parent or child carefully to limit repetition, group elements logically
- You can use the `search-docs` tool to get exact examples from the official documentation when needed.

### Spacing
- When listing items, use gap utilities for spacing, don't use margins.

    <code-snippet name="Valid Flex Gap Spacing Example" lang="html">
        <div class="flex gap-8">
            <div>Superior</div>
            <div>Michigan</div>
            <div>Erie</div>
        </div>
    </code-snippet>


### Dark Mode
- If existing pages and components support dark mode, new pages and components must support dark mode in a similar way, typically using `dark:`.


=== tailwindcss/v3 rules ===

## Tailwind 3

- Always use Tailwind CSS v3 - verify you're using only classes supported by this version.
</laravel-boost-guidelines>
