<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\data\db;

use Throwable;

/**
 * Stub for {@see \yii\db\Command} short-circuiting execution and queries with configurable results, including the rows
 * returned by {@see queryAll()}.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class Command extends \yii\db\Command
{
    public function __construct(
        private int|false $executeResult = 1,
        array|false $fetchRow = false,
        private Throwable|null $queryAllException = null,
        private array $queryAllRows = [],
    ) {
        parent::__construct();

        $this->pdoStatement = new PdoStatement($fetchRow);
    }

    public function insert($table, $columns)
    {
        return $this;
    }

    public function execute()
    {
        return $this->executeResult;
    }

    public function queryAll($fetchMode = null)
    {
        if ($this->queryAllException !== null) {
            throw $this->queryAllException;
        }

        return $this->queryAllRows;
    }
}
