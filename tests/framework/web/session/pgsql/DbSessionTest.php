<?php

namespace yiiunit\framework\web\session\pgsql;

use yii\db\Connection;

/**
 * Class DbSessionTest
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 *
 * @group db
 * @group pgsql
 */
class DbSessionTest extends \yiiunit\framework\web\session\DbSessionTest
{
    protected function getDriverName()
    {
        return 'pgsql';
    }
}
