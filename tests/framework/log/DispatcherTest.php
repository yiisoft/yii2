<?php
/**
 * @author Carsten Brandt <mail@cebe.cc>
 */

namespace yiiunit\framework\log;

use yii\log\Dispatcher;
use yii\log\Logger;
use Yii;
use yiiunit\TestCase;

/**
 * @group log
 */
class DispatcherTest extends TestCase
{

    public function testConfigureLogger()
    {
        $dispatcher = new Dispatcher();
        $this->assertSame(Yii::getLogger(), $dispatcher->getLogger());


        $logger = new Logger();
        $dispatcher = new Dispatcher([
            'logger' => $logger,
        ]);
        $this->assertSame($logger, $dispatcher->getLogger());


        $dispatcher = new Dispatcher([
            'logger' => 'yii\log\Logger',
        ]);
        $this->assertInstanceOf('yii\log\Logger', $dispatcher->getLogger());
        $this->assertEquals(0, $dispatcher->getLogger()->traceLevel);


        $dispatcher = new Dispatcher([
            'logger' => [
                'class' => 'yii\log\Logger',
                'traceLevel' => 42,
            ],
        ]);
        $this->assertInstanceOf('yii\log\Logger', $dispatcher->getLogger());
        $this->assertEquals(42, $dispatcher->getLogger()->traceLevel);
    }

    public function testSetLogger()
    {
        $dispatcher = new Dispatcher();
        $this->assertSame(Yii::getLogger(), $dispatcher->getLogger());

        $logger = new Logger();
        $dispatcher->setLogger($logger);
        $this->assertSame($logger, $dispatcher->getLogger());

        $dispatcher->setLogger('yii\log\Logger');
        $this->assertInstanceOf('yii\log\Logger', $dispatcher->getLogger());
        $this->assertEquals(0, $dispatcher->getLogger()->traceLevel);


        $dispatcher->setLogger([
            'class' => 'yii\log\Logger',
            'traceLevel' => 42,
        ]);
        $this->assertInstanceOf('yii\log\Logger', $dispatcher->getLogger());
        $this->assertEquals(42, $dispatcher->getLogger()->traceLevel);
    }

}
