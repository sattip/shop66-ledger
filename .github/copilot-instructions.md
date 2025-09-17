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