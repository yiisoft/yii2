<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\base\stub\module;

/**
 * Class located in the controller namespace whose name resolves to `PlainClassController` but does not extend
 * {@see \yii\base\Controller}, used to exercise the negative branch of {@see \yii\base\Module::createControllerByID()}
 * under `YII_DEBUG = true`.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class PlainClassController
{
}
