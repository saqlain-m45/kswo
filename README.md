# KSWO Website (PHP + MySQL + jQuery)

KSWO is a responsive student welfare platform with:
- Public pages (Home, About, Past Presidents, Transparency)
- User module (Register/Login, Profile, Donate, Dashboard)
- Admin panel (Dashboard, Members, Donations, Presidents, Settings)

## Tech Stack
- PHP (XAMPP)
- MySQL
- HTML/CSS
- jQuery + Chart.js

## Database Setup
1. Start Apache and MySQL in XAMPP.
2. Import [database.sql](database.sql) using phpMyAdmin **or** CLI:
   ```bash
   C:\xampp\mysql\bin\mysql.exe -u root < C:\xampp\htdocs\kswo\database.sql
   ```

## Default Credentials
- Admin:
  - Email: `admin@kswo.org`
  - Password: `12345678`
- Super Admin:
  - Email: `superadmin@kswo.org`
  - Password: `12345678`
- User:
  - Email: `member@kswo.org`  
  - Password: `12345678`

## Entry Points
- Public Home: `/kswo/index.php`
- Public Donation: `/kswo/public_donate.php`
- Register: `/kswo/register.php`
- Login: `/kswo/login.php`
- User Dashboard: `/kswo/user/dashboard.php`
- Admin Dashboard: `/kswo/admin/dashboard.php`

## Features Implemented from Requirement
- Member registration with CNIC + password validation
- Login/logout and role-based dashboard routing
- Member verification workflow (approve/reject)
- 3-step donation flow with receipt and transaction ID
- Separate donation paths for members and public donors
- Public transparency table with filters/search + chart area
- Past presidents timeline + admin CRUD with optional photo upload
- Donation management with filters and CSV export
- Admin settings persistence
- Super admin full CRUD access across admin modules + payment account management
- Mobile responsive layout and trust-focused UI

## Flow Diagrams
See [docs/user_flows.md](docs/user_flows.md).
