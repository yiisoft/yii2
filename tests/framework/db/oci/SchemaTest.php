<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\oci;

use yii\db\CheckConstraint;
use yiiunit\framework\db\AnyValue;

/**
 * @group db
 * @group oci
 */
class SchemaTest extends \yiiunit\framework\db\SchemaTest
{
    public $driverName = 'oci';

    protected $expectedSchemas = [];

    public function getExpectedColumns()
    {
        $columns = parent::getExpectedColumns();
        unset($columns['enum_col']);
        unset($columns['json_col']);
        $columns['int_col']['dbType'] = 'NUMBER';
        $columns['int_col']['size'] = 22;
        $columns['int_col']['precision'] = null;
        $columns['int_col']['scale'] = 0;
        $columns['int_col2']['dbType'] = 'NUMBER';
        $columns['int_col2']['size'] = 22;
        $columns['int_col2']['precision'] = null;
        $columns['int_col2']['scale'] = 0;
        $columns['tinyint_col']['dbType'] = 'NUMBER';
        $columns['tinyint_col']['type'] = 'integer';
        $columns['tinyint_col']['size'] = 22;
        $columns['tinyint_col']['precision'] = 3;
        $columns['tinyint_col']['scale'] = 0;
        $columns['smallint_col']['dbType'] = 'NUMBER';
        $columns['smallint_col']['type'] = 'integer';
        $columns['smallint_col']['size'] = 22;
        $columns['smallint_col']['precision'] = null;
        $columns['smallint_col']['scale'] = 0;
        $columns['char_col']['type'] = 'string';
        $columns['char_col']['dbType'] = 'CHAR';
        $columns['char_col']['precision'] = null;
        $columns['char_col']['size'] = 100;
        $columns['char_col2']['dbType'] = 'VARCHAR2';
        $columns['char_col2']['precision'] = null;
        $columns['char_col2']['size'] = 100;
        $columns['char_col3']['type'] = 'string';
        $columns['char_col3']['dbType'] = 'VARCHAR2';
        $columns['char_col3']['precision'] = null;
        $columns['char_col3']['size'] = 4000;
        $columns['float_col']['dbType'] = 'FLOAT';
        $columns['float_col']['precision'] = 126;
        $columns['float_col']['scale'] = null;
        $columns['float_col']['size'] = 22;
        $columns['float_col2']['dbType'] = 'FLOAT';
        $columns['float_col2']['precision'] = 126;
        $columns['float_col2']['scale'] = null;
        $columns['float_col2']['size'] = 22;
        $columns['blob_col']['dbType'] = 'BLOB';
        $columns['blob_col']['phpType'] = 'resource';
        $columns['blob_col']['type'] = 'binary';
        $columns['blob_col']['size'] = 4000;
        $columns['numeric_col']['dbType'] = 'NUMBER';
        $columns['numeric_col']['size'] = 22;
        $columns['time']['dbType'] = 'TIMESTAMP(6)';
        $columns['time']['size'] = 11;
        $columns['time']['scale'] = 6;
        $columns['time']['defaultValue'] = null;
        $columns['bool_col']['type'] = 'string';
        $columns['bool_col']['phpType'] = 'string';
        $columns['bool_col']['dbType'] = 'CHAR';
        $columns['bool_col']['size'] = 1;
        $columns['bool_col']['precision'] = null;
        $columns['bool_col2']['type'] = 'string';
        $columns['bool_col2']['phpType'] = 'string';
        $columns['bool_col2']['dbType'] = 'CHAR';
        $columns['bool_col2']['size'] = 1;
        $columns['bool_col2']['precision'] = null;
        $columns['bool_col2']['defaultValue'] = '1';
        $columns['ts_default']['type'] = 'timestamp';
        $columns['ts_default']['phpType'] = 'string';
        $columns['ts_default']['dbType'] = 'TIMESTAMP(6)';
        $columns['ts_default']['scale'] = 6;
        $columns['ts_default']['size'] = 11;
        $columns['ts_default']['defaultValue'] = null;
        $columns['bit_col']['type'] = 'string';
        $columns['bit_col']['phpType'] = 'string';
        $columns['bit_col']['dbType'] = 'CHAR';
        $columns['bit_col']['size'] = 3;
        $columns['bit_col']['precision'] = null;
        $columns['bit_col']['defaultValue'] = '130';
        return $columns;
    }

    /**
     * Autoincrement columns detection should be disabled for Oracle
     * because there is no way of associating a column with a sequence.
     */
    public function testAutoincrementDisabled()
    {
        $table = $this->getConnection(false)->schema->getTableSchema('order', true);
        $this->assertFalse($table->columns['id']->autoIncrement);
    }

    public function constraintsProvider()
    {
        $result = parent::constraintsProvider();
        $result['1: check'][2][0]->expression = '"C_check" <> \'\'';
        $result['1: check'][2][] = new CheckConstraint([
            'name' => AnyValue::getInstance(),
            'columnNames' => ['C_id'],
            'expression' => '"C_id" IS NOT NULL',
        ]);
        $result['1: check'][2][] = new CheckConstraint([
            'name' => AnyValue::getInstance(),
            'columnNames' => ['C_not_null'],
            'expression' => '"C_not_null" IS NOT NULL',
        ]);
        $result['1: check'][2][] = new CheckConstraint([
            'name' => AnyValue::getInstance(),
            'columnNames' => ['C_unique'],
            'expression' => '"C_unique" IS NOT NULL',
        ]);
        $result['1: check'][2][] = new CheckConstraint([
            'name' => AnyValue::getInstance(),
            'columnNames' => ['C_default'],
            'expression' => '"C_default" IS NOT NULL',
        ]);

        $result['2: check'][2][] = new CheckConstraint([
            'name' => AnyValue::getInstance(),
            'columnNames' => ['C_id_1'],
            'expression' => '"C_id_1" IS NOT NULL',
        ]);
        $result['2: check'][2][] = new CheckConstraint([
            'name' => AnyValue::getInstance(),
            'columnNames' => ['C_id_2'],
            'expression' => '"C_id_2" IS NOT NULL',
        ]);

        $result['3: foreign key'][2][0]->foreignSchemaName = AnyValue::getInstance();
        $result['3: foreign key'][2][0]->onUpdate = null;
        $result['3: index'][2] = [];
        $result['3: check'][2][] = new CheckConstraint([
            'name' => AnyValue::getInstance(),
            'columnNames' => ['C_fk_id_1'],
            'expression' => '"C_fk_id_1" IS NOT NULL',
        ]);
        $result['3: check'][2][] = new CheckConstraint([
            'name' => AnyValue::getInstance(),
            'columnNames' => ['C_fk_id_2'],
            'expression' => '"C_fk_id_2" IS NOT NULL',
        ]);
        $result['3: check'][2][] = new CheckConstraint([
            'name' => AnyValue::getInstance(),
            'columnNames' => ['C_id'],
            'expression' => '"C_id" IS NOT NULL',
        ]);

        $result['4: check'][2][] = new CheckConstraint([
            'name' => AnyValue::getInstance(),
            'columnNames' => ['C_id'],
            'expression' => '"C_id" IS NOT NULL',
        ]);
        $result['4: check'][2][] = new CheckConstraint([
            'name' => AnyValue::getInstance(),
            'columnNames' => ['C_col_2'],
            'expression' => '"C_col_2" IS NOT NULL',
        ]);
        return $result;
    }

    public function testFindUniqueIndexes()
    {
        if ($this->driverName === 'sqlsrv') {
            $this->markTestSkipped('`\yii\db\mssql\Schema::findUniqueIndexes()` returns only unique constraints not unique indexes.');
        }

        $db = $this->getConnection();

        try {
            $db->createCommand()->dropTable('uniqueIndex')->execute();
        } catch (\Exception $e) {
        }
        $db->createCommand()->createTable('uniqueIndex', [
            'somecol' => 'string',
            'someCol2' => 'string',
            'someCol3' => 'string',
        ])->execute();

        /* @var $schema Schema */
        $schema = $db->schema;

        $uniqueIndexes = $schema->findUniqueIndexes($schema->getTableSchema('uniqueIndex', true));
        $this->assertEquals([], $uniqueIndexes);

        $db->createCommand()->createIndex('somecolUnique', 'uniqueIndex', 'somecol', true)->execute();

        $uniqueIndexes = $schema->findUniqueIndexes($schema->getTableSchema('uniqueIndex', true));
        $this->assertEquals([
            'somecolUnique' => ['somecol'],
        ], $uniqueIndexes);

        // create another column with upper case letter that fails postgres
        // see https://github.com/yiisoft/yii2/issues/10613
        $db->createCommand()->createIndex('someCol2Unique', 'uniqueIndex', 'someCol2', true)->execute();

        $uniqueIndexes = $schema->findUniqueIndexes($schema->getTableSchema('uniqueIndex', true));
        $this->assertEquals([
            'somecolUnique' => ['somecol'],
            'someCol2Unique' => ['someCol2'],
        ], $uniqueIndexes);

        // see https://github.com/yiisoft/yii2/issues/13814
        $db->createCommand()->createIndex('another unique index', 'uniqueIndex', 'someCol3', true)->execute();

        $uniqueIndexes = $schema->findUniqueIndexes($schema->getTableSchema('uniqueIndex', true));
        $this->assertEquals([
            'somecolUnique' => ['somecol'],
            'someCol2Unique' => ['someCol2'],
            'another unique index' => ['someCol3'],
        ], $uniqueIndexes);
    }

    public function testCompositeFk()
    {
        $this->markTestSkipped('Should be fixed.');
    }
}
