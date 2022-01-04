<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\data;

use yii\data\ActiveDataProvider;
use yii\db\Query;
use yiiunit\TestCase;

class ActiveDataProviderCloningTest extends TestCase
{
    public function testClone()
    {
        $queryFirst = new Query();

        $dataProviderFirst = new ActiveDataProvider([
            'query' => $queryFirst
        ]);

        $dataProviderSecond = clone $dataProviderFirst;

        $querySecond = $dataProviderSecond->query;

        $this->assertNotSame($querySecond, $queryFirst);
    }
}
