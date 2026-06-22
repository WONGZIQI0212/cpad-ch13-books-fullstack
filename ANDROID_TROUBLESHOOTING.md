# Android Troubleshooting

## Gradle cannot find the Android SDK

If Android Studio says the SDK is missing, open Android Studio once and let it download the SDK.

If you need to point Gradle at an existing SDK, create `frontend/android/local.properties` with:

```properties
sdk.dir=C:\\Users\\user\\AppData\\Local\\Android\\Sdk
```

## WebView cannot reach localhost

- Do not use `localhost` in `VITE_API_BASE_URL` for the final phone demo.
- Build with the deployed API URL, then run `npx cap sync android` again.

## CORS errors

- Add the frontend deployment URL to `CORS_ALLOWED_ORIGINS`.
- Rebuild the frontend after changing `.env.production`.
