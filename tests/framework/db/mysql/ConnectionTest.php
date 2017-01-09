<?php

namespace yiiunit\framework\db\mysql;

use yii\db\Connection;
use yii\db\Exception;
use yii\db\Transaction;

/**
 * @group db
 * @group mysql
 */
class ConnectionTest extends \yiiunit\framework\db\ConnectionTest
{
    protected $driverName = 'mysql';

    /**
     * Test deadlock exception
     *
     * Accident deadlock exception lost while rolling back a transaction or savepoint
     */
    public function testDeadlockException()
    {
        if (!function_exists('pcntl_fork')) {
            $this->markTestSkipped('pcntl_fork() is not available');
        }
        if (!function_exists('posix_kill')) {
            $this->markTestSkipped('posix_kill() is not available');
        }
        // HHVM does not support this (?)
        if (!function_exists('pcntl_sigtimedwait')) {
            $this->markTestSkipped('pcntl_sigtimedwait() is not available');
        }

        $log = sys_get_temp_dir() . '/deadlock_' . posix_getpid();
        if (is_file($log)) {
            unlink($log);
        }

        try {
            $trace = function ($message) use ($log) {
                $t = microtime(true);
                $t_int = floor($t);
                $z = $t - $t_int;
                file_put_contents($log, "[" . date("Y-m-d H:i:s", $t_int) . '.' . round($z * 1000) . "] $message\n", FILE_APPEND | LOCK_EX);
            };

            // to cause deadlock we do:
            //
            // 1. FIRST errornously forgot "FOR UPDATE" while read the row for next update.
            // 2. SECOND does update the row and locks it exclusively.
            // 3. FIRST tryes to update the row too, but it already has shared lock. Here comes deadlock.

            $pid = pcntl_fork();
            if (-1 === $pid) {
                $this->markTestIncomplete('cannot fork');
            }

            if (0 === $pid) {
                // SECOND child
                if (!pcntl_signal(SIGUSR1, function () {}, false)) {
                    $trace("child 2: cannot install signal handler");
                    exit(1);
                }

                try {
                    // at first, parent should do 1st select
                    $trace("child 2: wait signal from child 1");
                    if (pcntl_sigtimedwait([SIGUSR1], $info, 10) <= 0) {
                        $trace("child 2: wait timeout exceeded");
                        exit(1);
                    }

                    $trace("child 2: connect");
                    /** @var Connection $second */
                    $second = $this->getConnection(true, false);
                    $second->open();
                    //sleep(1);
                    $trace("child 2: transaction");
                    $second->transaction(function (Connection $second) use ($trace) {
                        $second->transaction(function (Connection $second) use ($trace) {
                            $trace("child 2: update");
                            // do the 2nd update
                            $second->createCommand()
                                ->update('{{customer}}', ['name' => 'second'], ['id' => 97])
                                ->execute();

                            $trace("child 2: commit");
                        });
                    }, Transaction::REPEATABLE_READ);
                } catch (Exception $e) {
                    list ($sql_error, $driver_error, $driver_message) = $e->errorInfo;
                    // Deadlock found when trying to get lock; try restarting transaction
                    if ('40001' === $sql_error && 1213 === $driver_error) {
                        exit(15);
                    }
                    $trace("child 2: ! sql error $sql_error: $driver_error: $driver_message");
                    exit(1);
                } catch (\Exception $e) {
                    // REFACT: PHP >= 7.0: catch (\Trowable $e)
                    $trace("child 2: ! exit <<" . get_class($e) . " #" . $e->getCode() . ": " . $e->getMessage() . "\n" . $e->getTraceAsString() . ">>");
                    exit(1);
                }
                $trace("child 2: exit");
                exit;
            }

            $pid_first = pcntl_fork();
            if (-1 === $pid_first) {
                $this->markTestIncomplete('cannot fork second child');
            }
            if (0 === $pid_first) {
                // FIRST child

                try {
                    $trace("child 1: connect");
                    /** @var Connection $first */
                    $first = $this->getConnection(false, false);

                    $trace("child 1: delete");
                    $first->createCommand()
                        ->delete('{{customer}}', ['id' => 97])
                        ->execute();

                    $trace("child 1: insert");
                    // insert test row
                    $first->createCommand()
                        ->insert('{{customer}}', [
                            'id' => 97,
                            'email' => 'deadlock@example.com',
                            'name' => 'test',
                            'address' => 'test address',
                        ])
                        ->execute();

                    $trace("child 1: transaction");
                    $first->transaction(function (Connection $first) use ($trace, $pid) {
                        $first->transaction(function (Connection $first) use ($trace, $pid) {
                            $trace("child 1: select");
                            // SELECT with shared lock
                            $first->createCommand("SELECT id FROM {{customer}} WHERE id = 97 LOCK IN SHARE MODE")
                                ->execute();

                            $trace("child 1: send signal to child 2");
                            // let child to continue
                            if (!posix_kill($pid, SIGUSR1)) {
                                throw new \RuntimeException('Cannot send signal');
                            }

                            // now child 2 tries to do the 2nd update, and hits the lock and waits

                            // delay to let child hit the lock
                            sleep(2);

                            $trace("child 1: update");
                            // now do the 3rd update for deadlock
                            $first->createCommand()
                                ->update('{{customer}}', ['name' => 'first'], ['id' => 97])
                                ->execute();
                            $trace("child 1: commit");
                        });
                    }, Transaction::REPEATABLE_READ);
                } catch (Exception $e) {
                    list ($sql_error, $driver_error, $driver_message) = $e->errorInfo;
                    // Deadlock found when trying to get lock; try restarting transaction
                    if ('40001' === $sql_error && 1213 === $driver_error) {
                        exit(15);
                    }
                    $trace("child 1: ! sql error $sql_error: $driver_error: $driver_message");
                    exit(1);
                } catch (\Exception $e) {
                    // REFACT: PHP >= 7.0: catch (\Trowable $e)
                    $trace("child 1: ! exit <<" . get_class($e) . " #" . $e->getCode() . ": " . $e->getMessage() . "\n" . $e->getTraceAsString() . ">>");
                    exit(1);
                }
                $trace("child 1: exit");
                exit;
            }

            // parent
            // nothing to do
        } catch (\Exception $e) {
            // wait all children
            while (-1 !== pcntl_wait($status)) {
                // nothing to do
            }

            if (is_file($log)) {
                unlink($log);
            }

            throw $e;
        }

        // wait all children
        // all must exit with success
        $fails = [];
        $hit_deadlock = 0;
        while (-1 !== pcntl_wait($status)) {
            if (!pcntl_wifexited($status)) {
                $fails[] = 'child did not exit';
            } else {
                $exit_status = pcntl_wexitstatus($status);
                if (15 === $exit_status) {
                    ++$hit_deadlock;
                } elseif (0 !== $exit_status) {
                    $fails[] = 'child exited with error status';
                }
            }
        }
        if (is_file($log)) {
            $log_content = file_get_contents($log);
            unlink($log);
        } else {
            $log_content = null;
        }
        if ($fails) {
            $this->fail(
                join('; ', $fails)
                . ($log_content ? ". Shared children log:\n$log_content" : '')
            );
        }
        if (1 !== $hit_deadlock) {
            $this->fail("It is FALSE that exactly one child hit deadlock; shared children log:\n" . $log_content);
        }
    }
}
