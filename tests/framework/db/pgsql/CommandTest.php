<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\tests\unit\framework\db\pgsql;

/**
 * @group db
 * @group pgsql
 */
class CommandTest extends \yiiunit\framework\db\CommandTest
{
    public $driverName = 'pgsql';

    public function testAutoQuoting()
    {
        $db = $this->getConnection(false);

        $sql = 'SELECT [[id]], [[t.name]] FROM {{customer}} t';
        $command = $db->createCommand($sql);
        $this->assertEquals('SELECT "id", "t"."name" FROM "customer" t', $command->sql);
    }

    public function testBooleanValuesInsert()
    {
        $db = $this->getConnection();
        $command = $db->createCommand();
        $command->insert('bool_values', ['bool_col' => true]);
        $this->assertEquals(1, $command->execute());

        $command = $db->createCommand();
        $command->insert('bool_values', ['bool_col' => false]);
        $this->assertEquals(1, $command->execute());

        $command = $db->createCommand('SELECT COUNT(*) FROM "bool_values" WHERE bool_col = TRUE;');
        $this->assertEquals(1, $command->queryScalar());
        $command = $db->createCommand('SELECT COUNT(*) FROM "bool_values" WHERE bool_col = FALSE;');
        $this->assertEquals(1, $command->queryScalar());
    }

    public function testBooleanValuesBatchInsert()
    {
        $db = $this->getConnection();
        $command = $db->createCommand();
        $command->batchInsert('bool_values',
            ['bool_col'], [
                [true],
                [false],
            ]
        );
        $this->assertEquals(2, $command->execute());

        $command = $db->createCommand('SELECT COUNT(*) FROM "bool_values" WHERE bool_col = TRUE;');
        $this->assertEquals(1, $command->queryScalar());
        $command = $db->createCommand('SELECT COUNT(*) FROM "bool_values" WHERE bool_col = FALSE;');
        $this->assertEquals(1, $command->queryScalar());
    }

    public function testLastInsertId()
    {
        $db = $this->getConnection();

        $sql = 'INSERT INTO {{profile}}([[description]]) VALUES (\'non duplicate\')';
        $command = $db->createCommand($sql);
        $command->execute();
        $this->assertEquals(3, $db->getSchema()->getLastInsertID('public.profile_id_seq'));

        $sql = 'INSERT INTO {{schema1.profile}}([[description]]) VALUES (\'non duplicate\')';
        $command = $db->createCommand($sql);
        $command->execute();
        $this->assertEquals(3, $db->getSchema()->getLastInsertID('schema1.profile_id_seq'));
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/11498
     */
    public function testSaveSerializedObject()
    {
        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped('HHVMs PgSQL implementation does not seem to support blob colums in the way they are used here.');
        }

        $db = $this->getConnection();

        $command = $db->createCommand()->insert('type', [
            'int_col' => 1,
            'char_col' => 'serialize',
            'float_col' => 5.6,
            'bool_col' => true,
            'blob_col' => serialize($db),
        ]);
        $this->assertEquals(1, $command->execute());

        $command = $db->createCommand()->update('type', [
            'blob_col' => serialize($db),
        ], ['char_col' => 'serialize']);
        $this->assertEquals(1, $command->execute());
    }
}
