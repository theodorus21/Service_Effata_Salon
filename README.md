
# Service Effata Fun Learning

A Laravel-based fun learning management system.

The Laravel application and its Composer/npm configuration are located at the
repository root. Run the commands below from that directory.

## Project Setup

This project uses Laravel framework for the backend.

### Requirements
- PHP 8.3+
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

The public landing page, admin login, member login, signup, and dashboard are
served by Laravel at `/`, `/admin/signin`, `/member`, `/signup`, and `/admin`.
The signup page creates murid accounts through `POST /api/v1/users`.

Class categories are stored as lookup values (`Pelajaran` and `Musik`), can be
managed from `/admin/settings`, and can be selected when creating or editing a class.
Each class can have multiple schedules, stored separately and submitted as a
`schedules` array containing the day, lesson time, and duration for each record.

Dashboard, class, schedule, settings, and user-management routes require an
authenticated Laravel session. Guru and admin sign in at `/admin/signin`; use
the **Keluar** menu to end the session.

Students sign in at `/member` with an active murid account. The member portal
keeps its home, profile, password update, and sign-out flow under `/member`.
Member and staff accounts use separate authentication guards, so both portals
can stay signed in in the same browser; signing out of one does not sign out
the other.
Public signup creates murid accounts only; other staff accounts are created by
Master Admin.

All authenticated admin pages use the `/admin` path prefix. The authenticated
sidebar also provides **Role Akses** at `/admin/role-access`, where
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
