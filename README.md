# FastBiz-Net

FastBiz-Net is a comprehensive ISP management system built with Laravel and Filament, featuring Mikrotik integration for network management.

## Features

### Network Management
- Mikrotik Router Integration
- IP Pool Management
- IP Binding Management
- Automatic IP Binding Synchronization

### Customer Management
- Customer Profiles
- Service Package Assignment
- Installation Tracking
- Due Date Management
- Status Monitoring

### Billing Management
- Service Package Configuration
- Invoice Generation (Individual & Bulk)
- Payment Tracking
- Professional Invoice Printing
- Payment Proof Upload
- Overdue Invoice Management

## Requirements

- PHP 8.1 or higher
- Laravel 10.x
- MySQL/MariaDB
- Composer
- Node.js & NPM
- Mikrotik Router with API access

## Installation

1. Clone the repository:
```bash
git clone https://github.com/yourusername/fastbiz-net.git
cd fastbiz-net
```

2. Install PHP dependencies:
```bash
composer install
```

3. Install and compile frontend dependencies:
```bash
npm install
npm run build
```

4. Copy environment file and configure:
```bash
cp .env.example .env
# Edit .env with your database and Mikrotik credentials
```

5. Generate application key:
```bash
php artisan key:generate
```

6. Run database migrations:
```bash
php artisan migrate
```

7. Create admin user:
```bash
php artisan db:seed --class=AdminUserSeeder
```

## Usage

1. Access the admin panel at `/admin`
2. Login with your admin credentials
3. Configure your Mikrotik routers
4. Start managing customers and billing

## Security

Remember to:
- Change default admin password
- Secure your Mikrotik API access
- Regularly backup your database
- Keep the system updated

## License

This project is licensed under the MIT License.
