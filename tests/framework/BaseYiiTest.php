<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework;

use Yii;
use yii\BaseYii;
use yii\di\Container;
use yii\log\Logger;
use yiiunit\data\base\Singer;
use yiiunit\TestCase;
use yiiunit\data\base\CallableClass;
use yiiunit\framework\di\stubs\FooBaz;
use yiiunit\framework\di\stubs\FooDependentSubclass;
use yiiunit\framework\di\stubs\Qux;

/**
 * BaseYiiTest.
 * @group base
 */
class BaseYiiTest extends TestCase
{
    public $aliases;

    protected function setUp()
    {
        parent::setUp();
        $this->aliases = Yii::$aliases;
    }

    protected function tearDown()
    {
        parent::tearDown();
        Yii::$aliases = $this->aliases;
    }

    public function testAlias()
    {
        $this->assertEquals(YII2_PATH, Yii::getAlias('@yii'));

        Yii::$aliases = [];
        $this->assertFalse(Yii::getAlias('@yii', false));

        Yii::setAlias('@yii', '/yii/framework');
        $this->assertEquals('/yii/framework', Yii::getAlias('@yii'));
        $this->assertEquals('/yii/framework/test/file', Yii::getAlias('@yii/test/file'));
        Yii::setAlias('@yii/gii', '/yii/gii');
        $this->assertEquals('/yii/framework', Yii::getAlias('@yii'));
        $this->assertEquals('/yii/framework/test/file', Yii::getAlias('@yii/test/file'));
        $this->assertEquals('/yii/gii', Yii::getAlias('@yii/gii'));
        $this->assertEquals('/yii/gii/file', Yii::getAlias('@yii/gii/file'));

        Yii::setAlias('@tii', '@yii/test');
        $this->assertEquals('/yii/framework/test', Yii::getAlias('@tii'));

        Yii::setAlias('@yii', null);
        $this->assertFalse(Yii::getAlias('@yii', false));
        $this->assertEquals('/yii/gii/file', Yii::getAlias('@yii/gii/file'));

        Yii::setAlias('@some/alias', '/www');
        $this->assertEquals('/www', Yii::getAlias('@some/alias'));
    }

    public function testGetVersion()
    {
        $this->assertTrue((bool) preg_match('~\d+\.\d+(?:\.\d+)?(?:-\w+)?~', \Yii::getVersion()));
    }

    public function testPowered()
    {
        $this->assertInternalType('string', Yii::powered());
    }

    public function testCreateObjectArray()
    {
        Yii::$container = new Container();

        $qux = Yii::createObject([
            '__class' => Qux::className(),
            'a' => 42,
        ]);

        $this->assertInstanceOf(Qux::className(), $qux);
        $this->assertSame(42, $qux->a);
    }

    public function testCreateObjectCallable()
    {
        Yii::$container = new Container();

        // Test passing in of normal params combined with DI params.
        $this->assertTrue(Yii::createObject(function (Singer $singer, $a) {
            return $a === 'a';
        }, ['a']));


        $singer = new Singer();
        $singer->firstName = 'Bob';
        $this->assertTrue(Yii::createObject(function (Singer $singer, $a) {
            return $singer->firstName === 'Bob';
        }, [$singer, 'a']));


        $this->assertTrue(Yii::createObject(function (Singer $singer, $a = 3) {
            return true;
        }));

        $this->assertTrue(Yii::createObject(new CallableClass()));
    }

    public function testCreateObjectEmptyArrayException()
    {
        $this->expectException('yii\base\InvalidConfigException');
        $this->expectExceptionMessage('Object configuration must be an array containing a "class" or "__class" element.');

        Yii::createObject([]);
    }

    public function testCreateObjectInvalidConfigException()
    {
        $this->expectException('yii\base\InvalidConfigException');
        $this->expectExceptionMessage('Unsupported configuration type: ' . gettype(null));

        Yii::createObject(null);
    }

    public function testDi3CompatibilityCreateDependentObject()
    {
        $object = Yii::createObject([
            '__class' => FooBaz::className(),
            'fooDependent' => ['__class' => FooDependentSubclass::className()],
        ]);

        $this->assertInstanceOf(FooBaz::className(), $object);
        $this->assertInstanceOf(FooDependentSubclass::className(), $object->fooDependent);
    }

    /**
     * @covers \yii\BaseYii::setLogger()
     * @covers \yii\BaseYii::getLogger()
     */
    public function testSetupLogger()
    {
        $logger = new Logger();
        BaseYii::setLogger($logger);

        $this->assertSame($logger, BaseYii::getLogger());

        BaseYii::setLogger(null);
        $defaultLogger = BaseYii::getLogger();
        $this->assertInstanceOf(Logger::className(), $defaultLogger);
    }

    /**
     * @covers \yii\BaseYii::info()
     * @covers \yii\BaseYii::warning()
     * @covers \yii\BaseYii::debug()
     * @covers \yii\BaseYii::error()
     * @covers \yii\BaseYii::beginProfile()
     * @covers \yii\BaseYii::endProfile()
     */
    public function testLog()
    {
        $logger = $this->getMockBuilder('yii\\log\\Logger')
            ->setMethods(['log'])
            ->getMock();
        BaseYii::setLogger($logger);

        $logger->expects($this->exactly(6))
            ->method('log')
            ->withConsecutive(
                [$this->equalTo('info message'), $this->equalTo(Logger::LEVEL_INFO), $this->equalTo('info category')],
                [
                    $this->equalTo('warning message'),
                    $this->equalTo(Logger::LEVEL_WARNING),
                    $this->equalTo('warning category'),
                ],
                [$this->equalTo('trace message'), $this->equalTo(Logger::LEVEL_TRACE), $this->equalTo('trace category')],
                [$this->equalTo('error message'), $this->equalTo(Logger::LEVEL_ERROR), $this->equalTo('error category')],
                [
                    $this->equalTo('beginProfile message'),
                    $this->equalTo(Logger::LEVEL_PROFILE_BEGIN),
                    $this->equalTo('beginProfile category'),
                ],
                [
                    $this->equalTo('endProfile message'),
                    $this->equalTo(Logger::LEVEL_PROFILE_END),
                    $this->equalTo('endProfile category'),
                ]
            );

        BaseYii::info('info message', 'info category');
        BaseYii::warning('warning message', 'warning category');
        BaseYii::debug('trace message', 'trace category');
        BaseYii::error('error message', 'error category');
        BaseYii::beginProfile('beginProfile message', 'beginProfile category');
        BaseYii::endProfile('endProfile message', 'endProfile category');

        BaseYii::setLogger(null);
    }
}
