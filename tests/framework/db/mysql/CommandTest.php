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
            'It\'s an initial comment.',
        )->execute();

        self::assertSame(
            'It\'s an initial comment.',
            $schema->getTableSchema($tableName, true)->getColumn($columnName)->comment,
            'Column comment must be created.',
        );

        $db->createCommand()->addCommentOnColumn(
            $commentTarget,
            $columnName,
            'It\'s an updated comment.',
        )->execute();

        self::assertSame(
            'It\'s an updated comment.',
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

    #[DataProviderExternal(CommandProvider::class, 'commentSpecialCharacters')]
    public function testAddCommentOnColumnRoundTripsSpecialCharacters(string $comment): void
    {
        $db = $this->getConnection(false);

        $schema = $db->getSchema();
        $sqlMode = $db->createCommand(
            <<<SQL
            SELECT @@SESSION.sql_mode
            SQL,
        )->queryScalar();

        $modes = [
            'default' => '',
            'no_backslash_escapes' => 'NO_BACKSLASH_ESCAPES',
        ];

        foreach ($modes as $label => $mode) {
            $db->createCommand(
                <<<SQL
                SET SESSION sql_mode = '$mode'
                SQL,
            )->execute();

            DbHelper::dropTablesIfExist($db, ['yii2_mysql_comment_special']);

            $db->createCommand()->createTable(
                'yii2_mysql_comment_special',
                [
                    'id' => 'integer',
                    'description' => 'string',
                ],
            )->execute();
            $db->createCommand()->addCommentOnColumn(
                'yii2_mysql_comment_special',
                'description',
                $comment,
            )->execute();

            self::assertSame(
                $comment,
                $schema->getTableSchema('yii2_mysql_comment_special', true)->getColumn('description')->comment,
                "Comment must round-trip under the `$label` sql_mode.",
            );

            DbHelper::dropTablesIfExist($db, ['yii2_mysql_comment_special']);
        }

        $db->createCommand(
            <<<SQL
            SET SESSION sql_mode = :sqlMode
            SQL,
            [':sqlMode' => $sqlMode],
        )->execute();
    }

    public function testAddCommentOnColumnExecutesWithCheckContainingQuotedParenthesis(): void
    {
        $db = $this->getConnection(false);

        $schema = $db->getSchema();

        DbHelper::dropTablesIfExist($db, ['yii2_mysql_quoted_paren']);

        $db->createCommand(
            <<<SQL
            CREATE TABLE `yii2_mysql_quoted_paren` (`status` varchar(32) CHECK (`status` <> '('))
            SQL,
        )->execute();

        $db->createCommand()->addCommentOnColumn('yii2_mysql_quoted_paren', 'status', 'A column comment.')->execute();

        self::assertSame(
            'A column comment.',
            $schema->getTableSchema('yii2_mysql_quoted_paren', true)->getColumn('status')->comment,
            'Comment must round-trip when the CHECK contains a quoted parenthesis.',
        );

        DbHelper::dropTablesIfExist($db, ['yii2_mysql_quoted_paren']);
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
