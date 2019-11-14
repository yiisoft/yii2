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

        $this->assertGreaterThanOrEqual(1, count($mutexTwo->attemptsTime));
        $this->assertLessThanOrEqual(20, count($mutexTwo->attemptsTime));

        foreach ($mutexTwo->attemptsTime as $i => $attemptTime) {
            if ($i === 0) {
                continue;
            }

            $intervalMilliseconds = ($mutexTwo->attemptsTime[$i] - $mutexTwo->attemptsTime[$i-1]) * 1000;
            $this->assertGreaterThanOrEqual($mutexTwo->retryDelay, $intervalMilliseconds);
        }
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
