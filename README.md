# Real Estate Portal

PHP + MySQL real estate listing platform with role-based access (Admin, Seller, Buyer, Broker).

## Requirements

- XAMPP (Apache + MySQL + PHP 8+)
- Browser

## Setup

1. Start **Apache** and **MySQL** in XAMPP Control Panel.

2. Import the database:
   - Open phpMyAdmin: http://localhost/phpmyadmin
   - Import `database.sql`

   Or via command line:
   ```
   C:\xampp\mysql\bin\mysql.exe -u root < database.sql
   ```

3. Open the app: http://localhost/real-estate/

## Default Admin Login

| Field    | Value                  |
|----------|------------------------|
| Email    | admin@realestate.com   |
| Password | admin123               |

Change this password in production.

## User Roles

| Role   | Registration | Notes                                      |
|--------|--------------|--------------------------------------------|
| Admin  | Seeded only  | Approves properties and seller accounts    |
| Seller | Self-register| Starts as `pending` until admin approves   |
| Buyer  | Self-register| Can browse, favourite, and send enquiries  |
| Broker | Self-register| Can manage broker profile                  |

## Workflow

1. Seller registers → admin approves seller account
2. Seller adds property → status `Pending`
3. Admin approves property → visible on public site
4. Buyer browses approved listings, saves favourites, sends enquiries

## Project Structure

```
real-estate/
├── admin/          Admin dashboard, approvals, users, reports
├── seller/         Property CRUD
├── buyer/          Favourites and enquiries
├── broker/         Profile management
├── includes/       DB, auth, navbar, footer
├── assets/         CSS and JS
├── uploads/        Property images
└── database.sql    Schema + seed data
```

## Tech Stack

- HTML, CSS, Bootstrap 5, JavaScript, SweetAlert2
- PHP (PDO)
- MySQL
