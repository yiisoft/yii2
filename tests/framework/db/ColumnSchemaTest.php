<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db;


use yii\db\ColumnSchema;
use yii\db\Schema;
use yiiunit\TestCase;

/**
 * ColumnSchemaTest tests ColumnSchema
 */
class ColumnSchemaTest extends TestCase
{
    public function testDbTypecastWithEmptyCharType()
    {
        $columnSchema = new ColumnSchema(['type' => Schema::TYPE_CHAR]);
        $this->assertSame('', $columnSchema->dbTypecast(''));
    }
}
