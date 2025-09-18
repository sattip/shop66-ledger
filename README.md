# Shop66 Ledger - Financial Management System

A comprehensive Laravel 11-based financial management application designed to handle multiple retail stores' income and expenses with AI-powered document processing.

## Features

### ✅ Implemented Features

1. **AdminLTE 3.2 UI Framework**
   - Responsive dashboard with Bootstrap 4
   - Dark/light mode support
   - Professional navigation and sidebar
   - DataTables integration for all listings

2. **Transaction Management**
   - Complete CRUD operations for income/expense transactions
   - Line items support with categories and items
   - Advanced filtering and search with DataTables
   - Bulk operations and export functionality

3. **Document Processing Interface**
   - Drag & drop file upload with progress tracking
   - Support for PDF, JPG, PNG, WEBP formats (max 10MB)
   - Side-by-side document review interface
   - Confidence scoring and field validation
   - OCR text extraction display

4. **Dashboard Analytics**
   - KPI widgets (transactions, income, expenses, pending documents)
   - Monthly income vs expenses chart (Chart.js)
   - Category breakdown pie chart
   - Recent transactions and pending documents tables

5. **Multi-Store Architecture**
   - Store selector in navigation
   - Store-scoped data isolation
   - User role-based access control

### 🔄 Partially Implemented

1. **Document Processing Pipeline**
   - Upload and storage infrastructure ✅
   - OCR integration (Tesseract/AWS Textract) 🔄
   - LLM extraction (OpenAI/Anthropic) 🔄
   - Entity matching algorithms 🔄
   - Approval workflow ✅

2. **RBAC (Role-Based Access Control)**
   - 6 user roles defined 🔄
   - Permission-based navigation ✅
   - Store-scoped visibility 🔄

### 📋 TODO (Not Yet Implemented)

1. **Authentication System**
   - Laravel Breeze/Jetstream integration
   - User registration and login
   - Password reset functionality

2. **Master Data Management**
   - Vendors CRUD interface
   - Customers CRUD interface
   - Items catalog with SKU tracking
   - Categories with hierarchy
   - Accounts management

3. **Reporting System**
   - Income Statement generation
   - Expense Report with filters
   - Vendor Ledger
   - Export to CSV/XLSX/PDF

4. **API Development**
   - Laravel Sanctum authentication
   - RESTful API endpoints
   - Resource transformers
   - Rate limiting

5. **Testing Infrastructure**
   - Model factories
   - Unit tests for services
   - Feature tests for workflows
   - Integration tests

## Requirements

- PHP 8.2+
- MySQL 8.0+ or PostgreSQL 16+
- Node.js 18+ (for asset compilation)
- Redis (for caching and queues)

## Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd shop66-ledger/shop66-ledger-app
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node.js dependencies**
   ```bash
   npm install
   ```

4. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configure database**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=shop66_ledger
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

6. **Run migrations**
   ```bash
   php artisan migrate
   ```

7. **Seed the database**
   ```bash
   php artisan db:seed
   ```

8. **Build assets**
   ```bash
   npm run dev
   ```

9. **Start the development server**
   ```bash
   php artisan serve
   ```

## Configuration

### Storage Setup
```env
FILESYSTEM_DISK=local
# For production:
# FILESYSTEM_DISK=s3
# AWS_ACCESS_KEY_ID=your_key
# AWS_SECRET_ACCESS_KEY=your_secret
# AWS_DEFAULT_REGION=us-east-1
# AWS_BUCKET=your_bucket
```

### Queue Configuration
```env
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### OCR & AI Services
```env
# OCR Engine
OCR_ENGINE=tesseract
# or AWS_TEXTRACT_REGION=us-east-1

# LLM Provider
LLM_PROVIDER=openai
OPENAI_API_KEY=your_openai_key
# or ANTHROPIC_API_KEY=your_anthropic_key
```

## Development Workflow

### Running the Application
```bash
# Start all services
composer run dev

# Or individually:
php artisan serve          # Web server
php artisan queue:work     # Queue worker
php artisan horizon        # Queue monitoring
npm run dev               # Asset watching
```

### Testing
```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit

# Code coverage
php artisan test --coverage
```

### Code Quality
```bash
# Run PHP CS Fixer
./vendor/bin/pint

# Run PHPStan
./vendor/bin/phpstan analyse

# Run all quality checks
composer run check
```

## API Documentation

The API documentation is available at `/api/documentation` when the application is running.

Key endpoints:
- `GET /api/transactions` - List transactions
- `POST /api/transactions` - Create transaction
- `GET /api/documents` - List documents
- `POST /api/documents` - Upload documents
- `POST /api/documents/{id}/approve` - Approve document

## Architecture

### Directory Structure
```
app/
├── Http/Controllers/     # Web and API controllers
├── Models/              # Eloquent models
├── Services/            # Business logic services
├── Jobs/                # Queue jobs
├── Enums/               # Enumeration classes
└── Support/             # Helper classes

resources/views/
├── layouts/             # Layout templates
├── dashboard.blade.php  # Main dashboard
├── transactions/        # Transaction views
├── documents/           # Document views
└── placeholder.blade.php # Placeholder for unimplemented features
```

### Key Services
- `DocumentProcessingPipeline` - Handles document OCR and AI extraction
- `OCRService` - Pluggable OCR engine abstraction
- `LLMExtractionService` - AI-powered data extraction
- `EntityMatchingService` - Fuzzy matching for vendors/items
- `ReportingService` - Financial report generation

## Demo Features

The current implementation includes:

1. **Dashboard** - `/dashboard`
   - KPI widgets with sample data
   - Interactive charts
   - Recent transactions table
   - Pending documents queue

2. **Transactions** - `/transactions`
   - Advanced DataTables with server-side processing
   - Create/edit forms with line items
   - Filtering and search capabilities

3. **Document Upload** - `/documents/upload`
   - Drag & drop interface
   - File validation and preview
   - Progress tracking
   - Auto-processing option

4. **Document Review** - `/documents/review/{id}`
   - Side-by-side document and form view
   - Confidence indicators for extracted data
   - Field validation and suggestions
   - Approve/reject workflow

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Run tests and quality checks
5. Submit a pull request

## License

This project is licensed under the MIT License.

## Support

For support and questions, please contact the development team or create an issue in the repository.