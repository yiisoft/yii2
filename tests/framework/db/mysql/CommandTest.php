<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mysql;

use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Group;
use yii\db\ConstraintFinderInterface;
use yiiunit\base\db\BaseCommand;
use yiiunit\framework\db\mysql\providers\CommandProvider;
use yiiunit\support\DbHelper;

/**
 * Unit tests for {@see \yii\db\Command} functionality for the MySQL driver.
 *
 * {@see CommandProvider} for test case data providers.
 */
#[Group('db')]
#[Group('mysql')]
#[Group('command')]
final class CommandTest extends BaseCommand
{
    public $driverName = 'mysql';

    protected $upsertTestCharCast = 'CONVERT([[address]], CHAR)';

    #[DataProviderExternal(CommandProvider::class, 'addCommentOnColumn')]
    public function testAddUpdateDropCommentOnColumn(string $tableName, string $commentTarget, string $columnName): void
    {
        $db = $this->getConnection(false);

        $schema = $db->getSchema();

        DbHelper::dropTablesIfExist($db, [$tableName]);

        $db->createCommand()->createTable(
            $tableName,
            [
                'id' => 'integer',
                $columnName => 'string',
            ],
        )->execute();
        $db->createCommand()->addCommentOnColumn(
            $commentTarget,
            $columnName,
            'Initial column comment.',
        )->execute();

        self::assertSame(
            'Initial column comment.',
            $schema->getTableSchema($tableName, true)->getColumn($columnName)->comment,
            'Column comment must be created.',
        );

        $db->createCommand()->addCommentOnColumn(
            $commentTarget,
            $columnName,
            'Updated column comment.',
        )->execute();

        self::assertSame(
            'Updated column comment.',
            $schema->getTableSchema($tableName, true)->getColumn($columnName)->comment,
            'Column comment must be updated.',
        );

        $db->createCommand()->dropCommentFromColumn(
            $commentTarget,
            $columnName,
        )->execute();

        self::assertEmpty(
            $schema->getTableSchema($tableName, true)->getColumn($columnName)->comment,
            'Column comment must be removed.',
        );

        DbHelper::dropTablesIfExist($db, [$tableName]);
    }

    public function testAddDropCheckSeveral(): void
    {
        $db = $this->getConnection(false);

        $tableName = 'test_ck_several';
        $schema = $db->getSchema();
        $this->assertInstanceOf(ConstraintFinderInterface::class, $schema);

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
