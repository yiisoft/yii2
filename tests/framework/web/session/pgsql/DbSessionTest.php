<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web\session\pgsql;

use yiiunit\base\web\session\BaseDbSession;

/**
 * Class DbSessionTest.
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 *
 * @group db
 * @group db-session
 * @group pgsql
 */
class DbSessionTest extends BaseDbSession
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getDriverNames()
    {
        return ['pgsql'];
    }
}
