<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web;

use Yii;
use yii\db\Connection;
use yii\db\Query;
use yii\web\DbSession;
use yiiunit\framework\console\controllers\EchoMigrateController;
use yiiunit\TestCase;

/**
 * @group db
 */
class DbSessionTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        /*
         * @todo Optionally do fallback to some other database, however this might be overkill for tests only since
         * sqlite is always available on travis.
         */
        if (!in_array('sqlite', \PDO::getAvailableDrivers())) {
            $this->markTestIncomplete('DbSessionTest requires SQLite!');
        }
        $this->mockApplication();
        Yii::$app->set('db', [
            'class' => Connection::className(),
            'dsn' => 'sqlite::memory:',
        ]);
    }

    protected function createTableSession()
    {
        Yii::$app->db->createCommand()->createTable('session', [
            'id' => 'string',
            'expire' => 'integer',
            'data' => 'text',
            'user_id' => 'integer',
        ])->execute();
    }

    // Tests :

    public function testReadWrite()
    {
        $this->createTableSession();

        $session = new DbSession();

        $session->writeSession('test', 'session data');
        $this->assertEquals('session data', $session->readSession('test'));
        $session->destroySession('test');
        $this->assertEquals('', $session->readSession('test'));
    }

    /**
     * @depends testReadWrite
     */
    public function testGarbageCollection()
    {
        $this->createTableSession();

        $session = new DbSession();

        $session->writeSession('new', 'new data');
        $session->writeSession('expire', 'expire data');

        $session->db->createCommand()
            ->update('session', ['expire' => time() - 100], 'id = :id', ['id' => 'expire'])
            ->execute();
        $session->gcSession(1);

        $this->assertEquals('', $session->readSession('expire'));
        $this->assertEquals('new data', $session->readSession('new'));
    }

    /**
     * @depends testReadWrite
     */
    public function testWriteCustomField()
    {
        $this->createTableSession();

        $session = new DbSession();
        $session->writeCallback = function ($session) {
            return [
                'user_id' => 15,
            ];
        };

        $session->writeSession('test', 'session data');

        $query = new Query();
        $sessionRow = $query->from('session')
            ->where(['id' => 'test'])
            ->one();

        $this->assertEquals('session data', $sessionRow['data']);
        $this->assertEquals(15, $sessionRow['user_id']);
    }

    protected function runMigrate($action, $params = [])
    {
        $migrate = new EchoMigrateController('migrate', Yii::$app, [
            'migrationPath' => '@yii/web/migrations',
            'interactive' => false,
        ]);

        ob_start();
        ob_implicit_flush(false);
        $migrate->run($action, $params);
        ob_get_clean();

        return array_map(function ($version) {
            return substr($version, 15);
        }, (new Query())->select(['version'])->from('migration')->column());
    }

    public function testMigration()
    {
        $this->mockWebApplication([
            'components' => [
                'db' => [
                    'class' => Connection::className(),
                    'dsn' => 'sqlite::memory:',
                ],
            ],
        ]);

        $history = $this->runMigrate('history');
        $this->assertEquals(['base'], $history);

        $history = $this->runMigrate('up');
        $this->assertEquals(['base', 'session_init'], $history);

        $history = $this->runMigrate('down');
        $this->assertEquals(['base'], $history);
    }
}
