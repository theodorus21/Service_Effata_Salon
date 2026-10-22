
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

### Frontend

The signin, signup, and dashboard pages are served by Laravel at `/signin`,
`/signup`, and `/dashboard`. The signup page sends `name`, `email`, `password`,
and the default `user` role to `POST /api/v1/users` on the same application.

Class categories are stored as lookup values (`Pelajaran` and `Musik`), can be
managed from `/settings`, and can be selected when creating or editing a class.
Each class can have multiple schedules, stored separately and submitted as a
`schedules` array containing the day, lesson time, and duration for each record.

Dashboard, class, schedule, settings, and user-management routes require an
authenticated Laravel session. Sign in at `/signin` and use the **Keluar** menu
to end the session.

The authenticated sidebar also provides **Role Akses** at `/role-access`, where
roles can be listed, added, renamed, and removed when they are not system roles
or assigned to a user. Existing roles are seeded into the dedicated `roles`
table during migration.

Select **Akses** for a role to configure its menu, submenu, and action
permissions. Permissions are stored in `permissions` and `role_permissions`;
the same permission checks control both menu/button visibility and server-side
route access. Master Admin always has every permission. Other existing roles
retain dashboard access by default, while newly created roles start without
permissions until configured.

The public signup API remains available; the user-management API endpoints
require the **API: lihat dan kelola akun pengguna** permission.
