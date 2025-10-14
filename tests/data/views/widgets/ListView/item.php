<?php

use yii\widgets\ListView;

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 *
 * @var array|object $model
 * @var string $key
 * @var int $index
 * @var ListView $widget
 */
echo "Item #{$index}: {$model['login']} - Widget: " . $widget->className();
