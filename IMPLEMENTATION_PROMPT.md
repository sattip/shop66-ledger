# Implementation Prompt for Shop66 Ledger

## Primary Prompt

"Build a Laravel 11 multi-store financial management system with the following core features:

1. **Multi-Store Architecture**: Single-tenant application supporting multiple retail stores, each with independent financial tracking, configurable currency, and tax settings.

2. **Role-Based Access Control**: Implement 6 user roles (Super Admin, Admin, Store Manager, Accountant, Staff, Read Only) using spatie/laravel-permission with store-scoped visibility rules.

3. **Document Processing Pipeline**: Create an AI-powered document ingestion system that:
   - Accepts PDF/image uploads
   - Performs OCR text extraction (multiple engines: Textract, Google Vision, Tesseract)
   - Uses LLM (GPT-4) to extract structured data (vendor, line items, totals)
   - Matches extracted data to existing entities (vendors, items, categories)
   - Requires human validation before posting to ledger
   - Implements duplicate detection

4. **Financial Management**:
   - CRUD for transactions (income/expense) with line items
   - Categories with hierarchy
   - Vendor and customer management
   - Multiple account types (cash, bank, card, wallet)
   - Budget tracking

5. **UI/UX**: AdminLTE 3.2 interface with Yajra DataTables, side-by-side document review interface, Chart.js dashboards

6. **Reporting**: Generate financial reports (Income Statement, Expense Report, Vendor Ledger) with export to CSV/XLSX/PDF

7. **API**: RESTful API with Laravel Sanctum authentication for all core operations

## Technical Implementation Requirements

### Database Schema
Create migrations for: users, stores, store_user, categories, vendors, customers, items, accounts, transactions, transaction_lines, documents, document_lines, document_ingestions, attachments, budgets, audits

### Queue Jobs Architecture
```php
// Document processing pipeline
DocumentUploadedJob -> PrepareDocumentJob -> OCRDocumentJob -> ExtractDataJob -> MatchEntitiesJob -> ValidateDocumentJob -> NotifyReviewersJob
```

### Service Layer Structure
- OCRService (pluggable engines)
- LLMExtractionService (structured JSON output)
- EntityMatchingService (fuzzy matching algorithms)
- ValidationService (arithmetic & business rules)
- NotificationService (status updates)

### Security Requirements
- Store-scoped data isolation using global scopes
- Policy-based authorization for all models
- Signed URLs for document access
- Rate limiting on uploads and API endpoints
- Audit logging for all CRUD operations

### Performance Requirements
- Page loads < 2 seconds
- Support 100+ concurrent users
- Process 1000+ documents/day
- Cache extracted data for 7 days
- Queue processing with Redis/Horizon

## Step-by-Step Implementation Order

### Phase 1: Foundation (Week 1)
1. Initialize Laravel 11 project with required packages
2. Create database migrations following the ERD
3. Implement authentication with Laravel Breeze/Jetstream
4. Setup spatie/laravel-permission with 6 roles and permissions
5. Create base models with relationships
6. Implement store-scoped global scopes
7. Setup AdminLTE layout and navigation

### Phase 2: Core CRUD (Week 2)
1. Build Stores management (CRUD + user assignments)
2. Create Categories with parent-child hierarchy
3. Implement Vendors management with validation
4. Build Items catalog with SKU tracking
5. Create Accounts management
6. Implement Transactions with line items
7. Add Yajra DataTables for all index pages

### Phase 3: Document Upload (Week 3)
1. Create document upload interface with drag-drop
2. Setup file storage (local/S3)
3. Implement document model and migrations
4. Create document preview component
5. Setup Redis and Horizon for queues
6. Build basic document status tracking UI

### Phase 4: OCR Integration (Week 4)
1. Create OCRService with pluggable engines
2. Implement Tesseract driver (local fallback)
3. Add AWS Textract driver (if AWS credentials available)
4. Create OCRDocumentJob queue job
5. Store OCR results in document_ingestions
6. Add OCR status to document UI

### Phase 5: AI Extraction (Week 5)
1. Setup OpenAI/Anthropic API client
2. Create LLMExtractionService with structured prompts
3. Define JSON schema for extraction
4. Implement ExtractDataJob queue job
5. Parse and validate LLM responses
6. Store extracted data in document_lines

### Phase 6: Entity Matching (Week 6)
1. Build EntityMatchingService
2. Implement vendor matching (tax ID, fuzzy name)
3. Create item matching algorithms
4. Add category suggestion logic
5. Calculate confidence scores
6. Create MatchEntitiesJob queue job

### Phase 7: Review Interface (Week 7)
1. Build side-by-side document review UI
2. Create editable form for extracted data
3. Implement field-level confidence indicators
4. Add approve/reject workflow
5. Create posting mechanism to transactions
6. Implement duplicate detection

### Phase 8: Dashboards & Analytics (Week 8)
1. Create dashboard controller
2. Implement KPI calculations
3. Add Chart.js integration
4. Build time-series queries
5. Create category breakdowns
6. Add vendor spend analysis

### Phase 9: Reports & Exports (Week 9)
1. Build report generation service
2. Implement maatwebsite/excel for exports
3. Create PDF reports with dompdf
4. Add report scheduling
5. Build report templates
6. Implement filters and parameters

### Phase 10: API Development (Week 10)
1. Setup Laravel Sanctum
2. Create API controllers
3. Implement resource transformers
4. Add API documentation
5. Create Postman collection
6. Implement rate limiting

### Phase 11: Testing (Week 11)
1. Create model factories
2. Write unit tests for services
3. Create feature tests for workflows
4. Test RBAC enforcement
5. Test document pipeline
6. Performance testing

### Phase 12: Polish & Deploy (Week 12)
1. UI/UX improvements
2. Add loading states and progress bars
3. Implement error handling
4. Create user documentation
5. Setup staging environment
6. Deploy to production

## Key Code Patterns to Follow

### Global Scope for Store Isolation
```php
class StoreScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        if (auth()->check() && !auth()->user()->hasRole(['Admin', 'Super Admin'])) {
            $storeIds = auth()->user()->stores->pluck('id');
            $builder->whereIn('store_id', $storeIds);
        }
    }
}
```

### Document Processing Job
```php
class OCRDocumentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public $tries = 3;
    public $backoff = [60, 180, 300];

    public function handle(OCRService $ocrService)
    {
        $text = $ocrService->extract($this->document);
        $this->document->ingestions()->create([
            'ocr_text' => $text,
            'ocr_engine' => $ocrService->getEngine(),
            'status' => 'completed'
        ]);

        ExtractDataJob::dispatch($this->document);
    }
}
```

### LLM Extraction Prompt
```php
$prompt = "Extract the following from this invoice/receipt:
- Vendor name and tax ID
- Document number and date
- Line items with description, quantity, unit price, tax
- Totals (subtotal, tax, total)

Return as JSON matching this schema:
{schema}

OCR Text:
{text}";
```

## Success Criteria

1. **Functionality**: All 6 user roles can perform their assigned tasks
2. **Accuracy**: Document extraction achieves >90% accuracy on test set
3. **Performance**: Pages load in <2 seconds, documents process in <30 seconds
4. **Security**: Passes security audit, no data leakage between stores
5. **Usability**: Clean, intuitive interface requiring minimal training
6. **Reliability**: 99.9% uptime, graceful error handling

## Environment Setup

```env
# Laravel
APP_NAME="Shop66 Ledger"
APP_ENV=local
APP_DEBUG=true

# Database
DB_CONNECTION=mysql
DB_DATABASE=shop66_ledger

# Redis
REDIS_HOST=127.0.0.1
QUEUE_CONNECTION=redis

# Storage
FILESYSTEM_DISK=local

# OCR
OCR_ENGINE=tesseract

# LLM
LLM_PROVIDER=openai
OPENAI_API_KEY=your-key

# Mail
MAIL_MAILER=smtp
```

## Testing Commands

```bash
# Run tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature

# Generate code coverage
php artisan test --coverage

# Run PHPStan
./vendor/bin/phpstan analyse

# Format code
./vendor/bin/pint
```

## Deployment Checklist

- [ ] Environment variables configured
- [ ] Database migrations run
- [ ] Storage directories created with permissions
- [ ] Queue workers running
- [ ] Horizon configured and running
- [ ] SSL certificate installed
- [ ] Backup system configured
- [ ] Monitoring setup (Sentry, New Relic)
- [ ] Admin user created
- [ ] Test data cleared

## Additional Considerations

1. **Internationalization**: Prepare for multi-language support using Laravel's localization
2. **Timezone Handling**: Store all times in UTC, display in user's timezone
3. **Currency Conversion**: Consider adding real-time exchange rates
4. **Audit Trail**: Log all financial modifications with user, timestamp, and old/new values
5. **Backup Strategy**: Daily automated backups of database and documents
6. **Performance Monitoring**: Implement Laravel Telescope for development debugging

## Common Pitfalls to Avoid

1. Don't forget to scope queries by store_id
2. Always validate arithmetic in financial calculations
3. Use database transactions for financial operations
4. Implement idempotency for webhook handlers
5. Cache expensive queries appropriately
6. Handle file upload failures gracefully
7. Validate file types and sizes before processing
8. Implement proper retry logic for external APIs
9. Use queues for time-consuming operations
10. Test with real-world document samples

---

This prompt provides a complete blueprint for building the Shop66 Ledger application with all technical details, implementation order, and code patterns needed for successful development."