<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\mutex;

use Yii;
use yii\base\InvalidConfigException;
use yiiunit\framework\mutex\mocks\DumbMutex;
use yiiunit\TestCase;

/**
 * Class RetryAcquireTraitTest.
 *
 * @group mutex
 *
 * @author Robert Korulczyk <robert@korulczyk.pl>
 */
class RetryAcquireTraitTest extends TestCase
{
    /**
     * @throws InvalidConfigException
     */
    public function testRetryAcquire()
    {
        $mutexName = __FUNCTION__;
        $mutexOne = $this->createMutex();
        $mutexTwo = $this->createMutex();

        $this->assertTrue($mutexOne->acquire($mutexName));
        $this->assertFalse($mutexTwo->acquire($mutexName, 1));

        $this->assertSame(20, $mutexTwo->attemptsCounter);
    }

    /**
     * @return DumbMutex
     * @throws InvalidConfigException
     */
    private function createMutex()
    {
        return Yii::createObject([
            'class' => DumbMutex::className(),
        ]);
    }
}
