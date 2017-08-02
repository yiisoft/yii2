<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\console\controllers;

use yii\console\controllers\MigrateController;

/**
 * MigrateController that writes output via echo instead of using output stream. Allows us to buffer it.
 */
class EchoMigrateController extends MigrateController
{
    /**
     * @inheritdoc
     */
    public function stdout($string)
    {
        echo $string;
    }
}
