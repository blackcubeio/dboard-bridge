<?php

declare(strict_types=1);

/**
 * application.php
 *
 * PHP Version 8.4
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Blackcube\Dboard\Middlewares\MultiVerbParserMiddleware;
use Yiisoft\DataResponse\Middleware\ContentNegotiatorDataResponseMiddleware;
use Yiisoft\Definitions\DynamicReference;
use Yiisoft\Definitions\Reference;
use Yiisoft\ErrorHandler\Middleware\ErrorCatcher;
use Yiisoft\Middleware\Dispatcher\MiddlewareDispatcher;
use Yiisoft\RequestProvider\RequestCatcherMiddleware;
use Yiisoft\Router\Middleware\Router;
use Yiisoft\Session\SessionMiddleware;
use Yiisoft\Yii\Http\Application;
use Yiisoft\Yii\Http\Handler\NotFoundHandler;

return [
    Application::class => [
        '__construct()' => [
            'fallbackHandler' => Reference::to(NotFoundHandler::class),
            'dispatcher' => DynamicReference::to([
                'class' => MiddlewareDispatcher::class,
                'withMiddlewares()' => [
                    [
                        ErrorCatcher::class,
                        SessionMiddleware::class,
                        MultiVerbParserMiddleware::class,
                        ContentNegotiatorDataResponseMiddleware::class,
                        RequestCatcherMiddleware::class,
                        Router::class,
                    ],
                ],
            ]),
        ],
    ],
];
