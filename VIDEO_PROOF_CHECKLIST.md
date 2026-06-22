# Video Proof Checklist

Use this order for the Chapter 13 submission video.

## 1. Repository

- Show the GitHub repository.
- Show the commit history.
- Show the project structure: `backend`, `frontend`, and `deploy`.

## 2. Backend Production

- Open the deployed backend URL.
- Show `GET /` returning the API health JSON.
- Show `GET /api/books` returning book data.
- Mention the backend uses MySQL, JWT, CORS allow-list, validation, security headers, rate limiting, IDOR protection, and audit logs.

## 3. Frontend Production

- Open the deployed frontend URL.
- Login with `member@books.test / password`.
- Show books loading from the deployed API.
- Search for a book.
- Create a new book.
- Edit your own book.
- Show `/profile`.
- Logout.

## 4. Admin Flow

- Login with `admin@books.test / password`.
- Delete a book.
- Explain that admin-only delete is enforced by the backend.

## 5. Production Build

Run or show:

```bat
cd frontend
npm run build
npm run preview
```

Explain that `dist/` is the optimized production bundle.

## 6. Capacitor Android

Run or show:

```bat
cd frontend
npm run build
npx cap sync android
npx cap open android
```

In Android Studio:

- Wait for Gradle sync.
- Select an emulator or phone.
- Click Run.
- Login inside the Android app.
- Show books loading in the app.

## 7. Closing

Say that the same project now runs in local development, production web hosting, and Android through Capacitor.
