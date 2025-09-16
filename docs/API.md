# Shop66 Ledger - API Documentation

## Overview

The Shop66 Ledger API is a RESTful service built with Laravel 11 that provides comprehensive financial management capabilities for multi-store retail operations.

**Base URL**: `http://your-domain.com/api`
**Authentication**: Bearer Token (Laravel Sanctum)
**Content-Type**: `application/json`

## Authentication

### Login
```http
POST /api/auth/login
Content-Type: application/json

{
  "email": "admin@example.com",
  "password": "password"
}
```

**Response**:
```json
{
  "user": {
    "id": 1,
    "name": "Admin User", 
    "email": "admin@example.com"
  },
  "token": "1|abc123...",
  "expires_at": "2024-02-16T10:00:00.000Z"
}
```

## Dashboard Analytics

### Get Dashboard Metrics
Returns comprehensive analytics for a store including KPIs, charts, and recent activity.

```http
GET /api/stores/{store_id}/dashboard
Authorization: Bearer {token}
```

## Reports

### Financial Summary Report
```http
GET /api/stores/{store_id}/reports/financial-summary?start_date=2024-01-01&end_date=2024-01-31
Authorization: Bearer {token}
```

### Export Reports
```http
POST /api/stores/{store_id}/reports/export
Authorization: Bearer {token}
Content-Type: application/json

{
  "type": "financial_summary",
  "format": "excel",
  "start_date": "2024-01-01",
  "end_date": "2024-01-31"
}
```

See full documentation at: https://github.com/sattip/shop66-ledger