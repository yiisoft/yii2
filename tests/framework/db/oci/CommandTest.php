<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\oci;

use yii\caching\ArrayCache;
use yii\db\Connection;
use yii\db\Schema;

/**
 * @group db
 * @group oci
 */
class CommandTest extends \yiiunit\framework\db\CommandTest
{
    protected $driverName = 'oci';

    public function testAutoQuoting()
    {
        $db = $this->getConnection(false);

        $sql = 'SELECT [[id]], [[t.name]] FROM {{customer}} t';
        $command = $db->createCommand($sql);
        $this->assertEquals('SELECT "id", "t"."name" FROM "customer" t', $command->sql);
    }

    public function testLastInsertId()
    {
        $db = $this->getConnection();

        $sql = 'INSERT INTO {{profile}}([[description]]) VALUES (\'non duplicate\')';
        $command = $db->createCommand($sql);
        $command->execute();
        $this->assertEquals(3, $db->getSchema()->getLastInsertID('profile_SEQ'));
    }

    public function batchInsertSqlProvider()
    {
        $data = parent::batchInsertSqlProvider();
        $data['issue11242']['expected'] = 'INSERT INTO "type" ("int_col", "float_col", "char_col") VALUES (NULL, NULL, \'Kyiv {{city}}, Ukraine\')';
        $data['wrongBehavior']['expected'] = 'INSERT INTO "type" ("type"."int_col", "float_col", "char_col") VALUES (\'\', \'\', \'Kyiv {{city}}, Ukraine\')';

        return $data;
    }

    /**
     * Testing the "ORA-01461: can bind a LONG value only for insert into a LONG column"
     *
     * @return void
     */
    public function testCLOBStringInsertion()
    {
        $db = $this->getConnection();

        if ($db->getSchema()->getTableSchema('longstring') !== null) {
            $db->createCommand()->dropTable('longstring')->execute();
        }

        $db->createCommand()->createTable('longstring', ['message' => Schema::TYPE_TEXT])->execute();

        $longData = str_pad('-', 4001, '-=', STR_PAD_LEFT);
        $db->createCommand()->insert('longstring', [
            'message' => $longData,
        ])->execute();

        $this->assertEquals(1, $db->createCommand('SELECT count(*) FROM {{longstring}}')->queryScalar());

        $db->createCommand()->dropTable('longstring')->execute();
    }

    public function testQueryCache()
    {
        $db = $this->getConnection(true);

        $db->enableQueryCache = true;
        $db->queryCache = new ArrayCache();

        $command = $db->createCommand('SELECT [[name]] FROM {{customer}} WHERE [[id]] = :id');

        $this->assertEquals('user1', $command->bindValue(':id', 1)->queryScalar());

        $update = $db->createCommand('UPDATE {{customer}} SET [[name]] = :name WHERE [[id]] = :id');
        $update->bindValues([':id' => 1, ':name' => 'user11'])->execute();

        $command = $db->createCommand('SELECT [[name]] FROM {{customer}} WHERE [[id]] = :id');

        $this->assertEquals('user11', $command->bindValue(':id', 1)->queryScalar());

        $db->cache(function (Connection $db) use ($update) {
            $command = $db->createCommand('SELECT [[name]] FROM {{customer}} WHERE [[id]] = :id');

            $this->assertEquals('user2', $command->bindValue(':id', 2)->queryScalar());

            $update->bindValues([':id' => 2, ':name' => 'user22'])->execute();

            $command = $db->createCommand('SELECT [[name]] FROM {{customer}} WHERE [[id]] = :id');

            $this->assertEquals('user2', $command->bindValue(':id', 2)->queryScalar());

            $db->noCache(function () use ($db) {
                $command = $db->createCommand('SELECT [[name]] FROM {{customer}} WHERE [[id]] = :id');

                $this->assertEquals('user22', $command->bindValue(':id', 2)->queryScalar());
            });

            $command = $db->createCommand('SELECT [[name]] FROM {{customer}} WHERE [[id]] = :id');

            $this->assertEquals('user2', $command->bindValue(':id', 2)->queryScalar());
        }, 10);

        $db->enableQueryCache =false;

        $db->cache(function (Connection $db) use ($update) {
            $command = $db->createCommand('SELECT [[name]] FROM {{customer}} WHERE [[id]] = :id');

            $this->assertEquals('user22', $command->bindValue(':id', 2)->queryScalar());

            $update->bindValues([':id' => 2, ':name' => 'user2'])->execute();

            $command = $db->createCommand('SELECT [[name]] FROM {{customer}} WHERE [[id]] = :id');

            $this->assertEquals('user2', $command->bindValue(':id', 2)->queryScalar());
        }, 10);

        $db->enableQueryCache = true;

        $command = $db->createCommand('SELECT [[name]] FROM {{customer}} WHERE [[id]] = :id')->cache();

        $this->assertEquals('user11', $command->bindValue(':id', 1)->queryScalar());

        $update->bindValues([':id' => 1, ':name' => 'user1'])->execute();

        $command = $db->createCommand('SELECT [[name]] FROM {{customer}} WHERE [[id]] = :id')->cache();

        $this->assertEquals('user11', $command->bindValue(':id', 1)->queryScalar());

        $command = $db->createCommand('SELECT [[name]] FROM {{customer}} WHERE [[id]] = :id')->noCache();

        $this->assertEquals('user1', $command->bindValue(':id', 1)->queryScalar());

        $db->cache(function (Connection $db) use ($update) {
            $command = $db->createCommand('SELECT [[name]] FROM {{customer}} WHERE [[id]] = :id');

            $this->assertEquals('user11', $command->bindValue(':id', 1)->queryScalar());

            $command = $db->createCommand('SELECT [[name]] FROM {{customer}} WHERE [[id]] = :id')->noCache();

            $this->assertEquals('user1', $command->bindValue(':id', 1)->queryScalar());
        }, 10);
    }
}
