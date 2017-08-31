<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework;

use Psr\Log\LogLevel;
use Yii;
use yii\BaseYii;
use yii\di\Container;
use yii\log\Logger;
use yii\profile\Profiler;
use yiiunit\data\base\Singer;
use yiiunit\TestCase;

/**
 * BaseYiiTest
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
        Yii::setLogger(null);
        Yii::setProfiler(null);
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
        $this->assertInstanceOf(Logger::class, $defaultLogger);

        BaseYii::setLogger(['flushInterval' => 789]);
        $logger = BaseYii::getLogger();
        $this->assertSame($defaultLogger, $logger);
        $this->assertEquals(789, $logger->flushInterval);

        BaseYii::setLogger(function() {
            return new Logger();
        });
        $this->assertNotSame($defaultLogger, BaseYii::getLogger());

        BaseYii::setLogger(null);
        $defaultLogger = BaseYii::getLogger();
        BaseYii::setLogger([
            'class' => Logger::class,
            'flushInterval' => 987,
        ]);
        $logger = BaseYii::getLogger();
        $this->assertNotSame($defaultLogger, $logger);
        $this->assertEquals(987, $logger->flushInterval);
    }

    /**
     * @covers \yii\BaseYii::setProfiler()
     * @covers \yii\BaseYii::getProfiler()
     */
    public function testSetupProfiler()
    {
        $profiler = new Profiler();
        BaseYii::setProfiler($profiler);

        $this->assertSame($profiler, BaseYii::getProfiler());

        BaseYii::setProfiler(null);
        $defaultProfiler = BaseYii::getProfiler();
        $this->assertInstanceOf(Profiler::class, $defaultProfiler);

        BaseYii::setProfiler(function() {
            return new Profiler();
        });
        $this->assertNotSame($defaultProfiler, BaseYii::getProfiler());

        BaseYii::setProfiler(null);
        $defaultProfiler = BaseYii::getProfiler();
        BaseYii::setProfiler([
            'class' => Profiler::class,
        ]);
        $profiler = BaseYii::getProfiler();
        $this->assertNotSame($defaultProfiler, $profiler);
    }

    /**
     * @depends testSetupLogger
     *
     * @covers \yii\BaseYii::info()
     * @covers \yii\BaseYii::warning()
     * @covers \yii\BaseYii::debug()
     * @covers \yii\BaseYii::error()
     */
    public function testLog()
    {
        $logger = $this->getMockBuilder('yii\\log\\Logger')
            ->setMethods(['log'])
            ->getMock();
        BaseYii::setLogger($logger);

        $logger->expects($this->exactly(4))
            ->method('log')
            ->withConsecutive(
                [
                    $this->equalTo(LogLevel::INFO),
                    $this->equalTo('info message'),
                    $this->equalTo(['category' => 'info category'])
                ],
                [
                    $this->equalTo(LogLevel::WARNING),
                    $this->equalTo('warning message'),
                    $this->equalTo(['category' => 'warning category']),
                ],
                [
                    $this->equalTo(LogLevel::DEBUG),
                    $this->equalTo('trace message'),
                    $this->equalTo(['category' => 'trace category'])
                ],
                [
                    $this->equalTo(LogLevel::ERROR),
                    $this->equalTo('error message'),
                    $this->equalTo(['category' => 'error category'])
                ]
            );

        BaseYii::info('info message', 'info category');
        BaseYii::warning('warning message', 'warning category');
        BaseYii::debug('trace message', 'trace category');
        BaseYii::error('error message', 'error category');
    }

    /**
     * @depends testSetupProfiler
     *
     * @covers \yii\BaseYii::beginProfile()
     * @covers \yii\BaseYii::endProfile()
     */
    public function testProfile()
    {
        $profiler = $this->getMockBuilder('yii\profile\Profiler')
            ->setMethods(['begin', 'end'])
            ->getMock();
        BaseYii::setProfiler($profiler);

        $profiler->expects($this->exactly(2))
            ->method('begin')
            ->withConsecutive(
                [
                    $this->equalTo('Profile message 1'),
                    $this->equalTo(['category' => 'Profile category 1'])
                ],
                [
                    $this->equalTo('Profile message 2'),
                    $this->equalTo(['category' => 'Profile category 2']),
                ]
            );

        $profiler->expects($this->exactly(2))
            ->method('end')
            ->withConsecutive(
                [
                    $this->equalTo('Profile message 1'),
                    $this->equalTo(['category' => 'Profile category 1'])
                ],
                [
                    $this->equalTo('Profile message 2'),
                    $this->equalTo(['category' => 'Profile category 2']),
                ]
            );

        BaseYii::beginProfile('Profile message 1', 'Profile category 1');
        BaseYii::endProfile('Profile message 1', 'Profile category 1');
        BaseYii::beginProfile('Profile message 2', 'Profile category 2');
        BaseYii::endProfile('Profile message 2', 'Profile category 2');
    }
}
