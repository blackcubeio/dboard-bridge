# dboard-bridge — Integration

## Split

- Package: the entry point and the Yii3 plumbing a non-Yii3 host lacks (HTTP factories, router, middleware stack, file logger, RBAC database storages).
- Host project: the configuration directory read by the runner, the database connection, the dboard parameters and the pieces dboard leaves to the application, the mount on its own router, the CSRF and cookie exemptions, the asset publication directory.

## Configuration directory

`composer require blackcube/dboard-bridge` pulls `blackcube/dboard` and its dependencies. The host creates a directory at its root (`dboard/` in the README) with a `configuration.php` declaring the groups the runner reads, `source-directory` set to that directory (`README.md:50-61`):

```php
return [
    'config-plugin' => [
        'di' => 'common/di/*.php',
        'di-web' => ['$di', 'web/di/*.php'],
    ],
    'config-plugin-options' => ['source-directory' => 'dboard'],
];
```

In that directory the host adds what the sub-application needs and the two packages do not bind:

| Need | Where it comes from |
|---|---|
| Database connection (`ConnectionInterface`) | host `di`, the same credentials as the host application |
| `blackcube/dboard` params: `routePrefix`, `oauth2` key paths, `webauthn`, `rbacClasses`, `addons` | host `params` (defaults in `blackcube/dboard` `config/common/params.php`) |
| RBAC `ManagerInterface` (direct permissions, `CachedRbacManager`) | host `di` (`blackcube/dboard` `llm-doc/rbac.md`) |
| `yiisoft/yii-view-renderer` injections (`ViewInjection`), translator sources (`Dboard::getI18nSources()`), `yiisoft/db-migration` namespace | host `params` / `di` (`blackcube/dboard` `llm-doc/integration.md`) |
| Asset manager `@assets` / `@assetsUrl` aliases and the published directory served by the host web server | host `params` for `yiisoft/assets` and `yiisoft/aliases` |
| Session (`SessionInterface`) | `yiisoft/session` is required and `SessionMiddleware` is in the stack; cookie settings in host `params` |

The migrations run through the Yii3 console of the host configuration or from a Yii3 tooling checkout: the bridge ships no console runner.

## Host-side exemptions

Laravel: `validateCsrfTokens(except: ['dboard/*', 'dboard'])` and `encryptCookies(except: ['PHPSESSID', 'access_token', '_csrf'])` (`README.md:131-134`). Any framework encrypting or signing cookies needs the same exemptions, and must not consume the request body before `handle()` unless it passes the request.

## Tests

None in the package: the bridge is validated by mounting it in a host application and driving the admin panel.
