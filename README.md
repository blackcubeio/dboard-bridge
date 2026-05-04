# Blackcube DBoard Bridge

Bridge for running the Blackcube admin panel (dboard) inside non-Yii3 frameworks. Boots a Yii3 sub-application, handles the request, returns a PSR-7 response.

[![License](https://img.shields.io/badge/license-BSD--3--Clause-blue.svg)](LICENSE.md)
[![Packagist Version](https://img.shields.io/packagist/v/blackcube/dboard-bridge.svg)](https://packagist.org/packages/blackcube/dboard-bridge)

## Where dboard-bridge sits

```
┌─────────────────────────────────┐
│  your app (Slim / Laravel / …)  │
└────────────────┬────────────────┘
                 ↓
┌─────────────────────────────────┐
│  dboard-bridge ← you are here   │
│  boots Yii3, returns PSR-7      │
└────────────────┬────────────────┘
                 ↓
          ┌──────────────┐
          │    dboard    │
          │ (admin panel)│
          └──────┬───────┘
                 ↓
            ┌──────────┐
            │  dcore   │
            │ (data)   │
            └──────────┘
                 ↓
                 DB
```

## Quickstart

```bash
composer require blackcube/dboard-bridge
```

Create a `dboard/` config directory at your project root with a `configuration.php`:

```php
<?php

declare(strict_types=1);

if (!class_exists(\Blackcube\DboardBridge\DboardBridge::class)) {
    return [];
}

return [
    'config-plugin' => [
        'di' => 'common/di/*.php',
        'di-web' => [
            '$di',
            'web/di/*.php',
        ],
    ],
    'config-plugin-options' => [
        'source-directory' => 'dboard',
    ],
];
```

The host project must provide DB connection, OAuth2 params and dboard params in this config directory.

## Integration

### Slim

Native PSR-7 — no conversion needed:

```php
use Blackcube\DboardBridge\DboardBridge;

if (class_exists(DboardBridge::class)) {
    $app->any('/dboard[/{path:.*}]', function () {
        return DboardBridge::handle(
            dirname(__DIR__),
            debug: true,
            configDirectory: 'dboard',
        );
    });
}
```

### Laravel

Requires manual PSR-7 conversion:

```php
use Blackcube\DboardBridge\DboardBridge;

if (class_exists(DboardBridge::class)) {
    Route::any('/dboard/{path?}', function (Request $request) {
        $psrFactory = new \HttpSoft\Message\ServerRequestFactory();
        $streamFactory = new \HttpSoft\Message\StreamFactory();
        $psrRequest = $psrFactory->createServerRequest(
            $request->method(),
            $request->fullUrl(),
            $_SERVER,
        );
        $psrRequest = $psrRequest
            ->withQueryParams($request->query->all())
            ->withCookieParams($_COOKIE)
            ->withParsedBody($request->all())
            ->withBody($streamFactory->createStream($request->getContent()));
        foreach ($request->headers->all() as $name => $values) {
            $psrRequest = $psrRequest->withHeader($name, $values);
        }

        $psr = DboardBridge::handle(
            base_path(),
            debug: true,
            configDirectory: 'dboard',
            request: $psrRequest,
        );

        $response = response($psr->getBody()->getContents(), $psr->getStatusCode());
        foreach ($psr->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                $response->headers->set($name, $value, false);
            }
        }
        return $response;
    })->where('path', '.*');
}
```

Laravel also requires CSRF and cookie exemptions in `bootstrap/app.php`:

```php
$middleware->validateCsrfTokens(except: ['dboard/*', 'dboard']);
$middleware->encryptCookies(except: ['PHPSESSID', 'access_token', '_csrf']);
```

## API

```php
DboardBridge::handle(
    string $rootPath,           // project root
    bool $debug = false,        // debug mode
    string $configDirectory = 'config',  // Yii3 config directory
    ?ServerRequestInterface $request = null,  // PSR-7 request (recommended)
): ResponseInterface
```

The `request` parameter is recommended when the host framework consumes `php://input` before Yii3 can read it (Laravel, Symfony). Slim passes PSR-7 natively.

## License

BSD-3-Clause. See [LICENSE.md](LICENSE.md).
