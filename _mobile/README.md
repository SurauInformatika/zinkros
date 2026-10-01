# Zinkros - Aplikasi Mobile

WebView wrapper untuk `https://zinkros.my.id/` (Sistem Zinkros).

## Struktur

```
_mobile/
├── shared/                 ← File bersama Android & iOS
│   └── offline.html        ← Halaman offline
├── android/                ← Kotlin Android app
│   ├── app/
│   │   ├── src/main/
│   │   │   ├── java/com/zinkros/app/
│   │   │   │   ├── MainActivity.kt    ← WebView utama
│   │   │   │   └── SplashActivity.kt  ← Splash screen
│   │   │   ├── res/
│   │   │   │   ├── layout/activity_main.xml
│   │   │   │   ├── layout/activity_splash.xml
│   │   │   │   ├── drawable/logo.xml
│   │   │   │   ├── values/colors.xml
│   │   │   │   ├── values/strings.xml
│   │   │   │   ├── values/themes.xml
│   │   │   │   └── xml/network_security_config.xml
│   │   │   ├── assets/offline.html
│   │   │   └── AndroidManifest.xml
│   │   └── build.gradle
│   └── build.gradle
└── ios/                    ← Swift iOS app
    └── Zinkros/
        ├── AppDelegate.swift
        ├── ViewController.swift
        ├── Info.plist
        └── offline.html
```

## Setup

### Android (Android Studio)
1. Buka Android Studio
2. Open Project → pilih `_mobile/android/`
3. Sync Gradle
4. Run

### iOS (Xcode)
1. Buka Xcode → New Project → pilih `_mobile/ios/`
2. Copy `AppDelegate.swift`, `ViewController.swift`, `Info.plist`, `offline.html` + gambar splash ke target
3. Run

## Fitur

| Fitur | Android | iOS |
|-------|---------|-----|
| WebView | ✅ | ✅ |
| Splash screen (2 detik) | ✅ `SplashActivity` | ✅ overlay |
| Offline detection | ✅ `ConnectivityManager` | ✅ `SCNetworkReachability` |
| Auto reconnect | ✅ | ✅ |
| Pull to refresh | ✅ `SwipeRefreshLayout` | ✅ bounce |
| Back navigation | ✅ + dialog konfirmasi keluar | ✅ gesture |
| Bridge JS ↔ Native | ✅ `NativeBridge` | ✅ `WKScriptMessageHandler` |
| Progress bar | ✅ | ✅ |

## Bridge: JavaScript ↔ Native

### Android
```javascript
// Dari JS ke Kotlin
window.NativeBridge.reload()
window.NativeBridge.isOnline()

// Dari Kotlin ke JS
webView.evaluateJavaScript("functionName()")
```

### iOS
```javascript
// Dari JS ke Swift
window.webkit.messageHandlers.NativeBridge.postMessage({action: 'reload'})
window.webkit.messageHandlers.NativeBridge.postMessage({action: 'isOnline'})

// Dari Swift ke JS
webView.evaluateJavaScript("functionName()")
```

## URL Configuration

| Environment | URL |
|-------------|-----|
| Production | `https://zinkros.my.id/` |

Ubah `baseUrl` (Android) / `baseUrl` (iOS) jika domain berubah.