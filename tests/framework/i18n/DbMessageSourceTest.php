<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\i18n;

use Yii;
use yii\base\Event;
use yii\db\Connection;
use yii\i18n\DbMessageSource;
use yii\i18n\I18N;
use yiiunit\framework\console\controllers\EchoMigrateController;

/**
 * @group i18n
 * @group db
 * @group mysql
 * @author Dmitry Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0.7
 */
class DbMessageSourceTest extends I18NTest
{
    protected static $database;
    protected static $driverName = 'mysql';

    /**
     * @var Connection
     */
    protected static $db;

    protected function setI18N()
    {
        $this->i18n = new I18N([
            'translations' => [
                'test' => [
                    'class' => $this->getMessageSourceClass(),
                    'db' => static::$db,
                ],
            ],
        ]);
    }

    private function getMessageSourceClass()
    {
        return DbMessageSource::className();
    }

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
                ],
            ]);
        }

        ob_start();
        $result = Yii::$app->runAction($route, $params);
        echo 'Result is ' . $result;
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

        static::runConsoleAction('migrate/up', ['migrationPath' => '@yii/i18n/migrations/', 'interactive' => false]);

        static::$db->createCommand()->truncateTable('source_message');
        static::$db->createCommand()->batchInsert('source_message', ['category', 'message'], [
            ['test', 'Hello world!'], // id = 1
            ['test', 'The dog runs fast.'], // id = 2
            ['test', 'His speed is about {n} km/h.'], // id = 3
            ['test', 'His name is {name} and his speed is about {n, number} km/h.'], // id = 4
            ['test', 'There {n, plural, =0{no cats} =1{one cat} other{are # cats}} on lying on the sofa!'], // id = 5
        ])->execute();

        static::$db->createCommand()->insert('message', ['id' => 1, 'language' => 'de', 'translation' => 'Hallo Welt!'])->execute();
        static::$db->createCommand()->insert('message', ['id' => 2, 'language' => 'de-DE', 'translation' => 'Der Hund rennt schnell.'])->execute();
        static::$db->createCommand()->insert('message', ['id' => 2, 'language' => 'en-US', 'translation' => 'The dog runs fast (en-US).'])->execute();
        static::$db->createCommand()->insert('message', ['id' => 2, 'language' => 'ru', 'translation' => 'Собака бегает быстро.'])->execute();
        static::$db->createCommand()->insert('message', ['id' => 3, 'language' => 'de-DE', 'translation' => 'Seine Geschwindigkeit beträgt {n} km/h.'])->execute();
        static::$db->createCommand()->insert('message', ['id' => 4, 'language' => 'de-DE', 'translation' => 'Er heißt {name} und ist {n, number} km/h schnell.'])->execute();
        static::$db->createCommand()->insert('message', ['id' => 5, 'language' => 'ru', 'translation' => 'На диване {n, plural, =0{нет кошек} =1{лежит одна кошка} one{лежит # кошка} few{лежит # кошки} many{лежит # кошек} other{лежит # кошки}}!'])->execute();
    }

    public static function tearDownAfterClass()
    {
        static::runConsoleAction('migrate/down', ['migrationPath' => '@yii/i18n/migrations/', 'interactive' => false]);
        if (static::$db) {
            static::$db->close();
        }
        Yii::$app = null;
        parent::tearDownAfterClass();
    }

    /**
     * @return \yii\db\Connection
     * @throws \yii\db\Exception
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\base\InvalidParamException
     */
    public static function getConnection()
    {
        if (static::$db == null) {
            $db = new Connection();
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

    public function testMissingTranslationEvent()
    {
        $this->assertEquals('Hallo Welt!', $this->i18n->translate('test', 'Hello world!', [], 'de-DE'));
        $this->assertEquals('Missing translation message.', $this->i18n->translate('test', 'Missing translation message.', [], 'de-DE'));
        $this->assertEquals('Hallo Welt!', $this->i18n->translate('test', 'Hello world!', [], 'de-DE'));

        Event::on(DbMessageSource::className(), DbMessageSource::EVENT_MISSING_TRANSLATION, function ($event) {});
        $this->assertEquals('Hallo Welt!', $this->i18n->translate('test', 'Hello world!', [], 'de-DE'));
        $this->assertEquals('Missing translation message.', $this->i18n->translate('test', 'Missing translation message.', [], 'de-DE'));
        $this->assertEquals('Hallo Welt!', $this->i18n->translate('test', 'Hello world!', [], 'de-DE'));
        Event::off(DbMessageSource::className(), DbMessageSource::EVENT_MISSING_TRANSLATION);

        Event::on(DbMessageSource::className(), DbMessageSource::EVENT_MISSING_TRANSLATION, function ($event) {
            if ($event->message == 'New missing translation message.') {
                $event->translatedMessage = 'TRANSLATION MISSING HERE!';
            }
        });
        $this->assertEquals('Hallo Welt!', $this->i18n->translate('test', 'Hello world!', [], 'de-DE'));
        $this->assertEquals('Another missing translation message.', $this->i18n->translate('test', 'Another missing translation message.', [], 'de-DE'));
        $this->assertEquals('Missing translation message.', $this->i18n->translate('test', 'Missing translation message.', [], 'de-DE'));
        $this->assertEquals('TRANSLATION MISSING HERE!', $this->i18n->translate('test', 'New missing translation message.', [], 'de-DE'));
        $this->assertEquals('Hallo Welt!', $this->i18n->translate('test', 'Hello world!', [], 'de-DE'));
        Event::off(DbMessageSource::className(), DbMessageSource::EVENT_MISSING_TRANSLATION);
    }


    public function testIssue11429($sourceLanguage = null)
    {
        $this->markTestSkipped('DbMessageSource does not produce any errors when messages file is missing.');
    }
}
