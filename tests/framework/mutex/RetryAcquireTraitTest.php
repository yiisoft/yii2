<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
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

        $this->assertTrue(
            $mutexOne->acquire($mutexName),
            'Failed to acquire first mutex.'
        );
        $this->assertFalse(
            $mutexTwo->acquire($mutexName, 1),
            'Second mutex was acquired but should have timed out.'
        );

        $this->assertGreaterThanOrEqual(
            1,
            count($mutexTwo->attemptsTime),
            'There should be at least one atttempt to acquire first mutex.'
        );
        $this->assertLessThanOrEqual(
            20,
            count($mutexTwo->attemptsTime),
            'There could be no more than 20 attempts consideing 50ms delay and 1s timeout.'
        );

        // https://docs.microsoft.com/en-us/windows/win32/api/synchapi/nf-synchapi-sleep
        // If dwMilliseconds is less than the resolution of the system clock, the thread may sleep for less
        // than the specified length of time.
        if (!$this->isRunningOnWindows()) {
            foreach ($mutexTwo->attemptsTime as $i => $attemptTime) {
                if ($i === 0) {
                    continue;
                }

                $attemptInterval = ($mutexTwo->attemptsTime[$i] - $mutexTwo->attemptsTime[$i - 1]) * 1000;
                $this->assertGreaterThanOrEqual(
                    $mutexTwo->retryDelay,
                    $attemptInterval,
                    sprintf(
                        'Retry delay of %s ms was not properly taken into account. Actual interval was %s ms.',
                        $mutexTwo->retryDelay,
                        $attemptInterval
                    )
                );
            }
        }
    }

    /**
     * @return bool
     */
    private function isRunningOnWindows()
    {
        return DIRECTORY_SEPARATOR === '\\';
    }

    /**
     * @return DumbMutex
     * @throws InvalidConfigException
     */
    private function createMutex()
    {
        return Yii::createObject(
            [
                'class' => DumbMutex::className(),
            ]
        );
    }
}
