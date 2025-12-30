# Deployment Guide - Heatt-Training Booking System

## Pre-deployment Checklist

- [ ] PHP 8.2+ installed on server
- [ ] MySQL/MariaDB database created
- [ ] LINE Messaging API channel created
- [ ] Pusher account created
- [ ] Domain pointed to server
- [ ] SSL certificate installed (HTTPS required for LINE LIFF)

## Step-by-Step Deployment

### 1. Server Setup

#### For Shared Hosting (cPanel/Plesk)

1. Upload all files to your hosting account
2. Extract files to your desired directory
3. Move all files from `public` folder to `public_html` or `www`
4. Adjust file paths in `public/index.php` if needed

#### For VPS/Dedicated Server

```bash
# Clone repository
git clone https://github.com/nsr002/booking.git /var/www/booking
cd /var/www/booking

# Set permissions
sudo chown -R www-data:www-data /var/www/booking
sudo chmod -R 755 /var/www/booking
sudo chmod -R 775 storage bootstrap/cache
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install --no-dev --optimize-autoload

# Install Node dependencies and build assets
npm install
npm run build
```

### 3. Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

Edit `.env` file with production values:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

PUSHER_APP_ID=your_pusher_app_id
PUSHER_APP_KEY=your_pusher_key
PUSHER_APP_SECRET=your_pusher_secret

LINE_CHANNEL_ID=your_line_channel_id
LINE_CHANNEL_SECRET=your_line_channel_secret
LINE_CHANNEL_ACCESS_TOKEN=your_line_access_token
LINE_LIFF_ID=your_liff_id
```

### 4. Database Migration

```bash
# Run migrations
php artisan migrate --force

# Seed sample data (optional)
php artisan db:seed --force
```

### 5. Storage Configuration

```bash
# Create symbolic link
php artisan storage:link

# Set proper permissions
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

### 6. Optimize Application

```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize autoloader
composer dump-autoload --optimize
```

### 7. Web Server Configuration

#### Apache (.htaccess)

The `.htaccess` files are already included. Ensure `mod_rewrite` is enabled:

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

#### Nginx

Create a server block:

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/booking/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

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
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### 8. SSL Certificate

```bash
# Using Let's Encrypt (Certbot)
sudo certbot --nginx -d your-domain.com
```

### 9. Queue Worker Setup

#### Using Supervisor (Recommended)

Create `/etc/supervisor/conf.d/booking-worker.conf`:

```ini
[program:booking-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/booking/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/booking/storage/logs/worker.log
stopwaitsecs=3600
```

Then:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start booking-worker:*
```

#### Using Cron (Alternative)

Add to crontab:

```bash
* * * * * cd /var/www/booking && php artisan schedule:run >> /dev/null 2>&1
```

### 10. Create Admin Account

```bash
php artisan tinker
```

```php
$user = new App\Models\User();
$user->name = 'Admin';
$user->email = 'admin@heatttraining.com';
$user->password = bcrypt('your-secure-password');
$user->role = 'admin';
$user->save();
exit;
```

### 11. LINE LIFF Configuration

1. Go to LINE Developers Console: https://developers.line.biz/
2. Select your channel
3. Go to LIFF tab
4. Create new LIFF app:
   - **Endpoint URL**: `https://your-domain.com/liff/booking`
   - **Size**: Full
   - **Scope**: `profile`, `openid`
5. Copy LIFF ID to `.env`
6. Add additional LIFF apps for:
   - History: `https://your-domain.com/liff/history`
   - Packages: `https://your-domain.com/liff/packages`

### 12. Pusher Configuration

1. Go to Pusher Dashboard: https://dashboard.pusher.com/
2. Create new app or select existing
3. Copy credentials to `.env`
4. Enable client events if needed

### 13. Test Deployment

```bash
# Test artisan commands
php artisan --version

# Test database connection
php artisan migrate:status

# Test cache
php artisan cache:clear

# Check logs
tail -f storage/logs/laravel.log
```

## Post-Deployment

### Monitoring

1. Monitor error logs: `storage/logs/laravel.log`
2. Monitor queue worker: `supervisorctl status booking-worker:*`
3. Monitor server resources (CPU, RAM, Disk)

### Backup Strategy

1. Database backup (daily):
```bash
mysqldump -u user -p database > backup-$(date +%Y%m%d).sql
```

2. Files backup (weekly):
```bash
tar -czf backup-$(date +%Y%m%d).tar.gz /var/www/booking
```

### Security Checklist

- [x] APP_DEBUG=false in production
- [x] HTTPS enabled
- [x] Strong database password
- [x] File permissions properly set
- [x] .env file not accessible via web
- [x] Regular security updates
- [x] Backup strategy in place

### Maintenance Mode

```bash
# Enable maintenance mode
php artisan down

# Run updates/migrations
php artisan migrate

# Disable maintenance mode
php artisan up
```

## Troubleshooting

### Common Issues

**Issue**: 500 Internal Server Error
**Solution**: Check storage permissions, verify .env configuration, check error logs

**Issue**: CSS/JS not loading
**Solution**: Run `npm run build`, clear cache, check asset paths

**Issue**: Database connection error
**Solution**: Verify database credentials, check if MySQL is running

**Issue**: Queue not processing
**Solution**: Restart supervisor, check worker logs

**Issue**: LIFF not loading
**Solution**: Verify HTTPS, check LIFF ID, ensure CORS is properly configured

## Support

For deployment issues, contact the development team.
