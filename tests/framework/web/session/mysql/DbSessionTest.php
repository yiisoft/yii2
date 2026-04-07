<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web\session\mysql;

use yiiunit\base\web\session\BaseDbSession;

/**
 * Class DbSessionTest.
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 *
 * @group db
 * @group db-session
 * @group mysql
 */
class DbSessionTest extends BaseDbSession
{
    protected function getDriverNames()
    {
        return ['mysql'];
    }
}
