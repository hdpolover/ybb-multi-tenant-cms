# YBB Multi-Tenant CMS - Deployment Guide

## Quick Start Deployment

### Prerequisites Checklist
- [ ] PHP 8.2+ installed with required extensions
- [ ] Composer 2.x installed
- [ ] MySQL 8.0+ or PostgreSQL 13+ database server
- [ ] Web server (Apache/Nginx) configured
- [ ] SSL certificate for HTTPS
- [ ] Domain/subdomain DNS configured

### 1. Server Setup

#### Ubuntu/Debian Server Setup
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP 8.2 and extensions
sudo apt install php8.2 php8.2-fpm php8.2-mysql php8.2-redis php8.2-mbstring \
    php8.2-xml php8.2-gd php8.2-curl php8.2-zip php8.2-bcmath php8.2-intl

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install MySQL
sudo apt install mysql-server-8.0
sudo mysql_secure_installation

# Install Redis
sudo apt install redis-server
sudo systemctl enable redis-server

# Install Nginx
sudo apt install nginx
sudo systemctl enable nginx
```

### 2. Application Deployment

#### Clone and Configure
```bash
# Clone repository
cd /var/www
sudo git clone https://github.com/your-org/ybb-multi-tenant-cms.git
cd ybb-multi-tenant-cms

# Set permissions
sudo chown -R www-data:www-data /var/www/ybb-multi-tenant-cms
sudo chmod -R 755 /var/www/ybb-multi-tenant-cms
sudo chmod -R 775 /var/www/ybb-multi-tenant-cms/storage
sudo chmod -R 775 /var/www/ybb-multi-tenant-cms/bootstrap/cache

# Install dependencies
composer install --optimize-autoloader --no-dev

# Environment setup
cp .env.example .env
php artisan key:generate
```

#### Environment Configuration
```bash
# Edit .env file
nano .env

# Production settings
APP_NAME="YBB Multi-Tenant CMS"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ybb_central
DB_USERNAME=ybb_user
DB_PASSWORD=secure_password

# Cache & Sessions
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Mail configuration
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-server
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
```

### 3. Database Setup

#### Create Databases
```sql
-- Login to MySQL
mysql -u root -p

-- Create central database
CREATE DATABASE ybb_central CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create database user
CREATE USER 'ybb_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON ybb_central.* TO 'ybb_user'@'localhost';
GRANT CREATE ON *.* TO 'ybb_user'@'localhost';
FLUSH PRIVILEGES;
```

#### Run Migrations
```bash
# Run central database migrations
php artisan migrate

# Create first tenant (example)
php artisan tenant:create example "Example Organization" example.your-domain.com
```

### 4. Web Server Configuration

#### Nginx Configuration
```nginx
# /etc/nginx/sites-available/ybb-cms
server {
    listen 80;
    server_name your-domain.com *.your-domain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name your-domain.com *.your-domain.com;
    root /var/www/ybb-multi-tenant-cms/public;
    index index.php;

    # SSL Configuration
    ssl_certificate /path/to/ssl/certificate.crt;
    ssl_certificate_key /path/to/ssl/private.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript application/javascript application/xml+rss application/json;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Cache static assets
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|woff|woff2)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

#### Enable Site
```bash
# Enable site
sudo ln -s /etc/nginx/sites-available/ybb-cms /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### 5. Process Management

#### Supervisor for Queues
```bash
# Install Supervisor
sudo apt install supervisor

# Create worker configuration
sudo nano /etc/supervisor/conf.d/ybb-worker.conf
```

```ini
[program:ybb-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/ybb-multi-tenant-cms/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/var/www/ybb-multi-tenant-cms/storage/logs/worker.log
stopwaitsecs=3600
```

```bash
# Start supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start ybb-worker:*
```

### 6. Caching & Optimization

#### Laravel Optimization
```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize autoloader
composer dump-autoload --optimize

# Create storage link
php artisan storage:link
```

#### PHP-FPM Tuning
```bash
# Edit PHP-FPM pool configuration
sudo nano /etc/php/8.2/fpm/pool.d/www.conf

# Optimize for production
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 1000

# Restart PHP-FPM
sudo systemctl restart php8.2-fpm
```

### 7. Security Configuration

#### Firewall Setup
```bash
# Configure UFW
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw --force enable
```

#### File Permissions
```bash
# Set proper permissions
find /var/www/ybb-multi-tenant-cms -type f -exec chmod 644 {} \;
find /var/www/ybb-multi-tenant-cms -type d -exec chmod 755 {} \;
chmod -R 775 /var/www/ybb-multi-tenant-cms/storage
chmod -R 775 /var/www/ybb-multi-tenant-cms/bootstrap/cache
```

### 8. Monitoring & Backup

#### Log Rotation
```bash
# Create logrotate configuration
sudo nano /etc/logrotate.d/ybb-cms
```

```
/var/www/ybb-multi-tenant-cms/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    notifempty
    create 644 www-data www-data
}
```

#### Database Backup Script
```bash
#!/bin/bash
# /home/backup/ybb-backup.sh

TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_DIR="/home/backup"
DB_USER="ybb_user"
DB_PASS="secure_password"

# Backup central database
mysqldump -u$DB_USER -p$DB_PASS ybb_central > $BACKUP_DIR/ybb_central_$TIMESTAMP.sql

# Backup tenant databases
mysql -u$DB_USER -p$DB_PASS -e "SHOW DATABASES LIKE 'tenant_%';" | grep tenant_ | while read db; do
    mysqldump -u$DB_USER -p$DB_PASS $db > $BACKUP_DIR/${db}_$TIMESTAMP.sql
done

# Compress backups
cd $BACKUP_DIR
tar -czf ybb_backup_$TIMESTAMP.tar.gz *_$TIMESTAMP.sql
rm *_$TIMESTAMP.sql

# Remove backups older than 30 days
find $BACKUP_DIR -name "ybb_backup_*.tar.gz" -mtime +30 -delete
```

#### Cron Jobs
```bash
# Edit crontab
sudo crontab -e

# Add scheduled tasks
# Laravel scheduler
* * * * * cd /var/www/ybb-multi-tenant-cms && php artisan schedule:run >> /dev/null 2>&1

# Daily backup (as backup user)
0 2 * * * /home/backup/ybb-backup.sh

# Weekly log cleanup
0 3 * * 0 find /var/www/ybb-multi-tenant-cms/storage/logs -name "*.log" -mtime +7 -delete
```

## Docker Deployment

### Docker Compose Production

#### docker-compose.yml
```yaml
version: '3.8'

services:
  app:
    build:
      context: .
      dockerfile: Dockerfile.prod
    container_name: ybb_app
    restart: unless-stopped
    volumes:
      - ./storage:/var/www/storage
      - ./bootstrap/cache:/var/www/bootstrap/cache
    depends_on:
      - database
      - redis
    environment:
      - APP_ENV=production
      - DB_HOST=database
      - REDIS_HOST=redis

  nginx:
    image: nginx:alpine
    container_name: ybb_nginx
    restart: unless-stopped
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./public:/var/www/public:ro
      - ./docker/nginx/production.conf:/etc/nginx/conf.d/default.conf:ro
      - ./ssl:/etc/nginx/ssl:ro
    depends_on:
      - app

  database:
    image: mysql:8.0
    container_name: ybb_database
    restart: unless-stopped
    environment:
      MYSQL_ROOT_PASSWORD: ${DB_PASSWORD}
      MYSQL_DATABASE: ${DB_DATABASE}
      MYSQL_USER: ${DB_USERNAME}
      MYSQL_PASSWORD: ${DB_PASSWORD}
    volumes:
      - mysql_data:/var/lib/mysql
      - ./docker/mysql/my.cnf:/etc/mysql/conf.d/my.cnf:ro
    ports:
      - "3306:3306"

  redis:
    image: redis:alpine
    container_name: ybb_redis
    restart: unless-stopped
    volumes:
      - redis_data:/data

  queue:
    build:
      context: .
      dockerfile: Dockerfile.prod
    container_name: ybb_queue
    restart: unless-stopped
    command: php artisan queue:work redis --sleep=3 --tries=3
    volumes:
      - ./storage:/var/www/storage
    depends_on:
      - database
      - redis
    environment:
      - APP_ENV=production

volumes:
  mysql_data:
  redis_data:
```

#### Dockerfile.prod
```dockerfile
FROM php:8.2-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    bash \
    curl \
    freetype-dev \
    g++ \
    gcc \
    git \
    icu-dev \
    jpeg-dev \
    libc-dev \
    libpng-dev \
    libzip-dev \
    make \
    mysql-client \
    oniguruma-dev \
    supervisor \
    zip

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install \
    bcmath \
    gd \
    intl \
    mbstring \
    pdo_mysql \
    zip

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy composer files
COPY composer.json composer.lock ./

# Install dependencies
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Copy application
COPY . .

# Set permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www \
    && chmod -R 775 /var/www/storage \
    && chmod -R 775 /var/www/bootstrap/cache

# Run composer scripts
RUN composer run-script post-autoload-dump

# Expose port
EXPOSE 9000

# Start PHP-FPM
CMD ["php-fpm"]
```

### Deployment Commands
```bash
# Deploy with Docker
git pull origin main
docker-compose down
docker-compose build --no-cache
docker-compose up -d

# Run migrations
docker-compose exec app php artisan migrate --force

# Clear caches
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:cache
```

## CI/CD Pipeline

### GitHub Actions Workflow

#### .github/workflows/deploy.yml
```yaml
name: Deploy to Production

on:
  push:
    branches: [ main ]

env:
  PHP_VERSION: 8.2

jobs:
  test:
    runs-on: ubuntu-latest
    
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: password
          MYSQL_DATABASE: testing
        options: >-
          --health-cmd="mysqladmin ping"
          --health-interval=10s
          --health-timeout=5s
          --health-retries=3

    steps:
    - uses: actions/checkout@v3

    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: ${{ env.PHP_VERSION }}
        extensions: mbstring, pdo_mysql, zip, gd, redis
        coverage: xdebug

    - name: Cache dependencies
      uses: actions/cache@v3
      with:
        path: ~/.composer/cache/files
        key: dependencies-composer-${{ hashFiles('composer.json') }}

    - name: Install dependencies
      run: composer install --prefer-dist --no-interaction --no-suggest

    - name: Copy environment file
      run: cp .env.example .env

    - name: Generate key
      run: php artisan key:generate

    - name: Run migrations
      run: php artisan migrate --env=testing

    - name: Run tests
      run: php artisan test --coverage-clover coverage.xml

    - name: Upload coverage
      uses: codecov/codecov-action@v3
      with:
        file: ./coverage.xml

  deploy:
    needs: test
    runs-on: ubuntu-latest
    if: github.ref == 'refs/heads/main'

    steps:
    - name: Deploy to server
      uses: appleboy/ssh-action@v0.1.5
      with:
        host: ${{ secrets.HOST }}
        username: ${{ secrets.USERNAME }}
        key: ${{ secrets.PRIVATE_KEY }}
        script: |
          cd /var/www/ybb-multi-tenant-cms
          git pull origin main
          composer install --no-dev --optimize-autoloader
          php artisan migrate --force
          php artisan config:cache
          php artisan route:cache
          php artisan view:cache
          sudo systemctl reload nginx
          sudo supervisorctl restart ybb-worker:*

    - name: Notify deployment
      uses: 8398a7/action-slack@v3
      with:
        status: ${{ job.status }}
        channel: '#deployments'
        webhook_url: ${{ secrets.SLACK_WEBHOOK }}
```

## Health Monitoring

### Application Health Checks

#### Health Check Route
```php
// routes/web.php
Route::get('/health', function () {
    $checks = [
        'database' => DB::connection()->getPdo() ? 'ok' : 'error',
        'redis' => Redis::ping() ? 'ok' : 'error',
        'storage' => is_writable(storage_path()) ? 'ok' : 'error',
        'queue' => Queue::size() < 1000 ? 'ok' : 'warning',
    ];
    
    $status = in_array('error', $checks) ? 'error' : 'ok';
    
    return response()->json([
        'status' => $status,
        'checks' => $checks,
        'timestamp' => now()->toISOString(),
    ], $status === 'error' ? 500 : 200);
});
```

### Monitoring with Uptime Tools
```bash
# Example monitoring endpoints
GET https://your-domain.com/health
GET https://tenant1.your-domain.com/health
GET https://admin.your-domain.com/health
```

## Performance Optimization

### Database Optimization
```sql
-- Add indexes for common queries
ALTER TABLE programs ADD INDEX idx_status_featured (status, featured);
ALTER TABLE programs ADD INDEX idx_type_deadline (type, deadline);
ALTER TABLE jobs ADD INDEX idx_status_featured_created (status, featured, created_at);
ALTER TABLE posts ADD INDEX idx_status_published (status, published_at);

-- Optimize MySQL configuration
[mysqld]
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
query_cache_size = 128M
max_connections = 200
```

### Redis Configuration
```bash
# /etc/redis/redis.conf
maxmemory 512mb
maxmemory-policy allkeys-lru
save 900 1
save 300 10
save 60 10000
```

### CDN Integration
```php
// config/filesystems.php
'cloudfront' => [
    'driver' => 's3',
    'key' => env('AWS_ACCESS_KEY_ID'),
    'secret' => env('AWS_SECRET_ACCESS_KEY'),
    'region' => env('AWS_DEFAULT_REGION'),
    'bucket' => env('AWS_BUCKET'),
    'url' => env('AWS_URL'),
    'endpoint' => env('AWS_ENDPOINT'),
],
```

This deployment guide provides comprehensive instructions for deploying the YBB Multi-Tenant CMS in production environments with proper security, monitoring, and optimization configurations.