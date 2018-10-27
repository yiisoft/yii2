<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\mutex;

use yii\base\InvalidConfigException;
use yii\mutex\Mutex;

/**
 * Class MutexTestTrait.
 */
trait MutexTestTrait
{
    /**
     * @return Mutex
     * @throws InvalidConfigException
     */
    abstract protected function createMutex();

    /**
     * @dataProvider mutexDataProvider()
     *
     * @param string $mutexName
     */
    public function testMutexAcquire($mutexName)
    {
        $mutex = $this->createMutex();

        $this->assertTrue($mutex->acquire($mutexName));
        $this->assertTrue($mutex->release($mutexName));
    }

    /**
     * @dataProvider mutexDataProvider()
     *
     * @param string $mutexName
     */
    public function testThatMutexLockIsWorking($mutexName)
    {
        $mutexOne = $this->createMutex($mutexName);
        $mutexTwo = $this->createMutex($mutexName);

        $this->assertTrue($mutexOne->acquire($mutexName));
        $this->assertFalse($mutexTwo->acquire($mutexName));

        $mutexOne->release($mutexName);

        $this->assertTrue($mutexTwo->acquire($mutexName));
    }

    public static function mutexDataProvider()
    {
        $utf = <<<'UTF'
𝐘˛𝜄 ӏ𝕤 𝗮 𝔣𝖺𐑈𝝉, 𐑈ℯ𝔠ｕ𝒓𝗲, 𝝰𝞹𝒹 𝖊𝘧𝒇𝗶𝕔𝖎ⅇπτ Ｐ𝘏𝙿 𝖿г𝖺ｍ𝖾ｗσｒ𝐤.
𝓕lе𝘅ӏᏏlе 𝞬𝖾𝘁 ϱ𝘳ɑ𝖌ｍ𝛼𝓉ͺ𝖼. 
𝑊ﮭ𝚛𝛞𝓼 𝔯𝕚𝕘һ𝞃 σ𝚞𝞽 ०𝒇 𝐭𝙝ҽ 𝗯𝘰𝘹. 
𝓗𝚊𝘀 𝓇𝖾𝙖𝐬ﻬ𝓃𝕒ᖯl𝔢 ꓒ𝘦քα𝗎l𝐭ꜱ. 
😱
UTF;

        return [
            'simple name' => ['testname'],
            'long name' => ['Y' . str_repeat('iiiiiiiiii', 1000)],
            'UTF-8 garbage' => [$utf],
        ];
    }
}
