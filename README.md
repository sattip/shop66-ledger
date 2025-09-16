# Shop66 Ledger - Financial Management System

A comprehensive Laravel 11-based financial management application designed for multi-store retail operations with AI-powered document processing and analytics.

## 🚀 Features

### Core Functionality
- **Multi-Store Management**: Handle multiple retail locations with isolated data
- **Role-Based Access Control**: Secure access with 6 different user roles
- **Document Processing**: AI-powered OCR and data extraction from invoices/receipts
- **Financial Analytics**: Real-time dashboards with KPIs and trend analysis
- **Report Generation**: Export financial reports in Excel/PDF formats
- **API-First Design**: Complete RESTful API with 90+ endpoints

### AI-Powered Document Processing
- **OCR Integration**: Extract text from PDF/image documents
- **Intelligent Parsing**: Structured data extraction with confidence scoring
- **Entity Matching**: Automatic vendor/item recognition and categorization
- **Review Workflow**: Human-in-the-loop validation for accuracy

### Analytics & Reporting
- **Real-time Dashboard**: Transaction trends, category breakdowns, vendor analysis
- **Financial Reports**: Income statements, expense reports, vendor ledgers
- **Export Options**: Excel (.xlsx), PDF, and CSV formats
- **Custom Date Ranges**: Flexible reporting periods with comparisons

## 📋 Requirements

- PHP 8.3+
- Laravel 11
- MySQL 8.0+ or PostgreSQL 16+
- Redis (for queues and caching)
- Composer
- Node.js & NPM (for assets)

## 🛠️ Installation

### 1. Clone the Repository
```bash
git clone https://github.com/sattip/shop66-ledger.git
cd shop66-ledger/shop66-ledger-app
```

### 2. Install Dependencies
```bash
composer install
npm install
```

### 3. Environment Setup
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Configure Environment
Edit `.env` file with your database and other settings:
```env
# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=shop66_ledger
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Queue
QUEUE_CONNECTION=redis

# File Storage
FILESYSTEM_DISK=local

# Mail
MAIL_MAILER=smtp
MAIL_HOST=your_smtp_host
MAIL_PORT=587
MAIL_USERNAME=your_email
MAIL_PASSWORD=your_password

# Optional: AI Services
OPENAI_API_KEY=your_openai_key
GOOGLE_VISION_KEY_FILE=path/to/service-account.json
AWS_ACCESS_KEY_ID=your_aws_key
AWS_SECRET_ACCESS_KEY=your_aws_secret
```

### 5. Database Setup
```bash
php artisan migrate
php artisan db:seed
```

### 6. Build Assets
```bash
npm run build
```

### 7. Start Services
```bash
# Start the application
php artisan serve

# Start queue workers (in separate terminal)
php artisan queue:work

# Start Horizon for queue monitoring (optional)
php artisan horizon
```

## 📖 API Documentation

### Authentication
All API endpoints require authentication via Laravel Sanctum tokens.

```bash
# Login
POST /api/auth/login
{
  "email": "user@example.com",
  "password": "password"
}

# Get user info
GET /api/auth/me
Authorization: Bearer {token}
```

### Core Endpoints

#### Stores
```bash
GET    /api/stores                     # List stores
POST   /api/stores                     # Create store
GET    /api/stores/{store}             # Get store details
PUT    /api/stores/{store}             # Update store
DELETE /api/stores/{store}             # Delete store
```

#### Dashboard
```bash
GET /api/stores/{store}/dashboard
```
Returns comprehensive metrics including:
- Transaction counts and amounts (current vs previous month)
- Document processing statistics
- Active vendor counts
- Transaction trend charts (last 30 days)
- Category breakdown
- Top vendors by spend
- Recent activity feed

#### Reports
```bash
# Financial Summary
GET /api/stores/{store}/reports/financial-summary?start_date=2024-01-01&end_date=2024-01-31

# Vendor Analysis
GET /api/stores/{store}/reports/vendor?start_date=2024-01-01&end_date=2024-01-31

# Category Breakdown
GET /api/stores/{store}/reports/category?start_date=2024-01-01&end_date=2024-01-31

# Export Reports
POST /api/stores/{store}/reports/export
{
  "type": "financial_summary|vendor|category",
  "format": "excel|pdf",
  "start_date": "2024-01-01",
  "end_date": "2024-01-31"
}

# Download Generated Reports
GET /api/stores/{store}/reports/download/{filename}
```

#### Transactions
```bash
GET    /api/stores/{store}/transactions
POST   /api/stores/{store}/transactions
GET    /api/stores/{store}/transactions/{transaction}
PUT    /api/stores/{store}/transactions/{transaction}
DELETE /api/stores/{store}/transactions/{transaction}
```

#### Documents
```bash
GET    /api/stores/{store}/documents
POST   /api/stores/{store}/documents        # Upload document
GET    /api/stores/{store}/documents/{document}
PUT    /api/stores/{store}/documents/{document}
DELETE /api/stores/{store}/documents/{document}

# Document processing
GET    /api/stores/{store}/documents/{document}/ingestions
POST   /api/stores/{store}/documents/{document}/ingestions
```

#### Other Resources
- Categories: `/api/stores/{store}/categories`
- Vendors: `/api/stores/{store}/vendors`
- Customers: `/api/stores/{store}/customers`
- Items: `/api/stores/{store}/items`
- Accounts: `/api/stores/{store}/accounts`
- Budgets: `/api/stores/{store}/budgets`

## 🧪 Testing

### Run Tests
```bash
# All tests
./vendor/bin/phpunit

# Specific test
./vendor/bin/phpunit --filter=DashboardControllerTest

# With coverage
./vendor/bin/phpunit --coverage-html coverage
```

### Generate Test Data
```bash
# Create sample data using factories
php artisan tinker

# In tinker console:
$store = Store::factory()->create();
$categories = Category::factory(10)->create(['store_id' => $store->id]);
$vendors = Vendor::factory(20)->create(['store_id' => $store->id]);
```

## 🏗️ Architecture

### Service Layer
- **DocumentProcessingPipeline**: Orchestrates OCR, extraction, and matching
- **ReportsService**: Handles report generation and data aggregation
- **OcrManager**: Manages OCR engine selection and processing
- **ExtractionService**: Parses documents and extracts structured data
- **MatchingService**: Matches extracted data to existing entities

### Queue System
- Document processing is handled asynchronously via Redis queues
- Laravel Horizon provides queue monitoring and management
- Automatic retry logic with exponential backoff

### Database Design
- Store-isolated data with proper foreign key relationships
- Audit trail using Spatie ActivityLog
- Soft deletes for data integrity
- Optimized indexes for performance

## 🔧 Configuration

### OCR Engines
Configure multiple OCR engines in your `.env`:
```env
OCR_ENGINE=tesseract
# or
OCR_ENGINE=textract
# or  
OCR_ENGINE=vision
```

### LLM Integration
For advanced AI extraction:
```env
LLM_PROVIDER=openai
OPENAI_API_KEY=your_key
OPENAI_MODEL=gpt-4-turbo
```

### Storage
```env
FILESYSTEM_DISK=local
# or for production:
FILESYSTEM_DISK=s3
AWS_BUCKET=your-bucket
```

## 📊 Performance

### Optimization Tips
1. **Database**: Use proper indexes and query optimization
2. **Caching**: Redis for session and cache storage
3. **Queues**: Process documents asynchronously
4. **Assets**: Use CDN for static assets in production

### Monitoring
- Laravel Horizon for queue monitoring
- Laravel Telescope for debugging (development)
- Application metrics via dashboard analytics

## 🔒 Security

### Best Practices
- All endpoints require authentication
- Store-level data isolation
- Input validation and sanitization
- Secure file upload handling
- Rate limiting on API endpoints

### Role Permissions
1. **Super Admin**: Full system access
2. **Admin**: Store management and user administration
3. **Manager**: Store operations and reporting
4. **Accountant**: Financial data and reports
5. **Clerk**: Basic data entry
6. **Viewer**: Read-only access

## 🚀 Deployment

### Production Setup
1. Configure web server (Nginx/Apache)
2. Set up SSL certificates
3. Configure production database
4. Set up Redis for queues/cache
5. Configure file storage (S3/local)
6. Set up queue workers as system services
7. Configure monitoring and logging

### Docker Support
```bash
# Using Laravel Sail
./vendor/bin/sail up -d
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests for new functionality
5. Submit a pull request

## 📄 License

This project is licensed under the [MIT License](LICENSE).

## 🆘 Support

For support and questions:
- Create an issue on GitHub
- Check the documentation in `/docs`
- Review the API endpoints above

## 🗺️ Roadmap

### Upcoming Features
- Web dashboard interface with AdminLTE
- Advanced AI/LLM integration
- Multi-language support
- Mobile API extensions
- Webhook integrations
- Advanced reporting with charts

### Current Status
✅ Core API functionality
✅ Document processing pipeline  
✅ Dashboard analytics
✅ Report generation
🟡 AI/LLM integration (basic)
⏳ Web interface
⏳ Comprehensive testing
⏳ Advanced OCR engines