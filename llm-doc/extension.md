# dboard-bridge — Extension

## Extension points

| Point | Where | Use |
|---|---|---|
| Everything the sub-application binds | the host configuration directory: a `di` / `di-web` / `params` file of the host is merged after the package ones by `yiisoft/config`, so a host definition replaces a package one | another logger, another RBAC storage, another PSR-17 implementation, another middleware stack |
| Config directory and debug | the `configDirectory` and `debug` arguments of `handle()` | several panels, environment switch |
| The request | the `request` argument: any PSR-7 server request, however the host built it | frameworks that already consumed `php://input` |
| Mount point | the host route: any prefix, as long as it equals the `routePrefix` param of dboard | `/admin` instead of `/dboard` |
| Routes of the sub-application | the config-plugin `routes` group: dboard's, the addons', and any host `routes` file in the directory | extra admin routes |

## Not extensible

| Element | Reason |
|---|---|
| The runner | `HttpApplicationRunner` constructed inside `handle()` with `checkEvents: false`; no hook to pass another runner or extra bootstrap (`src/DboardBridge.php:50-55`) |
| Per-request boot | the container is built on every call; no shared kernel between requests |
| Response type | PSR-7 `ResponseInterface` only; the conversion to the host response type is host code (`README.md:118-124`) |
| Middleware order | `config/web/di/application.php:29-38`, replaceable as a whole by a host definition, not editable item by item |
