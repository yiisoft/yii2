<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\imagine;

/**
 * Image implements most commonly used image manipulation functions using the [Imagine library](http://imagine.readthedocs.org/).
 *
 * Example of use:
 *
 * ~~~php
 * // thumb - saved on runtime path
 * $imagePath = Yii::$app->getBasePath() . '/web/img/test-image.jpg';
 * $runtimePath = Yii::$app->getRuntimePath();
 * Image::thumb('@app/web/img/test-image.jpg', 120, 120)
 *     ->save('@runtime/thumb-test-image.jpg', ['quality' => 50]);
 * ~~~
 *
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Image extends BaseImage
{
}
