# blackcube/dboard-bridge — LLM index

**Role:** runs the Blackcube admin panel (`blackcube/dboard`, a Yii3 package) inside an application that is not Yii3. One static call boots a Yii3 sub-application from a configuration directory of the host project, lets it handle the admin request, and hands back a PSR-7 response the host framework converts to its own. The package ships the Yii3 plumbing dboard needs and the host does not have: HTTP factories, router, middleware stack, logger, RBAC storages.

## Package card

| | |
|---|---|
| Namespace | `Blackcube\DboardBridge` |
| Class | `DboardBridge::handle(string $rootPath, bool $debug = false, string $configDirectory = 'config', ?ServerRequestInterface $request = null): ResponseInterface` (`src/DboardBridge.php:44-58`), the only class |
| Config | config-plugin (`config/configuration.php:12-21`): group `di` → `config/common/di/*.php` (`logger.php`, `rbac.php`), group `di-web` → `$di` + `config/web/di/*.php` (`application.php`, `http.php`, `router.php`) |
| Requires | PHP ^8.2, `blackcube/dboard` ^1.0, `blackcube/assets` ^1.0, `httpsoft/http-message` ^1.1, `yiisoft/config` ^1.6, `csrf` ^2.2, `data-response` ^2.2, `log` ^2.2 + `log-target-file` ^3.1, `rbac-db` ^2.1, `request-provider` ^1.3, `session` ^3.0, `yii-runner-http` ^3.2, `yii-view-renderer` ^7.4 (`composer.json:27-42`) |
| License | BSD-3-Clause |

## Rules

- One entry point: `DboardBridge::handle()` builds a `HttpApplicationRunner` with `rootPath`, `debug`, `checkEvents: false`, `configDirectory`, and returns `runAndGetResponse($request)` (`src/DboardBridge.php:50-57`). Nothing is cached between calls: each request boots the Yii3 container from the config directory.
- The host project owns the configuration directory (`dboard/` in the README, `configuration.php` with `di` and `di-web` groups, `README.md:39-64`): the database connection, the `blackcube/dboard` params (route prefix, OAuth2 keys, WebAuthn) and whatever dboard leaves to the application (`blackcube/dboard` `llm-doc/integration.md`). The package config only fills the gaps listed in `bridge.md`.
- Pass the PSR-7 request when the host framework has already read `php://input` (Laravel, Symfony); Slim can let the runner read the request itself (`src/DboardBridge.php:27-28`, `README.md:147`).
- The middleware stack of the sub-application is fixed in `config/web/di/application.php:29-38`: `ErrorCatcher`, `SessionMiddleware`, `MultiVerbParserMiddleware` (dboard), `ContentNegotiatorDataResponseMiddleware`, `RequestCatcherMiddleware`, `Router`; the not-found fallback is the yiisoft `NotFoundHandler`. CSRF is enforced by the dboard routes group itself.
- The routes are the ones the config-plugin `routes` group collects, dboard's included (`config/web/di/router.php:20-27`); the host mounts the sub-application on the dboard route prefix (`/dboard[/{path:.*}]`, `README.md:76`).

## Parts

| File | Content | Read when |
|---|---|---|
| `bridge.md` | The call, the request flow, what each config file binds, the host-side mounting on Slim and Laravel. | before mounting the panel |
| `integration.md` | What the host configuration directory must contain, the CSRF and cookie exemptions. | before plugging the package in |
| `extension.md` | What extends and what does not. | before extending |
