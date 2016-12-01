<?php
namespace yiiunit\framework\log;

use Yii;
use yii\db\Connection;
use yii\db\Query;
use yii\log\Logger;
use yiiunit\framework\console\controllers\EchoMigrateController;
use yiiunit\TestCase;

/**
 * @group log
 */
abstract class DbTargetTest extends TestCase
{
    protected static $database;
    protected static $driverName = 'mysql';

    /**
     * @var Connection
     */
    protected static $db;

    protected static $logTable = '{{%log}}';

    protected static function runConsoleAction($route, $params = [])
    {
        if (Yii::$app === null) {
            new \yii\console\Application([
                'id' => 'Migrator',
                'basePath' => '@yiiunit',
                'controllerMap' => [
                    'migrate' => EchoMigrateController::className(),
                ],
                'components' => [
                    'db' => static::getConnection(),
                    'log' => [
                        'targets' => [
                            [
                                'class' => 'yii\log\DbTarget',
                                'levels' => ['warning'],
                                'logTable' => self::$logTable,
                            ],
                        ],
                    ],
                ],
            ]);
        }

        ob_start();
        $result = Yii::$app->runAction($route, $params);
        echo "Result is " . $result;
        if ($result !== \yii\console\Controller::EXIT_CODE_NORMAL) {
            ob_end_flush();
        } else {
            ob_end_clean();
        }
    }

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        $databases = static::getParam('databases');
        static::$database = $databases[static::$driverName];
        $pdo_database = 'pdo_' . static::$driverName;

        if (!extension_loaded('pdo') || !extension_loaded($pdo_database)) {
            static::markTestSkipped('pdo and ' . $pdo_database . ' extension are required.');
        }

        static::runConsoleAction('migrate/up', ['migrationPath' => '@yii/log/migrations/', 'interactive' => false]);
    }

    public static function tearDownAfterClass()
    {
        static::runConsoleAction('migrate/down', ['migrationPath' => '@yii/log/migrations/', 'interactive' => false]);
        if (static::$db) {
            static::$db->close();
        }
        Yii::$app = null;
        parent::tearDownAfterClass();
    }

    protected function tearDown()
    {
        parent::tearDown();
        self::getConnection()->createCommand()->truncateTable(self::$logTable)->execute();
    }

    /**
     * @throws \yii\base\InvalidParamException
     * @throws \yii\db\Exception
     * @throws \yii\base\InvalidConfigException
     * @return \yii\db\Connection
     */
    public static function getConnection()
    {
        if (static::$db == null) {
            $db = new Connection;
            $db->dsn = static::$database['dsn'];
            if (isset(static::$database['username'])) {
                $db->username = static::$database['username'];
                $db->password = static::$database['password'];
            }
            if (isset(static::$database['attributes'])) {
                $db->attributes = static::$database['attributes'];
            }
            if (!$db->isActive) {
                $db->open();
            }
            static::$db = $db;
        }
        return static::$db;
    }

    /**
     * Tests that precision isn't lost for log timestamps
     * @see https://github.com/yiisoft/yii2/issues/7384
     */
    public function testTimestamp()
    {
        $logger = Yii::getLogger();

        $time = 1424865393.0105;

        // forming message data manually in order to set time
        $messsageData = [
            'test',
            Logger::LEVEL_WARNING,
            'test',
            $time,
            []
        ];

        $logger->messages[] = $messsageData;
        $logger->flush(true);

        $query = (new Query())->select('log_time')->from(self::$logTable)->where(['category' => 'test']);
        $loggedTime = $query->createCommand(self::getConnection())->queryScalar();
        static::assertEquals($time, $loggedTime);
    }
}