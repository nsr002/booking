# Heatt-Training Booking System

A comprehensive booking system for training sessions built with Laravel 11, featuring LINE LIFF integration for customers and a real-time admin dashboard.

## 🚀 Features

### Customer Features (LINE LIFF)
- **LINE Login** - Seamless authentication via LINE LIFF
- **View Schedules** - Browse available training sessions with trainer details
- **Book Sessions** - Book training sessions using credits or pay separately
- **Credit Management** - Purchase and manage training packages
- **Booking History** - View all past and upcoming bookings
- **Real-time Updates** - Get instant notifications when bookings are approved/rejected

### Admin Features
- **Real-time Dashboard** - Live updates when new bookings arrive
- **Booking Management** - Approve, reject, or complete bookings
- **Schedule Management** - Create and manage training schedules
- **Package Management** - Create and manage training packages
- **User Management** - View and manage users
- **Statistics** - View booking statistics and revenue

## 🛠️ Tech Stack

- **Framework:** Laravel 11
- **Database:** MySQL/MariaDB
- **Real-time:** Pusher Broadcasting
- **Frontend:** Blade Templates + Tailwind CSS
- **LINE Integration:** LIFF SDK + Messaging API
- **File Storage:** Laravel Storage (public disk)

## 📋 Requirements

- PHP 8.2 or higher
- Composer
- MySQL 5.7+ or MariaDB 10.3+
- Node.js & NPM (for assets compilation)
- LINE Messaging API account
- Pusher account (for real-time features)

## 🔧 Installation

### 1. Clone the Repository

```bash
git clone https://github.com/nsr002/booking.git
cd booking
```

### 2. Install Dependencies

```bash
composer install --no-dev --optimize-autoload
npm install
npm run build
```

### 3. Environment Configuration

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` file with your configuration:

```env
APP_NAME=HeattTraining
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

BROADCAST_DRIVER=pusher
FILESYSTEM_DISK=public

# Pusher Configuration
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=ap1

# LINE Configuration
LINE_CHANNEL_ID=your_channel_id
LINE_CHANNEL_SECRET=your_channel_secret
LINE_CHANNEL_ACCESS_TOKEN=your_access_token
LINE_LIFF_ID=your_liff_id
```

### 4. Database Setup

```bash
php artisan migrate --force
```

### 5. Storage Setup

```bash
php artisan storage:link
chmod -R 775 storage bootstrap/cache
```

### 6. Create Admin User

```bash
php artisan tinker
```

Then run:

```php
$user = new App\Models\User();
$user->name = 'Admin';
$user->email = 'admin@example.com';
$user->password = bcrypt('password');
$user->role = 'admin';
$user->save();
```

### 7. Cache Configuration

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 📱 LINE LIFF Setup

1. Create a LINE Messaging API channel at https://developers.line.biz/
2. Create a LIFF app in your channel
3. Set LIFF endpoint URL: `https://your-domain.com/liff/booking`
4. Copy the LIFF ID to your `.env` file
5. Add the following LIFF endpoints:
   - `/liff/booking` - Main booking page
   - `/liff/history` - Booking history
   - `/liff/packages` - Package management

## 🔐 Security

- CSRF protection enabled on all forms
- LINE token verification on API endpoints
- Admin authentication middleware
- File upload validation (images only, max 5MB)
- Rate limiting on API endpoints
- SQL injection protection via Eloquent ORM

## 📊 Database Schema

### Tables
- `users` - User accounts (customers, admins, trainers)
- `packages` - Training packages
- `user_packages` - User's purchased packages
- `trainers` - Trainer profiles
- `schedules` - Training schedules
- `bookings` - Booking records

See migrations in `database/migrations/` for detailed schema.

## 🌐 API Endpoints

### Public Endpoints

```
POST   /api/liff/auth                    - Authenticate LINE user
```

### Protected Endpoints (Require LIFF Token)

```
GET    /api/me                           - Get current user info
GET    /api/packages                     - List active packages
POST   /api/packages/purchase            - Purchase a package
GET    /api/user/credits                 - Get user's credits
GET    /api/schedules                    - List available schedules
GET    /api/schedules/{id}               - Get schedule details
GET    /api/bookings/my                  - Get user's bookings
POST   /api/bookings                     - Create a booking
GET    /api/bookings/{id}                - Get booking details
POST   /api/bookings/{id}/cancel         - Cancel a booking
```

### Admin Endpoints (Require Authentication)

```
GET    /admin/dashboard                  - Admin dashboard
GET    /admin/bookings                   - List bookings
POST   /admin/bookings/{id}/approve      - Approve booking
POST   /admin/bookings/{id}/reject       - Reject booking
POST   /admin/bookings/{id}/complete     - Complete booking
...and more
```

## 🚀 Deployment

### Shared Hosting (cPanel/Plesk)

1. Upload all files to your hosting
2. Point document root to `/public` folder
3. Import database and run migrations
4. Set proper permissions on `storage` and `bootstrap/cache`
5. Configure `.htaccess` if needed
6. Set up queue worker for real-time events:

```bash
php artisan queue:work --daemon
```

### Queue Worker Setup

Add to crontab:

```
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

For continuous queue processing:

```bash
php artisan queue:work --tries=3
```

## 🔄 Real-time Events

The system uses Pusher for real-time updates:

### Events
- `NewBookingEvent` - Fired when a customer creates a booking
- `BookingStatusUpdated` - Fired when admin approves/rejects booking

### Channels
- `booking-channel` - Public channel for admin notifications
- `private-user.{line_user_id}` - Private channel for user notifications

## 📝 Usage

### For Customers

1. Open LINE app and access LIFF URL
2. Allow LINE login
3. Browse available schedules
4. Purchase packages (optional)
5. Book training sessions
6. View booking history

### For Admins

1. Login at `/admin/dashboard`
2. View real-time booking notifications
3. Approve or reject bookings
4. Manage schedules, packages, and users
5. View statistics and reports

## 🤝 Contributing

This is a private project. For any issues or suggestions, please contact the development team.

## 📄 License

Proprietary - All rights reserved

## 👥 Support

For support, please contact:
- Email: support@heatttraining.com
- LINE: @heatttraining

## 🔗 Links

- Production: https://your-domain.com
- Admin Panel: https://your-domain.com/admin/dashboard
- LIFF App: https://liff.line.me/your-liff-id

---

Built with ❤️ for Heatt Training by the Development Team
