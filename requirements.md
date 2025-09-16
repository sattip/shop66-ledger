# Shop66 Ledger - Requirements Document

## Executive Summary

Shop66 Ledger is a Laravel 11-based financial management application designed to handle multiple retail stores' income and expenses. The system features role-based access control, comprehensive dashboards & analytics, and an AI-powered document ingestion pipeline that scans PDFs (invoices/receipts) and extracts item-level purchases for reconciliation.

## Core Objectives

- **Fast CRUD Operations**: Efficient management of income/expense and sales/purchase documents per store
- **Intelligent Document Processing**: Accurate line-item extraction from scanned PDFs/images via OCR + LLM
- **Role-Based Access Control**: Clear visibility based on user roles (users see own records, managers see their stores, admins see all)
- **Rich Analytics**: Comprehensive totals, trends, categories, vendors, and item analytics
- **Flexible Reporting**: Exportable reports in CSV/XLSX/PDF formats
- **Professional UI**: Clean AdminLTE interface with DataTables, filters, and validation workflows

## System Architecture

### Technology Stack

- **Backend**: Laravel 11, PHP 8.3
- **Database**: MySQL 8 (or PostgreSQL 16)
- **Cache/Queue**: Redis with Laravel Horizon
- **Storage**: S3 (production), local (development)
- **Deployment**: Laravel Forge
- **CDN/Proxy**: Cloudflare

### Key Laravel Packages

- `spatie/laravel-permission` - Role-based access control
- `spatie/laravel-activitylog` - Audit logging
- `yajra/laravel-datatables-oracle` - Server-side DataTables
- `maatwebsite/excel` - Excel/CSV exports
- `barryvdh/laravel-dompdf` or `barryvdh/laravel-snappy` - PDF generation
- `laravel/horizon` - Queue monitoring
- `intervention/image` - Image processing/thumbnails
- `laravel/sanctum` - API authentication

## User Roles & Permissions

### System Roles (Default Seeds)

1. **Super Admin**
   - Full system access
   - All administrative privileges
   - System configuration management

2. **Admin**
   - Manage users, stores, categories, vendors, items
   - View all financial data
   - Approve/reject documents
   - Access to all reports

3. **Store Manager**
   - Read/write access for assigned store(s)
   - Approve documents for their store
   - View store-specific reports
   - Manage store settings

4. **Accountant**
   - Read/write transactions for assigned stores
   - Manage categories and vendors
   - Run and export reports
   - Review and post documents

5. **Staff**
   - Create income/expenses they own
   - View records they created
   - Upload documents for processing
   - Limited report access

6. **Read Only**
   - View-only access for assigned store(s)
   - Access to basic reports
   - No modification privileges

### Visibility Rules

- **Own Records**: Creators can always see their own records
- **Store-Based**: Users assigned to stores see those stores' records
- **Global Access**: Admins and Super Admins see all records

## Database Schema

### Core Entities

#### Users & Authentication
- `users` - System users with authentication
- `roles` - Role definitions (via Spatie)
- `permissions` - Permission definitions
- `model_has_roles` - User-role assignments
- `model_has_permissions` - Direct permission assignments

#### Store Management
- `stores` - Store definitions with currency and tax settings
- `store_user` - Store-user assignments with role hints
- `tax_regions` - Tax configuration per region

#### Financial Entities
- `categories` - Income/expense categories with hierarchy
- `vendors` - Supplier/vendor records
- `customers` - Customer records (optional)
- `items` - Product/service catalog
- `accounts` - Financial accounts (cash, bank, card, wallet)

#### Transactions
- `transactions` - Main transaction records
- `transaction_lines` - Line items for transactions
- `attachments` - File attachments for transactions

#### Document Processing
- `documents` - Uploaded documents (invoices, receipts)
- `document_lines` - Extracted line items from documents
- `document_ingestions` - OCR and extraction audit trail

#### Planning & Reporting
- `budgets` - Budget allocations per store/category
- `audits` - Activity log via Spatie

### Key Indexes

- Store ID on all store-scoped tables
- Date indexes for time-based queries
- Vendor and category foreign keys
- Document number for lookup
- Full-text search on OCR text (MySQL InnoDB FTS)

## Core Workflows

### 1. Manual Transaction Entry

1. User selects store context
2. Chooses transaction type (income/expense)
3. Selects category and optional vendor/customer
4. Adds line items with automatic tax calculation
5. Saves as Draft or Posted (permission-based)
6. System logs audit trail

### 2. Document Ingestion Pipeline

1. **Upload Phase**
   - User uploads PDF/image file
   - Optionally selects store and vendor
   - Document created with status `uploaded`

2. **OCR Phase**
   - Queue job runs OCR engine
   - Text extraction saved to `document_ingestions`
   - Status updated to `processing`

3. **AI Extraction Phase**
   - LLM processes OCR text with structured prompt
   - Extracts vendor, line items, totals
   - Populates `document_lines` with confidence scores
   - Status updated to `extracted`

4. **Matching Phase**
   - System attempts vendor matching (tax ID, name)
   - Suggests item/category mappings
   - Calculates match confidence scores
   - Status updated to `review`

5. **Review Phase**
   - Reviewer validates/corrects extracted data
   - Assigns store if missing
   - Confirms line items and totals
   - Can reject document with reason

6. **Posting Phase**
   - Creates transaction from approved document
   - Links document to transaction
   - Status updated to `posted`
   - Notifications sent

### 3. Reconciliation Workflow

1. Import bank statement (CSV/OFX)
2. System suggests matches based on date/amount/vendor
3. User confirms or manually matches transactions
4. Unmatched items flagged for investigation
5. Reconciliation report generated

## AI/ML Features

### OCR Integration

**Supported Engines**:
- AWS Textract
- Google Cloud Vision
- Azure Computer Vision
- Tesseract (local fallback)

**Features**:
- Plain text extraction
- Positional metadata preservation
- Multi-language support
- Image pre-processing

### LLM Document Processing

**Extraction Schema**:
```json
{
  "vendor": {
    "name": "string",
    "tax_id": "string|null",
    "address": "string|null"
  },
  "document": {
    "number": "string|null",
    "issued_at": "YYYY-MM-DD",
    "due_date": "YYYY-MM-DD|null",
    "currency": "string"
  },
  "totals": {
    "subtotal": "number",
    "tax": "number",
    "discount": "number|null",
    "total": "number"
  },
  "lines": [
    {
      "description": "string",
      "quantity": "number",
      "unit_price": "number",
      "tax_rate": "number|null",
      "discount": "number|null",
      "total": "number"
    }
  ]
}
```

**Post-Processing**:
- Arithmetic validation with tolerance
- VAT/tax detection by region
- Vendor fuzzy matching (trigram similarity)
- Item catalog matching
- Confidence scoring per field
- Duplicate detection via document hashing

## Dashboard & Analytics

### Key Performance Indicators

**Store Level**:
- Daily/Weekly/Monthly revenue
- Expense breakdown by category
- Profit margins and trends
- Top vendors by spend
- Budget vs. actual comparison

**Global Level**:
- Multi-store comparisons
- Aggregate financial metrics
- System-wide trends
- Document processing statistics

### Available Reports

1. **Financial Reports**
   - Income Statement
   - Expense Report
   - Cash Flow Statement
   - Tax Report

2. **Operational Reports**
   - Vendor Ledger
   - Item Usage Report
   - Category Analysis
   - Store Comparison

3. **Document Reports**
   - Processing Status
   - Extraction Accuracy
   - Pending Reviews
   - Rejection Analysis

### Export Formats
- CSV for data analysis
- XLSX with formatting
- PDF for formal reports
- JSON for API integrations

## User Interface

### Design Framework
- AdminLTE 3.2 with Bootstrap 4
- Responsive design for mobile/tablet
- Dark mode support
- Accessibility compliance (WCAG 2.1)

### Key Screens

1. **Dashboard**
   - KPI widgets
   - Charts and graphs
   - Quick actions
   - Recent activity

2. **Stores Management**
   - Store list with DataTables
   - Store settings and configuration
   - User assignments
   - Performance metrics

3. **Transaction Management**
   - Advanced filtering and search
   - Inline editing capabilities
   - Bulk operations
   - Export functions

4. **Document Processing**
   - Upload interface with drag-drop
   - OCR status monitoring
   - Side-by-side review interface
   - Approval workflow

5. **Reports Center**
   - Report templates
   - Custom date ranges
   - Scheduled reports
   - Export queue

## API Specifications

### Authentication
- Laravel Sanctum for token-based auth
- API rate limiting
- Token expiration management

### Core Endpoints

**Authentication**:
- `POST /api/auth/login`
- `POST /api/auth/logout`
- `GET /api/auth/me`
- `POST /api/auth/refresh`

**Stores**:
- `GET /api/stores` - List stores
- `GET /api/stores/{id}` - Get store details
- `POST /api/stores` - Create store
- `PUT /api/stores/{id}` - Update store
- `DELETE /api/stores/{id}` - Delete store

**Transactions**:
- `GET /api/transactions` - List with filters
- `GET /api/transactions/{id}` - Get details
- `POST /api/transactions` - Create
- `PUT /api/transactions/{id}` - Update
- `DELETE /api/transactions/{id}` - Delete

**Documents**:
- `POST /api/documents` - Upload (chunked)
- `GET /api/documents` - List documents
- `GET /api/documents/{id}` - Get details
- `POST /api/documents/{id}/approve` - Approve
- `POST /api/documents/{id}/reject` - Reject

**Reports**:
- `GET /api/reports/summary` - Financial summary
- `GET /api/reports/vendor-spend` - Vendor analysis
- `GET /api/reports/category-breakdown` - Category analysis
- `POST /api/reports/export` - Generate export

## Security & Compliance

### Security Measures

1. **Access Control**
   - Role-based permissions
   - Store-level isolation
   - API rate limiting
   - Session management

2. **Data Protection**
   - Encryption at rest
   - Encryption in transit (TLS)
   - Sensitive field encryption
   - Signed URLs for documents

3. **Upload Security**
   - File type validation
   - Size limits
   - Virus scanning (ClamAV)
   - Quarantine suspicious files

### Compliance Features

1. **GDPR Compliance**
   - Data export functionality
   - Right to deletion
   - Consent management
   - Data retention policies

2. **Audit Trail**
   - All CRUD operations logged
   - User activity tracking
   - Document approval chain
   - Login/logout events

3. **Financial Compliance**
   - Immutable transaction logs
   - Period locking
   - Approval workflows
   - Reconciliation trails

## Performance Requirements

### Response Times
- Page load: < 2 seconds
- API responses: < 500ms
- Report generation: < 10 seconds
- Document OCR: < 30 seconds

### Scalability
- Support 100+ concurrent users
- Handle 10,000+ transactions/day
- Process 1,000+ documents/day
- Store 5+ years of data

### Availability
- 99.9% uptime SLA
- Automated backups every 6 hours
- Point-in-time recovery
- Disaster recovery plan

## Testing Strategy

### Test Coverage

1. **Unit Tests**
   - Model logic
   - Service classes
   - Utility functions
   - Validation rules

2. **Feature Tests**
   - Authentication flows
   - RBAC enforcement
   - Document pipeline
   - API endpoints

3. **Integration Tests**
   - OCR service integration
   - LLM API integration
   - Payment gateways
   - Export functionality

### Test Data
- Factories for all models
- Seeders for demo data
- Sample documents for testing
- Performance test datasets

## Deployment & DevOps

### Environments
- **Development**: Local Docker setup
- **Staging**: Replica of production
- **Production**: High-availability setup

### CI/CD Pipeline
1. Code push triggers tests
2. PHPUnit/Pest test suite
3. PHPStan static analysis
4. Laravel Pint formatting
5. Build and deploy on success

### Monitoring
- Application monitoring (Sentry)
- Performance monitoring (New Relic)
- Uptime monitoring (Pingdom)
- Queue monitoring (Horizon)

## Implementation Milestones

### Milestone 1: Foundation (Weeks 1-2)
- [ ] Project setup and configuration
- [ ] Authentication system
- [ ] RBAC implementation
- [ ] Store management
- [ ] Basic CRUD for categories, vendors, items

### Milestone 2: Transactions (Weeks 3-4)
- [ ] Transaction model and API
- [ ] Transaction UI with DataTables
- [ ] Line items management
- [ ] Account management
- [ ] Basic reporting

### Milestone 3: Document Processing (Weeks 5-6)
- [ ] Document upload system
- [ ] OCR integration
- [ ] Queue setup
- [ ] Basic extraction pipeline
- [ ] Document storage

### Milestone 4: AI Integration (Weeks 7-8)
- [ ] LLM integration
- [ ] Structured extraction
- [ ] Confidence scoring
- [ ] Entity matching
- [ ] Review interface

### Milestone 5: Analytics (Weeks 9-10)
- [ ] Dashboard implementation
- [ ] Chart integrations
- [ ] Report generation
- [ ] Export functionality
- [ ] Performance optimization

### Milestone 6: Polish (Weeks 11-12)
- [ ] UI/UX refinements
- [ ] Performance tuning
- [ ] Security audit
- [ ] Documentation
- [ ] User training materials

## Success Criteria

### Functional Requirements
- ✓ All user roles can perform assigned functions
- ✓ Documents extracted with >90% accuracy
- ✓ Reports match transaction data exactly
- ✓ Exports preserve all filtered data

### Non-Functional Requirements
- ✓ Page loads under 2 seconds
- ✓ System handles 100 concurrent users
- ✓ 99.9% uptime achieved
- ✓ All security scans pass

### Business Requirements
- ✓ Reduces manual data entry by 70%
- ✓ Improves accuracy to 99%
- ✓ Provides real-time financial visibility
- ✓ Enables data-driven decisions

## Future Enhancements

### Phase 2 Features
- Mobile application (React Native)
- Bank feed automation
- Advanced reconciliation AI
- Predictive analytics
- Multi-language support

### Phase 3 Features
- Multi-tenant architecture
- White-label options
- Blockchain audit trail
- Advanced workflow automation
- API marketplace

### Integration Opportunities
- Accounting software (QuickBooks, Xero)
- E-commerce platforms (Shopify, WooCommerce)
- Payment processors (Stripe, PayPal)
- Tax filing services
- Business intelligence tools

## Risk Management

### Technical Risks
- **OCR Accuracy**: Mitigation via multiple engines and manual review
- **LLM Costs**: Caching and batch processing
- **Scalability**: Horizontal scaling and caching strategy
- **Data Loss**: Regular backups and replication

### Business Risks
- **User Adoption**: Comprehensive training and intuitive UI
- **Compliance Changes**: Flexible configuration system
- **Competition**: Continuous feature development
- **Data Breach**: Security-first architecture

## Support & Maintenance

### Documentation
- Technical documentation
- API documentation
- User manuals
- Video tutorials
- FAQ section

### Support Channels
- In-app help system
- Email support
- Knowledge base
- Community forum
- Priority support for enterprise

### Maintenance Schedule
- Weekly security updates
- Monthly feature releases
- Quarterly major updates
- Annual architecture review

## Appendices

### A. Glossary
- **OCR**: Optical Character Recognition
- **LLM**: Large Language Model
- **RBAC**: Role-Based Access Control
- **KPI**: Key Performance Indicator
- **API**: Application Programming Interface

### B. References
- Laravel Documentation
- AdminLTE Documentation
- Spatie Package Documentation
- OCR Service Documentation
- LLM API Documentation

### C. Contact Information
- Project Manager: [PM Email]
- Technical Lead: [Tech Lead Email]
- Support Team: [Support Email]
- Documentation: [Docs URL]

---

*Last Updated: December 2024*
*Version: 1.0*
*Status: In Development*