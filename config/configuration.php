<?php

declare(strict_types=1);

return [
    'config-plugin' => [
        'di' => 'common/di/*.php',
        'di-web' => [
            '$di',
            'web/di/*.php',
        ],
    ],
    'config-plugin-options' => [
        'source-directory' => 'config',
    ],
];
