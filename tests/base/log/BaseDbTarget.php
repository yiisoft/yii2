<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\base\log;

use Psr\Log\LogLevel;
use Yii;
use yii\db\Query;
use yii\db\Schema;
use yii\log\DbTarget;
use yii\log\Dispatcher;
use yii\log\Logger;
use yii\log\PsrMessage;
use yiiunit\framework\db\DatabaseTestCase;
use yiiunit\support\DbHelper;

use function is_resource;
use function stream_get_contents;
use function time;

/**
 * Base class for {@see \yii\log\DbTarget} tests across database drivers.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
abstract class BaseDbTarget extends DatabaseTestCase
{
    private const string LOG_TABLE = '{{%log}}';
    protected const string SQLITE_DATABASE_FILE = __DIR__ . '/../../runtime/sqlite-log-target.sq3';

    protected function setUp(): void
    {
        parent::setUp();

        unset($this->database['fixture']);

        if ($this->driverName === 'sqlite') {
            $this->database['dsn'] = 'sqlite:' . self::SQLITE_DATABASE_FILE;
        }

        $db = $this->getConnection(false);

        Yii::$app->set(
            'db',
            $db,
        );
        Yii::$app->set(
            'log',
            [
                'class' => Dispatcher::class,
                'targets' => [
                    'db' => [
                        'class' => DbTarget::class,
                        'levels' => ['warning'],
                        'logTable' => self::LOG_TABLE,
                    ],
                ],
            ],
        );

        Yii::$app->getLog();
        DbHelper::dropTablesIfExist($db, [self::LOG_TABLE]);

        $db->createCommand()
            ->createTable(
                self::LOG_TABLE,
                [
                    'id' => Schema::TYPE_BIGPK,
                    'level' => Schema::TYPE_INTEGER,
                    'category' => Schema::TYPE_STRING,
                    'log_time' => Schema::TYPE_DOUBLE,
                    'prefix' => Schema::TYPE_TEXT,
                    'message' => Schema::TYPE_TEXT,
                ],
            )
            ->execute();
    }

    protected function tearDown(): void
    {
        if (Yii::$app !== null && Yii::$app->has('db', true)) {
            DbHelper::dropTablesIfExist(Yii::$app->getDb(), [self::LOG_TABLE]);
        }

        parent::tearDown();
    }

    /**
     * Converts a fetched column value to a string, `pdo_oci` may return CLOB columns as streams.
     */
    protected static function toStringValue(mixed $value): string
    {
        if (is_resource($value)) {
            return (string) stream_get_contents($value);
        }

        return (string) $value;
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/7384
     */
    public function testTimestamp(): void
    {
        $logger = Yii::getLogger();

        $time = 1_424_865_393.0105;

        // forming message data manually in order to set time
        $logger->messages[] = ['test', Logger::LEVEL_WARNING, 'test', $time, []];

        $logger->flush(true);

        $loggedTime = (new Query())
            ->select('log_time')
            ->from(self::LOG_TABLE)
            ->where(['category' => 'test'])
            ->createCommand(Yii::$app->getDb())
            ->queryScalar();

        self::assertEquals(
            $time,
            $loggedTime,
            'Float log time must round-trip without precision loss.',
        );
    }

    public function testTransactionRollBack(): void
    {
        $db = Yii::$app->getDb();

        $logger = Yii::getLogger();
        $transaction = $db->beginTransaction();

        $logger->messages[] = ['test', Logger::LEVEL_WARNING, 'test', time(), []];

        $logger->flush(true);

        self::assertNotNull(
            $db->transaction,
            'Outer transaction must stay open after the flush.',
        );

        $dbTarget = Yii::$app->log->targets['db'];

        self::assertInstanceOf(
            DbTarget::class,
            $dbTarget,
            "Target 'db' must be a DbTarget.",
        );
        self::assertNull(
            $dbTarget->db->transaction,
            'Log connection must not join the outer transaction.',
        );

        $transaction->rollBack();

        $count = (new Query())
            ->from(self::LOG_TABLE)
            ->where(['category' => 'test'])
            ->count('*', $db);

        self::assertEquals(
            1,
            $count,
            'Logged row must survive the rollback.',
        );
    }

    public function testExportInsertsAllMessagesInOneBatch(): void
    {
        $target = new DbTarget(
            [
                'logTable' => self::LOG_TABLE,
                'prefix' => static fn(array $message): string => 'test-prefix',
            ],
        );

        $target->messages = [
            ['batch message one', Logger::LEVEL_INFO, 'batch', 1.1],
            ['batch message two', Logger::LEVEL_WARNING, 'batch', 2.2],
            ['batch message three', Logger::LEVEL_ERROR, 'batch', 3.3],
        ];

        $target->export();

        $rows = (new Query())
            ->from(self::LOG_TABLE)
            ->where(['category' => 'batch'])
            ->orderBy(['log_time' => SORT_ASC])
            ->all(Yii::$app->getDb());

        self::assertCount(
            3,
            $rows,
            'Row count must match the exported messages.',
        );

        foreach ($rows as $row) {
            self::assertSame(
                'batch',
                $row['category'],
                'Category must be stored per row.',
            );
            self::assertSame(
                'test-prefix',
                self::toStringValue($row['prefix']),
                'Prefix must be stored per row.',
            );
        }

        self::assertEquals(
            Logger::LEVEL_INFO,
            $rows[0]['level'],
            'Level: first row.',
        );
        self::assertEquals(
            Logger::LEVEL_WARNING,
            $rows[1]['level'],
            'Level: second row.',
        );
        self::assertEquals(
            Logger::LEVEL_ERROR,
            $rows[2]['level'],
            'Level: third row.',
        );
        self::assertSame(
            'batch message one',
            self::toStringValue($rows[0]['message']),
            'Message: first row.',
        );
        self::assertSame(
            'batch message two',
            self::toStringValue($rows[1]['message']),
            'Message: second row.',
        );
        self::assertSame(
            'batch message three',
            self::toStringValue($rows[2]['message']),
            'Message: third row.',
        );
    }

    public function testExportInterpolatesPsrMessage(): void
    {
        $target = new DbTarget(['logTable' => self::LOG_TABLE]);

        $target->messages = [
            [new PsrMessage('Hello, {name}!', ['name' => 'Yii'], LogLevel::INFO), Logger::LEVEL_INFO, 'psr', 1.1],
        ];

        $target->export();

        $message = (new Query())
            ->select('message')
            ->from(self::LOG_TABLE)
            ->where(['category' => 'psr'])
            ->createCommand(Yii::$app->getDb())
            ->queryScalar();

        self::assertSame(
            'Hello, Yii!',
            self::toStringValue($message),
            'Context placeholders must be interpolated.',
        );
    }

    public function testExportWithoutMessagesInsertsNothing(): void
    {
        $target = new DbTarget(['logTable' => self::LOG_TABLE]);

        $target->messages = [];

        $target->export();

        $count = (new Query())
            ->from(self::LOG_TABLE)
            ->count('*', Yii::$app->getDb());

        self::assertEquals(
            0,
            $count,
            'Empty export must not insert rows.',
        );
    }
}
