<?php

namespace console\tests\unit;

/**
 * @inheritdoc
 */
class DbTestCase extends \yii\codeception\DbTestCase
{
    public $appConfig = '@console/tests/unit/_config.php';
}
