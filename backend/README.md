# Books API Secure Lab

Complete Chapter 12 lab project for hardening the Chapter 11 Books API.

## What Was Added

- `Validator` helper for whitelist-based input validation.
- XSS-safer JSON encoding with `JSON_HEX_*` flags.
- `SecurityHeaders` middleware for HSTS, CSP, frame blocking, and related headers.
- `RateLimit` middleware on `POST /auth/login`.
- CORS allow-list from `CORS_ALLOWED_ORIGINS`.
- `books.created_by` ownership tracking.
- IDOR protection: owners or admins can update; only admins can delete.
- `audit_log` table and security event recording.
- Bonus `GET /admin/audit` endpoint for admins.

## Setup

Start Laragon first so MySQL is running.

For a clean Chapter 12 database:

```bat
cd /d "C:\Users\user\Documents\CPAD\chap12 lab-in class\books-api-secure"
mysql -u root < database\schema.sql
```

If you already imported Chapter 11 data and only want to upgrade it:

```bat
cd /d "C:\Users\user\Documents\CPAD\chap12 lab-in class\books-api-secure"
mysql -u root < database\upgrade_ch12.sql
```

Start the API:

```bat
php -S localhost:8000 -t public public/router.php
```

## Demo Users

Both seeded users use the password `password`.

- `admin@books.test` has role `admin`
- `member@books.test` has role `member`

Use `requests.http` with the VS Code REST Client extension to test the defenses.
