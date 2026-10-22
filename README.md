# Service Effata Fun Learning

A Laravel-based fun learning management system.

## Project Setup

This project uses Laravel framework for the backend.

### Requirements
- PHP 8.0+
- Composer
- MySQL/MariaDB
- Node.js (for frontend assets)

### Installation

1. Clone the repository
2. Run `composer install`
3. Copy `.env.example` to `.env`
4. Run `php artisan key:generate`
5. Configure your database in `.env`
6. Run `php artisan migrate`
7. Run `npm install && npm run dev`

### Running the Application

```bash
php artisan serve
```

The application will be available at `http://localhost:8000`
