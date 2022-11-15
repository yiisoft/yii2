<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\tests\unit\framework\db\pgsql;

use yii\db\ArrayExpression;
use yii\db\JsonExpression;

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

    public function dataProviderGetRawSql()
    {
        return array_merge(parent::dataProviderGetRawSql(), [
            [
                'SELECT * FROM customer WHERE id::integer IN (:in, :out)',
                [':in' => 1, ':out' => 2],
                'SELECT * FROM customer WHERE id::integer IN (1, 2)',
            ],
        ]);
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/11498
     */
    public function testSaveSerializedObject()
    {
        if (\defined('HHVM_VERSION')) {
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

    public function batchInsertSqlProvider()
    {
        $data = parent::batchInsertSqlProvider();
        $data['issue11242']['expected'] = 'INSERT INTO "type" ("int_col", "float_col", "char_col") VALUES (NULL, NULL, \'Kyiv {{city}}, Ukraine\')';
        $data['wrongBehavior']['expected'] = 'INSERT INTO "type" ("type"."int_col", "float_col", "char_col") VALUES (\'\', \'\', \'Kyiv {{city}}, Ukraine\')';
        $data['batchInsert binds params from expression']['expected'] = 'INSERT INTO "type" ("int_col") VALUES (:qp1)';
        $data['batchInsert binds params from jsonExpression'] = [
            '{{%type}}',
            ['json_col'],
            [[new JsonExpression(['username' => 'silverfire', 'is_active' => true, 'langs' => ['Ukrainian', 'Russian', 'English']])]],
            'expected' => 'INSERT INTO "type" ("json_col") VALUES (:qp0)',
            'expectedParams' => [':qp0' => '{"username":"silverfire","is_active":true,"langs":["Ukrainian","Russian","English"]}']
        ];
        $data['batchInsert binds params from arrayExpression'] = [
            '{{%type}}',
            ['intarray_col'],
            [[new ArrayExpression([1,null,3], 'int')]],
            'expected' => 'INSERT INTO "type" ("intarray_col") VALUES (ARRAY[:qp0, :qp1, :qp2]::int[])',
            'expectedParams' => [':qp0' => 1, ':qp1' => null, ':qp2' => 3]
        ];
        $data['batchInsert casts string to int according to the table schema'] = [
            '{{%type}}',
            ['int_col'],
            [['3']],
            'expected' => 'INSERT INTO "type" ("int_col") VALUES (3)',
        ];
        $data['batchInsert casts JSON to JSONB when column is JSONB'] = [
            '{{%type}}',
            ['jsonb_col'],
            [[['a' => true]]],
            'expected' => 'INSERT INTO "type" ("jsonb_col") VALUES (:qp0::jsonb)',
            'expectedParams' => [':qp0' => '{"a":true}']
        ];

        return $data;
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/15827
     */
    public function testIssue15827()
    {
        $db = $this->getConnection();

        $inserted = $db->createCommand()->insert('array_and_json_types', [
            'jsonb_col' => new JsonExpression(['Solution date' => '13.01.2011'])
        ])->execute();
        $this->assertSame(1, $inserted);


        $found = $db->createCommand(<<<PGSQL
            SELECT *
            FROM array_and_json_types
            WHERE jsonb_col @> '{"Some not existing key": "random value"}'
PGSQL
        )->execute();
        $this->assertSame(0, $found);

        $found = $db->createCommand(<<<PGSQL
            SELECT *
            FROM array_and_json_types
            WHERE jsonb_col @> '{"Solution date": "13.01.2011"}'
PGSQL
        )->execute();
        $this->assertSame(1, $found);


        $this->assertSame(1, $db->createCommand()->delete('array_and_json_types')->execute());
    }
}
