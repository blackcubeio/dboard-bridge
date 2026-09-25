<?php

declare(strict_types=1);

/**
 * rbac.php
 *
 * PHP Version 8.4
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

use Yiisoft\Rbac\AssignmentsStorageInterface;
use Yiisoft\Rbac\Db\AssignmentsStorage;
use Yiisoft\Rbac\Db\ItemsStorage;
use Yiisoft\Rbac\ItemsStorageInterface;

return [
    ItemsStorageInterface::class => ItemsStorage::class,
    AssignmentsStorageInterface::class => AssignmentsStorage::class,
];
