<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\console;

use yii\console\ExitCode;
use yiiunit\TestCase;

/**
 * @group console
 */
class ExitCodeTest extends TestCase
{
    public function testGetReasonKnownCode(): void
    {
        $this->assertSame('Success', ExitCode::getReason(ExitCode::OK));
        $this->assertSame('Unspecified error', ExitCode::getReason(ExitCode::UNSPECIFIED_ERROR));
        $this->assertSame('Insufficient permissions', ExitCode::getReason(ExitCode::NOPERM));
    }

    public function testGetReasonUnknownCode(): void
    {
        $this->assertSame('Unknown exit code', ExitCode::getReason(999));
    }
}
