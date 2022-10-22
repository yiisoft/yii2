<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web\session\pgsql;

/**
 * Class DbSessionTest.
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 *
 * @group db
 * @group pgsql
 */
class DbSessionTest extends \yiiunit\framework\web\session\AbstractDbSessionTest
{
    protected function setUp()
    {
        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped('HHVMs PgSQL implementation does not seem to support blob columns in the way they are used here.');
        }

        parent::setUp();
    }

    protected function getDriverNames()
    {
        return ['pgsql'];
    }
}
