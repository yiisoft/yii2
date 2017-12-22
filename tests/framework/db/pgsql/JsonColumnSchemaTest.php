<?php

namespace yii\db\pgsql;

use yiiunit\TestCase;

class JsonColumnSchemaTest extends TestCase
{
    protected $driverName = 'pgsql';

    protected function setUp()
    {
        parent::setUp();

        $this->columnSchema = new ColumnSchema([
            'name' => 'json',
            'allowNull' => true,
            'type' => Schema::TYPE_JSON,
            'phpType' => 'array',
            'dbType' => 'jsonb',
            'defaultValue' => NULL,
            'enumValues' => NULL,
            'size' => NULL,
            'precision' => NULL,
            'scale' => NULL,
            'isPrimaryKey' => false,
            'unsigned' => false,
            'comment' => NULL,
            'dimension' => 0,
        ]);
    }
}
