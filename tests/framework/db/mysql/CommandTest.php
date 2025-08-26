<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mysql;

/**
 * @group db
 * @group mysql
 */
class CommandTest extends \yiiunit\framework\db\CommandTest
{
    public $driverName = 'mysql';

    protected $upsertTestCharCast = 'CONVERT([[address]], CHAR)';

    public function testAddDropCheckSeveral()
    {
        $db = $this->getConnection(false);

        if (version_compare($db->getServerVersion(), '8.0.16', '<')) {
            $this->markTestSkipped('MySQL < 8.0.16 does not support CHECK constraints.');
        }

        $tableName = 'test_ck_several';
        $schema = $db->getSchema();

        if ($schema->getTableSchema($tableName) !== null) {
            $db->createCommand()->dropTable($tableName)->execute();
        }
        $db->createCommand()->createTable($tableName, [
            'int1' => 'integer',
            'int2' => 'integer',
            'int3' => 'integer',
            'int4' => 'integer',
        ])->execute();

        $this->assertEmpty($schema->getTableChecks($tableName, true));

        $constraints = [
            ['name' => 'check_int1_positive', 'expression' => '[[int1]] > 0', 'expected' => '(`int1` > 0)'],
            ['name' => 'check_int2_nonzero', 'expression' => '[[int2]] <> 0', 'expected' => '(`int2` <> 0)'],
            ['name' => 'check_int3_less_than_100', 'expression' => '[[int3]] < 100', 'expected' => '(`int3` < 100)'],
            ['name' => 'check_int1_less_than_int2', 'expression' => '[[int1]] < [[int2]]', 'expected' => '(`int1` < `int2`)'],
        ];

        if (\stripos($db->getServerVersion(), 'MariaDb') !== false) {
            $constraints[0]['expected'] = '`int1` > 0';
            $constraints[1]['expected'] = '`int2` <> 0';
            $constraints[2]['expected'] = '`int3` < 100';
            $constraints[3]['expected'] = '`int1` < `int2`';
        }

        foreach ($constraints as $constraint) {
            $db->createCommand()->addCheck($constraint['name'], $tableName, $constraint['expression'])->execute();
        }

        $tableChecks = $schema->getTableChecks($tableName, true);
        $this->assertCount(4, $tableChecks);

        foreach ($constraints as $index => $constraint) {
            $this->assertSame(
                $constraints[$index]['expected'],
                $tableChecks[$index]->expression
            );
        }

        foreach ($constraints as $constraint) {
            $db->createCommand()->dropCheck($constraint['name'], $tableName)->execute();
        }

        $this->assertEmpty($schema->getTableChecks($tableName, true));
    }
}
