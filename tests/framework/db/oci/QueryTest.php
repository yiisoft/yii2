<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\oci;

/**
 * @group db
 * @group oci
 */
class QueryTest extends \yiiunit\framework\db\QueryTest
{
    protected $driverName = 'oci';

    public function testUnion()
    {
        $this->markTestSkipped('Unsupported use of WITH clause in Oracle.');
    }
}
