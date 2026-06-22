# Lab 13 Books Full-Stack Deployment

SCSM2223 Chapter 13 project built from the Chapter 12 secure Books API.
Recommended GitHub repo name: `scsm2223-ch13-books-fullstack`

## Structure

```text
backend/   PHP Slim Books API with JWT, validation, CORS, rate limiting, IDOR protection, and audit logs
frontend/  Vue 3 + Vite + Pinia app configured for production build and Capacitor Android
deploy/    Deployment checklist for backend, frontend, database, and Android wrapping
```

## Local Backend

```bat
cd backend
composer install
copy .env.example .env
mysql -u root < database\schema.sql
php -S localhost:8000 -t public public/router.php
```

Seeded users:

```text
admin@books.test / password
member@books.test / password
```

## Local Frontend

```bat
cd frontend
npm install
npm run dev
```

The development frontend expects the API at `http://localhost:8000`.

## GitHub

See [`GITHUB_SETUP.md`](GITHUB_SETUP.md) for the exact remote and push commands.

## Production Build

```bat
cd frontend
npm run build
npm run preview
```

Deploy the generated `frontend/dist` folder to a static host.

For Vercel or Netlify, set this environment variable in the hosting dashboard:

```text
VITE_API_BASE_URL=https://your-deployed-api-url
```

After the frontend is deployed, add the frontend URL to the backend `CORS_ALLOWED_ORIGINS`.

## Capacitor Android

```bat
cd frontend
npm install @capacitor/core @capacitor/cli @capacitor/android
npm run build
npx cap add android
npx cap sync android
npx cap open android
```

Before syncing Android for the final demo, set `VITE_API_BASE_URL` to the deployed backend URL and rebuild.
