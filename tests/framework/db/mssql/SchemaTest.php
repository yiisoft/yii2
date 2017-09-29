<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mssql;

use yii\db\DefaultValueConstraint;
use yiiunit\framework\db\AnyValue;

/**
 * @group db
 * @group mssql
 */
class SchemaTest extends \yiiunit\framework\db\SchemaTest
{
    public $driverName = 'sqlsrv';

    public function constraintsProvider()
    {
        $result = parent::constraintsProvider();
        $result['1: check'][2][0]->expression = '([C_check]<>\'\')';
        $result['1: default'][2] = [];
        $result['1: default'][2][] = new DefaultValueConstraint([
            'name' => AnyValue::getInstance(),
            'columnNames' => ['C_default'],
            'value' => '((0))',
        ]);

        $result['2: default'][2] = [];

        $result['3: foreign key'][2][0]->foreignSchemaName = 'dbo';
        $result['3: index'][2] = [];
        $result['3: default'][2] = [];

        $result['4: default'][2] = [];
        return $result;
    }
}
