<?php

namespace yiiunit\framework\web\session\pgsql;

/**
 * Class DbSessionTest
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 *
 * @group db
 * @group pgsql
 */
class DbSessionTest extends \yiiunit\framework\web\session\AbstractDbSessionTest
{
    protected function getDriverNames()
    {
        return ['pgsql'];
    }
}
