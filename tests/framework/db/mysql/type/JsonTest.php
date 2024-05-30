<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mysql\type;

use yii\db\JsonExpression;
use yii\db\mysql\Schema;
use yiiunit\framework\db\DatabaseTestCase;

/**
 * @group db
 * @group mysql
 */
class JsonTest extends DatabaseTestCase
{
    protected $driverName = 'mysql';

    public function testCreateTable(): void
    {
        $db = $this->getConnection();

        if ($db->getSchema()->getTableSchema('json') !== null) {
            $db->createCommand()->dropTable('json')->execute();
        }

        $command = $db->createCommand();
        $command->createTable('json', ['id' => Schema::TYPE_PK, 'data' => Schema::TYPE_JSON])->execute();

        $this->assertTrue($db->getTableSchema('json') !== null);
        $this->assertSame('data', $db->getTableSchema('json')->getColumn('data')->name);
        $this->assertSame('json', $db->getTableSchema('json')->getColumn('data')->type);
    }

    public function testInsertAndSelect(): void
    {
        $db = $this->getConnection(true);
        $version = $db->getServerVersion();

        $command = $db->createCommand();
        $command->insert('storage', ['data' => ['a' => 1, 'b' => 2]])->execute();

        if (\stripos($version, 'MariaDb') === false) {
            $rowExpected = '{"a": 1, "b": 2}';
        } else {
            $rowExpected = '{"a":1,"b":2}';
        }

        $this->assertSame(
            $rowExpected,
            $command->setSql(
                <<<SQL
                SELECT `data` FROM `storage`
                SQL,
            )->queryScalar(),
        );
    }

    public function testInsertJsonExpressionAndSelect(): void
    {
        $db = $this->getConnection(true);
        $version = $db->getServerVersion();

        $command = $db->createCommand();
        $command->insert('storage', ['data' => new JsonExpression(['a' => 1, 'b' => 2])])->execute();

        if (\stripos($version, 'MariaDb') === false) {
            $rowExpected = '{"a": 1, "b": 2}';
        } else {
            $rowExpected = '{"a":1,"b":2}';
        }

        $this->assertSame(
            $rowExpected,
            $command->setSql(
                <<<SQL
                SELECT `data` FROM `storage`
                SQL,
            )->queryScalar(),
        );
    }
}
