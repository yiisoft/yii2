<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\log;

use PHPUnit\Framework\Attributes\Group;
use yii\db\Command;
use yii\db\Connection;
use yii\log\DbTarget;
use yii\log\Logger;
use yii\log\LogRuntimeException;
use yiiunit\TestCase;

/**
 * Unit tests for {@see \yii\log\DbTarget} behavior independent of a database driver.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
#[Group('log')]
final class DbTargetTest extends TestCase
{
    public function testThrowLogRuntimeExceptionWhenInsertAffectsNoRows(): void
    {
        $command = $this->createMock(Command::class);

        $command->expects(self::once())
            ->method('bindValues')
            ->willReturnSelf();
        $command->expects(self::once())
            ->method('execute')
            ->willReturn(0);

        $db = $this->createMock(Connection::class);

        $db->method('getTransaction')->willReturn(null);
        $db->method('quoteTableName')->willReturn('log');
        $db->method('createCommand')->willReturn($command);

        $target = new DbTarget(['db' => $db]);

        $target->messages = [
            ['Message', Logger::LEVEL_ERROR, 'application', 10.25, []],
        ];

        $this->expectException(LogRuntimeException::class);
        $this->expectExceptionMessage(
            'Unable to export log through database!',
        );

        $target->export();
    }
}
