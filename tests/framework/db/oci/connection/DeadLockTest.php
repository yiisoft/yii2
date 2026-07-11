<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\oci\connection;

use PHPUnit\Framework\Attributes\Group;
use Throwable;
use Yii;
use yii\db\Connection;
use yii\db\Query;
use yiiunit\framework\db\DatabaseTestCase;
use yiiunit\support\DbHelper;

use function ceil;
use function file_get_contents;
use function file_put_contents;
use function function_exists;
use function getmypid;
use function is_file;
use function pcntl_fork;
use function pcntl_waitpid;
use function pcntl_wexitstatus;
use function pcntl_wifexited;
use function str_repeat;
use function strlen;
use function substr;
use function sys_get_temp_dir;
use function unlink;
use function usleep;

/**
 * Integration test for the {@see \yii\db\oci\QueryBuilder} `BLOB` upsert under a concurrent duplicate-key race.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
#[Group('db')]
#[Group('oci')]
#[Group('deadlock')]
final class DeadLockTest extends DatabaseTestCase
{
    protected $driverName = 'oci';

    public function testUpsertReturnsOneAfterDuplicateRaceFallbackUpdate(): void
    {
        if (!function_exists('pcntl_fork')) {
            $this->markTestSkipped(
                'pcntl_fork() is required to reproduce the duplicate-key race.',
            );
        }

        $db = $this->getConnection();

        $table = 'blob_upsert_race';

        $quotedTable = $db->quoteTableName($table);

        DbHelper::dropTablesIfExist($db, [$table]);

        try {
            $db->createCommand()->createTable(
                $table,
                ['id' => 'integer PRIMARY KEY', 'blob_col' => 'binary'],
            )->execute();
            $db->createCommand(
                <<<SQL
                CREATE OR REPLACE TRIGGER YII_BLOB_UPSERT_RACE
                BEFORE INSERT ON {$quotedTable}
                FOR EACH ROW
                BEGIN
                    IF SYS_CONTEXT('USERENV', 'CLIENT_IDENTIFIER') = 'YII_BLOB_UPSERT_RACE' THEN
                        DBMS_SESSION.SLEEP(5);
                    END IF;
                END;
                SQL,
            )->execute();
        } catch (Throwable $e) {
            $db->createCommand("DROP TABLE {$quotedTable} PURGE")->execute();

            throw $e;
        }

        $db->close();

        $childResultFile = sys_get_temp_dir() . '/yii_oci_blob_upsert_race_' . getmypid();
        $childPid = pcntl_fork();

        if ($childPid === -1) {
            $cleanupDb = $this->openRaceConnection();

            $cleanupDb->createCommand(
                <<<SQL
                DROP TABLE {$quotedTable} PURGE
                SQL,
            )->execute();
            $cleanupDb->close();

            self::fail(
                'Unable to fork the duplicate-key race process.',
            );
        }

        if ($childPid === 0) {
            try {
                usleep(1_000_000);

                $childDb = $this->openRaceConnection();

                $childDb->createCommand()->insert(
                    $table,
                    ['id' => 1, 'blob_col' => null],
                )->execute();

                $childDb->close();

                file_put_contents($childResultFile, 'inserted');

                exit(0);
            } catch (Throwable $e) {
                file_put_contents($childResultFile, $e->getMessage());

                exit(1);
            }
        }

        $raceDb = null;

        try {
            $raceDb = $this->openRaceConnection();

            $raceDb->createCommand(
                <<<SQL
                BEGIN DBMS_SESSION.SET_IDENTIFIER('YII_BLOB_UPSERT_RACE'); END;
                SQL,
            )->execute();
            $payload = $this->createPayload(65_536);

            self::assertSame(
                1,
                $raceDb->createCommand()->upsert(
                    $table,
                    ['id' => 1, 'blob_col' => $payload],
                )->execute(),
                'Race upsert must report one affected row.',
            );

            pcntl_waitpid($childPid, $status);

            $childPid = null;

            self::assertTrue(
                pcntl_wifexited($status),
                'The competing insert process must exit normally.',
            );
            self::assertSame(
                0,
                pcntl_wexitstatus($status),
                'The competing insert must succeed.',
            );
            self::assertSame(
                'inserted',
                file_get_contents($childResultFile),
                'Competing insert must have persisted its row.',
            );
            self::assertSame(
                $payload,
                (new Query())
                    ->select(['blob_col'])
                    ->from($table)
                    ->where(['id' => 1])
                    ->createCommand($raceDb)
                    ->queryScalar(),
                'Fallback update must fill the BLOB payload.',
            );
        } finally {
            if ($childPid !== null) {
                pcntl_waitpid($childPid, $status);
            }

            if (is_file($childResultFile)) {
                unlink($childResultFile);
            }

            $cleanupDb = $raceDb instanceof Connection ? $raceDb : $this->openRaceConnection();

            try {
                $cleanupDb->createCommand(
                    <<<SQL
                    BEGIN DBMS_SESSION.CLEAR_IDENTIFIER; END;
                    SQL,
                )->execute();
            } catch (Throwable) {
            }

            $cleanupDb->createCommand(
                <<<SQL
                DROP TABLE {$quotedTable} PURGE
                SQL,
            )->execute();

            $cleanupDb->close();
        }
    }

    private function createPayload(int $length): string
    {
        $seed = "Yii2\0Oracle\xFF\xFE\x80BLOB";

        $payload = substr(str_repeat($seed, (int) ceil($length / strlen($seed))), 0, $length);

        self::assertSame(
            $length,
            strlen($payload),
            'Payload must have the requested length.',
        );
        self::assertStringContainsString(
            "\0",
            $payload,
            'Payload must contain NUL bytes.',
        );

        return $payload;
    }

    private function openRaceConnection(): Connection
    {
        $config = self::getParam('databases')['oci'];

        unset($config['fixture']);

        $config['class'] ??= Connection::class;

        $db = Yii::createObject($config);

        $db->open();

        return $db;
    }
}
