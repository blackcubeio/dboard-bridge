# dboard-bridge — Bridge

## The call (`src/DboardBridge.php`)

```php
DboardBridge::handle(
    string $rootPath,                       // project root: the directory holding the config directory and vendor/
    bool $debug = false,                    // Yii3 debug mode of the sub-application
    string $configDirectory = 'config',     // relative to $rootPath; 'dboard' in the README layout
    ?ServerRequestInterface $request = null // the PSR-7 request; null lets the runner build it from the globals
): ResponseInterface
```

`handle()` creates `new HttpApplicationRunner(rootPath, debug, checkEvents: false, configDirectory)` and returns `$runner->runAndGetResponse($request)` (`:50-57`). The runner of `yiisoft/yii-runner-http` loads the merged configuration of the directory (host files + every config-plugin package installed, dboard and this bridge included), builds the container from `di-web`, runs the `Application` middleware stack and returns the PSR-7 response without emitting it.

## What the package config binds

| File | Group | Bindings |
|---|---|---|
| `config/common/di/logger.php:17-24` | `di` | `LoggerInterface` → `Yiisoft\Log\Logger` with one `FileTarget` |
| `config/common/di/rbac.php:17-18` | `di` | `ItemsStorageInterface` → `Yiisoft\Rbac\Db\ItemsStorage`, `AssignmentsStorageInterface` → `Yiisoft\Rbac\Db\AssignmentsStorage` (the tables created by the dboard migrations) |
| `config/web/di/http.php:25-30` | `di-web` | the six PSR-17 factories → `HttpSoft\Message\*` |
| `config/web/di/router.php:20-27` | `di-web` | `RouteCollectionInterface` → `RouteCollection` over a `RouteCollector` fed with `$config->get('routes')` |
| `config/web/di/application.php:24-41` | `di-web` | `Yiisoft\Yii\Http\Application` with `NotFoundHandler` as fallback and the `MiddlewareDispatcher` stack `ErrorCatcher`, `SessionMiddleware`, `MultiVerbParserMiddleware`, `ContentNegotiatorDataResponseMiddleware`, `RequestCatcherMiddleware`, `Router` |

Not bound here, so provided by dboard's own config-plugin or by the host: the `blackcube/dboard` params, `DboardConfig`, `CypherKey`, `WebauthnConfig`, the routes (`blackcube/dboard` `config/routes.php`), the database connection, the RBAC `ManagerInterface`, the view renderer injections, the translator sources, the asset manager paths.

## Mounting on the host

Slim (`README.md:72-83`): a catch-all route on the dboard prefix returning `DboardBridge::handle(dirname(__DIR__), debug: true, configDirectory: 'dboard')`; Slim already speaks PSR-7, the response is returned as is.

Laravel (`README.md:90-126`): the Laravel request is rebuilt as a `HttpSoft` server request (method, full URL, `$_SERVER`, query, cookies, parsed body, raw body, headers), passed as `request:`; the PSR-7 response is copied into a Laravel response (body, status, headers). Laravel must exempt the dboard paths from its CSRF check and leave the `PHPSESSID`, `access_token` and `_csrf` cookies unencrypted (`README.md:131-134`), otherwise the Yii3 session, the JWT cookie of `JwtAuthMiddleware` and the Yii3 CSRF token are unreadable by the sub-application.

Both snippets guard the mount with `class_exists(DboardBridge::class)` so the application still runs when the admin panel is not installed (`README.md:46-48`, `:75`, `:93`).
