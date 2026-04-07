<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\oci;

use yiiunit\base\db\BaseQuery;

/**
 * @group db
 * @group oci
 */
class QueryTest extends BaseQuery
{
    protected $driverName = 'oci';

    public function testUnion(): void
    {
        $this->markTestSkipped('Unsupported use of WITH clause in Oracle.');
    }
}
