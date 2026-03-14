<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\db;

use yii\db\Command;
use yii\db\Connection;
use yii\db\Migration;

class MigrationTest extends \yiiunit\TestCase
{
    public function testBatchUpdate(): void
    {
        $command = $this->getMockBuilder(Command::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'batchUpdate',
                'execute',
            ])
            ->getMock();
        $command->expects($this->once())
            ->method('batchUpdate')
            ->with('customer', [['id' => 1, 'status' => 1]], [], ['id'], '')
            ->willReturnSelf();
        $command->expects($this->once())
            ->method('execute')
            ->willReturn(1);

        $connection = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['createCommand'])
            ->getMock();
        $connection->expects($this->once())
            ->method('createCommand')
            ->willReturn($command);

        $migration = new class extends Migration {
            public $beginDescription;
            public $endTime;

            public function init()
            {
            }

            protected function beginCommand($description)
            {
                $this->beginDescription = $description;
                return 42.0;
            }

            protected function endCommand($time)
            {
                $this->endTime = $time;
            }
        };
        $migration->db = $connection;
        $migration->batchUpdate('customer', [['id' => 1, 'status' => 1]], [], ['id']);

        $this->assertSame('update customer', $migration->beginDescription);
        $this->assertSame(42.0, $migration->endTime);
    }
}
