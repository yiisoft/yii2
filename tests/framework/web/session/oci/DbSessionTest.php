<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web\session\oci;

use PHPUnit\Framework\Attributes\Group;
use yii\web\DbSession;
use yiiunit\base\web\session\BaseDbSession;

use function str_repeat;
use function strlen;
use function strrev;
use function substr;

/**
 * Unit test for {@see \yii\web\DbSession} with Oracle driver.
 *
 * @see https://github.com/yiisoft/yii2/issues/15900
 * @see https://github.com/yiisoft/yii2/issues/16468
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
#[Group('db')]
#[Group('db-session')]
#[Group('oci')]
final class DbSessionTest extends BaseDbSession
{
    protected function getDriverNames()
    {
        return ['oci'];
    }

    public function testReadWriteReplaceLargeBinarySessionData(): void
    {
        $session = new DbSession();
        $seed = "Yii2\0Oracle\xFF\xFE\x80Session";
        $data = substr(str_repeat($seed, 8_000), 0, 131_072);
        $replacement = strrev($data);

        self::assertStringContainsString("\0", $data, 'Fixture must contain a NUL byte.');
        self::assertSame(131_072, strlen($data), 'Fixture must exceed 100,000 bytes.');

        $session->writeSession('big', $data);

        self::assertSame($data, $session->readSession('big'), 'Large session data must round-trip on insert.');

        $session->writeSession('big', $replacement);

        self::assertSame($replacement, $session->readSession('big'), 'Large session data must round-trip on replace.');

        $session->destroySession('big');
    }
}
