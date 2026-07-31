<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\db\pgsql;

use Throwable;
use yii\db\Transaction as BaseTransaction;

/**
 * Represents a PostgreSQL DB transaction.
 *
 * PostgreSQL discards `SET TRANSACTION` issued outside a transaction block, so the isolation level is applied through
 * [[setIsolationLevel()]] right after the transaction starts, and the transaction is rolled back if the level is
 * rejected.
 *
 * @see https://www.postgresql.org/docs/current/sql-set-transaction.html
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
class Transaction extends BaseTransaction
{
    /**
     * {@inheritdoc}
     */
    public function begin($isolationLevel = null)
    {
        if ($this->getLevel() > 0 || $isolationLevel === null) {
            parent::begin($isolationLevel);

            return;
        }

        parent::begin();

        try {
            $this->setIsolationLevel($isolationLevel);
        } catch (Throwable $exception) {
            $this->rollBack();

            throw $exception;
        }
    }
}
