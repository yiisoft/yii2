<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

/**
 * Production-mode bootstrap used by the dedicated PHPUnit job that exercises code paths guarded by `YII_DEBUG = false`.
 *
 * Defines `YII_DEBUG = false` BEFORE delegating to the standard bootstrap so the conditional `defined() or define()` in
 * tests/bootstrap.php preserves the override. Only tests tagged with the `prod` group are expected to run under this
 * bootstrap.
 */
if (!defined('YII_DEBUG')) {
    define('YII_DEBUG', false);
} elseif (YII_DEBUG !== false) {
    throw new RuntimeException('tests/bootstrap-prod.php requires YII_DEBUG=false; got ' . var_export(YII_DEBUG, true) . '.');
}

require __DIR__ . '/bootstrap.php';
