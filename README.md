# YBB Multi-Tenant CMS - Complete Documentation

## Table of Contents
1. [Project Overview](#project-overview)
2. [Architecture & Features](#architecture--features)
3. [Installation & Setup](#installation--setup)
4. [Configuration](#configuration)
5. [User Guide](#user-guide)
6. [API Documentation](#api-documentation)
7. [Development Guide](#development-guide)
8. [Testing](#testing)
9. [Deployment](#deployment)
10. [Troubleshooting](#troubleshooting)

## Project Overview

### Description
YBB Multi-Tenant CMS is a comprehensive Laravel 11-based content management system designed specifically for organizations like Youth Beyond Borders. It provides a robust platform for managing opportunities (scholarships, internships, fellowships), job listings, content publishing, and advertising across multiple tenant organizations.

### Key Features
- **Multi-Tenancy**: Complete tenant isolation with domain-based resolution
- **Content Management**: Full CRUD operations for programs, jobs, posts, and pages
- **User Management**: Role-based access control (Network Admin, Tenant Admin, User)
- **Advertisement System**: Comprehensive ad management with placement optimization
- **Search Functionality**: Advanced search with autocomplete and filtering
- **API Integration**: RESTful APIs for external integrations
- **Network Administration**: Centralized management for all tenants
- **Responsive Design**: Mobile-first, Bootstrap 5-based interface
- **SEO Optimization**: Meta tags, structured data, and search-friendly URLs

### Technology Stack
- **Backend**: Laravel 11, PHP 8.2+
- **Database**: MySQL 8.0+
- **Frontend**: Blade Templates, Bootstrap 5, JavaScript
- **Multi-Tenancy**: Stancl/Tenancy Package
- **Authentication**: Laravel Breeze
- **Testing**: PHPUnit, Feature & Unit Tests
- **Deployment**: Docker-ready, CI/CD compatible

---

## Architecture & Features

### Multi-Tenancy Architecture

The system uses domain-based tenant resolution where each organization gets its own subdomain:
- `tenant1.ybb-cms.com` → Tenant 1's site
- `tenant2.ybb-cms.com` → Tenant 2's site
- `admin.ybb-cms.com` → Network admin interface

#### Database Structure
- **Central Database**: Stores tenant information, network admin data
- **Tenant Databases**: Individual databases for each tenant's content
- **Automatic Switching**: Middleware handles tenant context switching

### Core Models

#### Tenant Model
```php
// Central database
- id, name, slug, domain, description, status
- settings (JSON), created_at, updated_at
```

#### User Model
```php
// Tenant database
- id, name, email, password, role (user/admin)
- email_verified_at, created_at, updated_at
```

#### Program Model
```php
// Tenant database  
- id, title, slug, description, type, status
- organization, location, deadline, requirements
- featured, banner_image, created_by, timestamps
```

#### Job Model
```php
// Tenant database
- id, title, slug, description, type, status
- company, location, salary_min, salary_max
- remote, featured, created_by, timestamps
```

#### Post Model
```php
// Tenant database
- id, title, slug, content, excerpt, status
- category, tags, author, featured_image
- published_at, created_by, timestamps
```

#### Ad Model
```php
// Tenant database
- id, title, placement, status, priority
- content (JSON), start_date, end_date
- analytics (JSON), created_by, timestamps
```

### User Roles & Permissions

#### Network Administrator
- Manages all tenants across the system
- Creates and configures new tenant organizations
- Views system-wide analytics and metrics
- Handles billing and subscription management

#### Tenant Administrator  
- Full access within their tenant's scope
- Manages content (programs, jobs, posts)
- Handles user management for their organization
- Configures tenant-specific settings and ads

#### Regular User
- Views public content
- Can register and login
- Limited to public-facing features

---

## Installation & Setup

### Prerequisites
- PHP 8.2 or higher
- Composer 2.x
- MySQL 8.0 or higher
- Node.js 18+ (for asset compilation)
- Web server (Apache/Nginx)

### Step 1: Clone and Install
```bash
# Clone the repository
git clone https://github.com/your-org/ybb-multi-tenant-cms.git
cd ybb-multi-tenant-cms

# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install

# Copy environment file
cp .env.example .env
```

### Step 2: Environment Configuration
```bash
# Generate application key
php artisan key:generate

# Configure database in .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ybb_central
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Configure tenant database
TENANCY_DATABASE_PREFIX=tenant_
TENANCY_DATABASE_SUFFIX=_db
```

### Step 3: Database Setup
```bash
# Run central database migrations
php artisan migrate

# Install tenancy
php artisan tenancy:install

# Seed initial data (optional)
php artisan db:seed
```

### Step 4: Create First Tenant
```bash
# Create tenant via artisan command
php artisan tenant:create example "Example Organization" example.localhost
```

### Step 5: Configure Web Server

#### Apache Configuration
```apache
<VirtualHost *:80>
    ServerName ybb-cms.local
    ServerAlias *.ybb-cms.local
    DocumentRoot /path/to/ybb-multi-tenant-cms/public
    
    <Directory /path/to/ybb-multi-tenant-cms/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

#### Nginx Configuration
```nginx
server {
    listen 80;
    server_name ybb-cms.local *.ybb-cms.local;
    root /path/to/ybb-multi-tenant-cms/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

---

## Configuration

### Environment Variables

#### Core Settings
```bash
APP_NAME="YBB Multi-Tenant CMS"
APP_ENV=production
APP_KEY=base64:your-key-here
APP_DEBUG=false
APP_URL=https://your-domain.com

# Tenant Configuration
TENANCY_DATABASE_AUTO_DELETE=false
TENANCY_CACHE_STORE=redis
TENANCY_FILESYSTEM_DISK=tenant
```

#### Mail Configuration
```bash
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@your-domain.com
```

#### Cache & Queue
```bash
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### Tenant Settings

Each tenant can configure:
- Organization details (name, description, logo)
- Theme customization (colors, branding)
- Feature toggles (enable/disable modules)
- Email templates and notifications
- SEO settings and meta data

---

## User Guide

### Network Administration

#### Accessing Network Admin
1. Navigate to `https://admin.your-domain.com`
2. Login with network administrator credentials
3. Access the network dashboard

#### Creating New Tenants
1. Go to "Tenants" → "Create New"
2. Fill in organization details:
   - Name: Organization display name
   - Slug: URL-friendly identifier
   - Domain: Subdomain (e.g., `example` for `example.your-domain.com`)
   - Description: Brief description
3. Configure initial settings
4. Click "Create Tenant"

#### Managing Tenants
- **View All Tenants**: List with status, user count, content metrics
- **Edit Tenant**: Update organization details and settings
- **Suspend/Activate**: Control tenant access
- **Delete Tenant**: Permanently remove (with confirmation)
- **Analytics**: View usage statistics and metrics

### Tenant Administration

#### Dashboard Overview
- Content statistics (programs, jobs, posts)
- Recent activity and user engagement
- Quick actions for common tasks
- System notifications and updates

#### Managing Programs/Opportunities
1. **Create Program**:
   - Navigate to "Programs" → "Create New"
   - Fill in details: title, description, type, organization
   - Set deadline, requirements, location
   - Upload banner image (optional)
   - Choose status: Draft, Published, Archived

2. **Edit Programs**:
   - Click program title or "Edit" button
   - Modify any field and save changes
   - Preview public view before publishing

3. **Program Types**:
   - Scholarship: Financial aid opportunities
   - Internship: Work experience programs
   - Fellowship: Advanced career programs
   - Conference: Event opportunities
   - Competition: Contests and challenges

#### Managing Job Listings
1. **Create Job**:
   - Go to "Jobs" → "Create New"
   - Enter job title, description, requirements
   - Set company, location, salary range
   - Mark as remote if applicable
   - Choose job type: Full-time, Part-time, Contract

2. **Job Management**:
   - Featured jobs appear on homepage
   - Set application deadlines
   - Track application metrics
   - Export applicant data

#### Content Management (Posts)
1. **Creating Posts**:
   - Navigate to "Posts" → "Create New"
   - Write content using rich text editor
   - Add featured image and excerpt
   - Assign categories and tags
   - Schedule publishing or publish immediately

2. **Post Categories**:
   - Career Advice
   - Industry News
   - Success Stories
   - Guides & Resources
   - Company Spotlights

#### Advertisement Management
1. **Creating Ads**:
   - Go to "Advertisements" → "Create New"
   - Choose placement: Header, Sidebar, Footer, Content
   - Set priority (higher numbers display first)
   - Configure start/end dates
   - Add HTML content or image

2. **Ad Placements**:
   - **Header**: Top of page, high visibility
   - **Sidebar**: Next to content, good engagement
   - **Footer**: Bottom of page, less intrusive
   - **Content**: Within article content

3. **Analytics**:
   - View impressions and click-through rates
   - Track revenue and conversion metrics
   - Export performance reports

### User Experience (Public Site)

#### Homepage Features
- Hero section with search functionality
- Featured opportunities and job listings
- Recent blog posts and articles
- Statistics and engagement metrics
- Newsletter signup and social links

#### Search Functionality
- Global search across all content types
- Auto-complete suggestions
- Advanced filtering options
- Type-specific search (programs, jobs, posts)
- Related searches and popular terms

#### Content Discovery
- Browse opportunities by category/type
- Filter jobs by location, salary, remote work
- Read career advice and industry articles
- Subscribe to updates and notifications

---

## API Documentation

### Authentication
Most API endpoints require authentication using Laravel Sanctum tokens.

```bash
# Get authentication token
POST /api/auth/login
{
    "email": "user@example.com",
    "password": "password"
}

# Use token in subsequent requests
Authorization: Bearer your-token-here
```

### Programs API

#### List Programs
```bash
GET /api/programs
Parameters:
- page: Page number (default: 1)
- per_page: Items per page (default: 15, max: 50)
- type: Program type (scholarship, internship, fellowship)
- status: published, draft, archived
- featured: true/false
- search: Search term
- organization: Filter by organization
- location: Filter by location

Response:
{
    "data": [
        {
            "id": 1,
            "title": "Engineering Scholarship",
            "slug": "engineering-scholarship",
            "description": "Full scholarship for engineering students...",
            "type": "scholarship",
            "organization": "Tech University",
            "location": "San Francisco, CA",
            "deadline": "2024-12-31",
            "featured": true,
            "banner_image": "/storage/programs/banner.jpg",
            "created_at": "2024-01-15T10:30:00Z"
        }
    ],
    "meta": {
        "current_page": 1,
        "total": 25,
        "per_page": 15
    }
}
```

#### Get Single Program
```bash
GET /api/programs/{id}
GET /api/programs/slug/{slug}

Response:
{
    "data": {
        "id": 1,
        "title": "Engineering Scholarship",
        "slug": "engineering-scholarship",
        "description": "Detailed description...",
        "requirements": "Requirements list...",
        "type": "scholarship",
        "organization": "Tech University",
        "location": "San Francisco, CA",
        "deadline": "2024-12-31",
        "application_url": "https://university.com/apply",
        "featured": true,
        "banner_image": "/storage/programs/banner.jpg",
        "creator": {
            "id": 1,
            "name": "Admin User"
        },
        "created_at": "2024-01-15T10:30:00Z",
        "updated_at": "2024-01-20T14:20:00Z"
    }
}
```

### Jobs API

#### List Jobs
```bash
GET /api/jobs
Parameters:
- page, per_page: Pagination
- type: full-time, part-time, contract, internship
- remote: true/false
- company: Filter by company name
- location: Filter by location
- salary_min, salary_max: Salary range
- search: Search term

Response:
{
    "data": [
        {
            "id": 1,
            "title": "Senior Software Engineer",
            "slug": "senior-software-engineer",
            "description": "Join our development team...",
            "type": "full-time",
            "company": "Tech Corp",
            "location": "Remote",
            "salary_min": 100000,
            "salary_max": 150000,
            "remote": true,
            "featured": true,
            "created_at": "2024-01-15T10:30:00Z"
        }
    ]
}
```

#### Get Single Job
```bash
GET /api/jobs/{id}
GET /api/jobs/slug/{slug}

Response:
{
    "data": {
        "id": 1,
        "title": "Senior Software Engineer",
        "slug": "senior-software-engineer",
        "description": "Detailed job description...",
        "requirements": "Required qualifications...",
        "type": "full-time",
        "company": "Tech Corp",
        "industry": "Technology",
        "location": "Remote",
        "salary_min": 100000,
        "salary_max": 150000,
        "remote": true,
        "application_url": "https://company.com/apply",
        "featured": true,
        "creator": {
            "id": 1,
            "name": "HR Manager"
        },
        "created_at": "2024-01-15T10:30:00Z"
    }
}
```

### Search API

#### Search All Content
```bash
GET /api/search
Parameters:
- q: Search query (required)
- type: all, programs, jobs, posts
- sort: relevance, date, title
- page, per_page: Pagination

Response:
{
    "query": "engineering",
    "total_results": 45,
    "data": {
        "programs": [
            {
                "id": 1,
                "title": "Engineering Scholarship",
                "type": "program",
                "relevance_score": 0.95
            }
        ],
        "jobs": [
            {
                "id": 1,
                "title": "Software Engineer",
                "type": "job",
                "relevance_score": 0.87
            }
        ],
        "posts": [
            {
                "id": 1,
                "title": "Engineering Career Guide",
                "type": "post",
                "relevance_score": 0.76
            }
        ]
    }
}
```

### Error Handling

API returns standard HTTP status codes:
- 200: Success
- 201: Created
- 400: Bad Request
- 401: Unauthorized
- 403: Forbidden
- 404: Not Found
- 422: Validation Error
- 500: Server Error

Error response format:
```json
{
    "error": {
        "message": "Validation failed",
        "code": "VALIDATION_ERROR",
        "details": {
            "title": ["The title field is required"],
            "email": ["The email format is invalid"]
        }
    }
}
```

---

## Development Guide

### Project Structure
```
ybb-multi-tenant-cms/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/           # Tenant admin controllers
│   │   │   ├── Api/             # API controllers
│   │   │   ├── Network/         # Network admin controllers
│   │   │   └── *.php            # Public controllers
│   │   ├── Middleware/          # Custom middleware
│   │   └── Requests/            # Form request validation
│   ├── Models/                  # Eloquent models
│   ├── Services/                # Business logic services
│   └── Traits/                  # Reusable traits
├── config/
│   ├── tenancy.php             # Multi-tenancy configuration
│   └── *.php                   # Other config files
├── database/
│   ├── migrations/
│   │   ├── landlord/           # Central database migrations
│   │   └── tenant/             # Tenant database migrations
│   └── seeders/                # Database seeders
├── resources/
│   ├── views/
│   │   ├── admin/              # Admin panel views
│   │   ├── network/            # Network admin views
│   │   ├── layouts/            # Layout templates
│   │   └── *.blade.php         # Public views
│   └── js/                     # Frontend JavaScript
├── routes/
│   ├── web.php                 # Web routes
│   ├── api.php                 # API routes
│   └── tenant.php              # Tenant-specific routes
└── tests/
    ├── Feature/                # Feature tests
    └── Unit/                   # Unit tests
```

### Coding Standards

#### Laravel Best Practices
- Follow PSR-12 coding standards
- Use Laravel conventions for naming
- Implement proper validation in Form Requests
- Use Eloquent relationships appropriately
- Implement proper error handling

#### Multi-Tenancy Considerations
```php
// Always use tenant-aware models
$programs = Program::all(); // ✅ Automatically scoped to current tenant
$programs = DB::table('programs')->get(); // ❌ Not tenant-aware

// For central operations, use landlord connection
$tenants = Tenant::all(); // ✅ Uses landlord connection
```

#### Security Best Practices
- Validate all user inputs
- Use CSRF protection for forms
- Implement proper authorization checks
- Sanitize data before database operations
- Use HTTPS in production

### Adding New Features

#### Creating a New Content Type
1. **Create Migration**:
```bash
php artisan make:migration create_resources_table --tenant
```

2. **Create Model**:
```php
// app/Models/Resource.php
class Resource extends Model
{
    use HasFactory, TenantAware;
    
    protected $fillable = [
        'title', 'description', 'type', 'status', 'created_by'
    ];
    
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
```

3. **Create Controller**:
```php
// app/Http/Controllers/Admin/ResourceController.php
class ResourceController extends Controller
{
    public function index()
    {
        $resources = Resource::paginate(15);
        return view('admin.resources.index', compact('resources'));
    }
}
```

4. **Add Routes**:
```php
// routes/web.php
Route::resource('admin/resources', ResourceController::class);
```

### Database Operations

#### Creating Migrations
```bash
# Central database migration
php artisan make:migration create_settings_table

# Tenant database migration  
php artisan make:migration create_resources_table --tenant
```

#### Running Migrations
```bash
# Run central migrations
php artisan migrate

# Run tenant migrations for all tenants
php artisan tenants:migrate

# Run for specific tenant
php artisan tenants:migrate --tenants=1,2,3
```

### Extending the Admin Panel

#### Adding New Admin Sections
1. Create controller in `app/Http/Controllers/Admin/`
2. Create views in `resources/views/admin/`
3. Add routes to admin group in `routes/web.php`
4. Update navigation in admin layout

#### Custom Form Validation
```php
// app/Http/Requests/StoreProgramRequest.php
class StoreProgramRequest extends FormRequest
{
    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:scholarship,internship,fellowship',
            'deadline' => 'nullable|date|after:today',
        ];
    }
}
```

---

## Testing

### Test Structure
The application includes comprehensive test coverage:

#### Feature Tests
- Public page functionality
- Admin panel operations
- API endpoint testing
- Authentication flows
- Multi-tenancy isolation

#### Unit Tests
- Model relationships and scopes
- Business logic validation
- Helper functions
- Service classes

### Running Tests

#### Full Test Suite
```bash
# Run all tests
php artisan test

# Run with coverage
php artisan test --coverage

# Run specific test file
php artisan test tests/Feature/AdminPanelTest.php
```

#### Testing Multi-Tenancy
```php
// Example test with tenant isolation
public function test_tenant_data_isolation()
{
    // Create and switch to tenant 1
    $tenant1 = Tenant::create(['name' => 'Tenant 1']);
    tenancy()->initialize($tenant1);
    
    $program1 = Program::create(['title' => 'Tenant 1 Program']);
    
    // Switch to tenant 2
    $tenant2 = Tenant::create(['name' => 'Tenant 2']);
    tenancy()->initialize($tenant2);
    
    // Verify isolation
    $this->assertEquals(0, Program::count());
}
```

### Test Database Setup
```bash
# Configure test database in phpunit.xml
<env name="DB_DATABASE" value="ybb_testing"/>

# Run migrations for testing
php artisan migrate --env=testing
```

---

## Deployment

### Production Requirements
- PHP 8.2+ with required extensions
- MySQL 8.0+ or PostgreSQL 13+
- Redis for caching and sessions
- SSL certificate for HTTPS
- Process manager (Supervisor) for queues

### Docker Deployment

#### Dockerfile
```dockerfile
FROM php:8.2-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev zip unzip

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy application files
COPY . .

# Install dependencies
RUN composer install --optimize-autoloader --no-dev

# Set permissions
RUN chown -R www-data:www-data /var/www
RUN chmod -R 755 /var/www/storage

EXPOSE 9000
CMD ["php-fpm"]
```

#### Docker Compose
```yaml
version: '3.8'
services:
  app:
    build: .
    volumes:
      - .:/var/www
    depends_on:
      - database
      - redis
    
  nginx:
    image: nginx:alpine
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - .:/var/www
      - ./docker/nginx.conf:/etc/nginx/conf.d/default.conf
    depends_on:
      - app
      
  database:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: ybb_central
    volumes:
      - mysql_data:/var/lib/mysql
      
  redis:
    image: redis:alpine
    
volumes:
  mysql_data:
```

### CI/CD Pipeline

#### GitHub Actions Example
```yaml
name: Deploy to Production
on:
  push:
    branches: [main]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: 8.2
      - name: Install dependencies
        run: composer install
      - name: Run tests
        run: php artisan test

  deploy:
    needs: test
    runs-on: ubuntu-latest
    steps:
      - name: Deploy to server
        run: |
          ssh user@server 'cd /var/www && git pull'
          ssh user@server 'cd /var/www && composer install --no-dev'
          ssh user@server 'cd /var/www && php artisan migrate --force'
          ssh user@server 'cd /var/www && php artisan config:cache'
```

### Performance Optimization

#### Caching Strategy
```bash
# Enable configuration caching
php artisan config:cache

# Enable route caching
php artisan route:cache

# Enable view caching
php artisan view:cache

# Configure Redis for sessions and cache
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

#### Database Optimization
- Index frequently queried columns
- Use database query optimization
- Implement proper eager loading
- Set up read replicas for high traffic

### Monitoring & Logging

#### Application Monitoring
- Configure Laravel Telescope for development
- Set up error tracking (Sentry, Bugsnag)
- Implement performance monitoring
- Monitor queue jobs and failures

#### Log Management
```php
// config/logging.php
'channels' => [
    'tenant' => [
        'driver' => 'daily',
        'path' => storage_path('logs/tenant.log'),
        'level' => 'debug',
        'days' => 14,
    ],
]
```

---

## Troubleshooting

### Common Issues

#### Multi-Tenancy Problems

**Issue: Tenant not found/switching**
```bash
# Check tenant configuration
php artisan tenants:list

# Verify domain configuration
php artisan tinker
> Tenant::where('domain', 'example.localhost')->first()

# Clear tenant cache
php artisan cache:clear
```

**Issue: Database connection errors**
```bash
# Check tenant database exists
php artisan tenants:migrate-fresh --seed

# Verify database configuration
php artisan config:show database
```

#### Performance Issues

**Issue: Slow page loading**
```bash
# Enable query logging
DB::enableQueryLog();
// ... your queries
dd(DB::getQueryLog());

# Check for N+1 problems
php artisan telescope:install
```

**Issue: Memory errors**
```bash
# Increase memory limit
ini_set('memory_limit', '512M');

# Optimize autoloader
composer dump-autoload --optimize
```

#### Authentication Problems

**Issue: Users can't login**
```bash
# Check user exists in correct tenant
php artisan tinker
tenancy()->initialize(Tenant::find(1));
User::where('email', 'user@example.com')->first();

# Reset password
php artisan tinker
$user = User::find(1);
$user->password = bcrypt('newpassword');
$user->save();
```

#### File Upload Issues

**Issue: Images not displaying**
```bash
# Create storage link
php artisan storage:link

# Check file permissions
chmod -R 755 storage/
chown -R www-data:www-data storage/
```

### Debug Mode

#### Enabling Debug Features
```bash
# Enable debug mode (development only)
APP_DEBUG=true

# Install Telescope for query debugging
composer require laravel/telescope
php artisan telescope:install
php artisan migrate
```

#### Common Debug Commands
```bash
# Clear all caches
php artisan optimize:clear

# View current configuration
php artisan config:show

# Check routes
php artisan route:list

# Check middleware
php artisan route:list --middleware=tenant

# Database queries
php artisan db:show
```

### Error Logs

#### Log Locations
- Application logs: `storage/logs/laravel.log`
- Web server logs: `/var/log/nginx/` or `/var/log/apache2/`
- Database logs: MySQL error log location

#### Common Error Patterns
```bash
# Tenant-related errors
grep "tenant" storage/logs/laravel.log

# Database connection errors
grep "database" storage/logs/laravel.log

# Permission errors
grep "permission" storage/logs/laravel.log
```

### Support & Maintenance

#### Regular Maintenance Tasks
```bash
# Update dependencies (monthly)
composer update
npm update

# Clear expired cache entries
php artisan cache:prune-stale-tags

# Optimize database tables
php artisan db:optimize

# Backup databases
php artisan backup:run

# Monitor disk space
df -h
```

#### Health Checks
```bash
# Check application status
php artisan health:check

# Verify tenant integrity
php artisan tenants:migrate --pretend

# Test API endpoints
curl -H "Accept: application/json" https://your-domain.com/api/programs
```

---

## Contributing

### Development Workflow
1. Fork the repository
2. Create feature branch (`git checkout -b feature/amazing-feature`)
3. Make changes and add tests
4. Run test suite (`php artisan test`)
5. Commit changes (`git commit -m 'Add amazing feature'`)
6. Push to branch (`git push origin feature/amazing-feature`)
7. Open Pull Request

### Code Review Guidelines
- Ensure all tests pass
- Follow Laravel coding standards
- Include documentation for new features
- Test multi-tenancy isolation
- Verify security implications

### Release Process
1. Update version in `composer.json`
2. Update `CHANGELOG.md`
3. Create release tag
4. Deploy to staging for testing
5. Deploy to production

---

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

## Support

For support and questions:
- **Documentation**: This comprehensive guide
- **GitHub Issues**: Report bugs and request features
- **Email**: support@your-domain.com
- **Documentation Updates**: Contributions welcome

---

*Last updated: January 2024*
*Version: 1.0.0*