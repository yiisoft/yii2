<?php

namespace yiiunit\framework\web\session\mysql;

use yii\db\Connection;

/**
 * Class DbSessionTest
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 *
 * @group db
 * @group mysql
 */
class DbSessionTest extends \yiiunit\framework\web\session\DbSessionTest
{
    protected function getDriverName()
    {
        return 'mysql';
    }
}
