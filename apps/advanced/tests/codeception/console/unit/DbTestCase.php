<?php

namespace console\tests\unit;

/**
 * @inheritdoc
 */
class DbTestCase extends \yii\codeception\DbTestCase
{
    public $appConfig = '@codeception/config/console/config.php';
}
