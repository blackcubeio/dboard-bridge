<?php

declare(strict_types=1);

namespace Blackcube\DboardBridge;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Yii\Runner\Http\HttpApplicationRunner;

/**
 * Dboard bridge for non-Yii3 frameworks.
 *
 * Boots a Yii3 sub-application to handle dboard admin routes.
 * The host project must provide its own configuration.php,
 * params (DB, OAuth2) and DI (db connection) in the configDirectory.
 *
 * Returns a PSR-7 ResponseInterface. The host framework converts
 * it to its own response type if needed.
 *
 * When running behind another framework, pass $request to avoid
 * php://input being consumed before Yii3 can read it.
 */
final class DboardBridge
{
    /**
     * Boot the Yii3 sub-application and return the response.
     *
     * @param string $rootPath Project root path
     * @param bool $debug Debug mode
     * @param string $configDirectory Config directory relative to rootPath
     * @param ServerRequestInterface|null $request PSR-7 request (recommended when host framework consumes php://input)
     */
    public static function handle(
        string $rootPath,
        bool $debug = false,
        string $configDirectory = 'config',
        ?ServerRequestInterface $request = null,
    ): ResponseInterface {
        $runner = new HttpApplicationRunner(
            rootPath: $rootPath,
            debug: $debug,
            checkEvents: false,
            configDirectory: $configDirectory,
        );

        return $runner->runAndGetResponse($request);
    }
}
