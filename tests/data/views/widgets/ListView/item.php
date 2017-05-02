<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * @var array|object $model
 * @var string $key
 * @var int $index
 * @var \yii\widgets\ListView $widget
 */

echo "Item #{$index}: {$model['login']} - Widget: " . $widget->className();
