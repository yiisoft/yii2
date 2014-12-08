<?php
namespace yiiunit\extensions\mongodb\console\controllers;

use yii\mongodb\console\controllers\MigrateController;

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