<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\mutex;

use Yii;
use yii\base\InvalidConfigException;
use yii\mutex\OracleMutex;
use yiiunit\framework\db\DatabaseTestCase;

/**
 * @group mutex
 * @group db
 * @group oci
 */
class OracleMutexIntegrationTest extends DatabaseTestCase
{
    use MutexTestTrait;

    protected $driverName = 'oci';

    /**
     * @return OracleMutex
     * @throws InvalidConfigException
     */
    protected function createMutex()
    {
        return Yii::createObject([
            'class' => OracleMutex::class,
            'db' => $this->getConnection(),
        ]);
    }

    public static function mutexDataProvider(): array
    {
        return [
            'simple name' => ['testname'],
            'max length safe name' => ['oracle_' . str_repeat('a', 120)],
        ];
    }

    public function testReleaseOnCommitReleasesLockAfterCommit(): void
    {
        $releasingMutex = Yii::createObject([
            'class' => OracleMutex::class,
            'db' => $this->getConnection(),
            'autoRelease' => false,
            'releaseOnCommit' => true,
        ]);
        $competingMutex = Yii::createObject([
            'class' => OracleMutex::class,
            'db' => $this->getConnection(),
            'autoRelease' => false,
        ]);
        $mutexName = 'testReleaseOnCommit';

        $this->assertTrue($releasingMutex->acquire($mutexName));
        $this->assertFalse($competingMutex->acquire($mutexName));

        $releasingMutex->db->createCommand('COMMIT')->execute();

        $this->assertTrue($competingMutex->acquire($mutexName));
        $this->assertFalse($releasingMutex->release($mutexName));
        $this->assertTrue($competingMutex->release($mutexName));
    }

    public function testNlLockModeIsCompatibleWithExclusiveLock(): void
    {
        $exclusiveMutex = $this->createMutex();
        $nullMutex = Yii::createObject([
            'class' => OracleMutex::class,
            'db' => $this->getConnection(),
            'lockMode' => OracleMutex::MODE_NL,
        ]);
        $mutexName = 'testNlMode';

        $this->assertTrue($exclusiveMutex->acquire($mutexName));
        $this->assertTrue($nullMutex->acquire($mutexName));
        $this->assertTrue($exclusiveMutex->release($mutexName));
        $this->assertTrue($nullMutex->release($mutexName));
    }
}
