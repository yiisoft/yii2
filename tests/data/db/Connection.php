<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\data\db;

use PDO;
use Throwable;

/**
 * Stub for {@see \yii\db\Connection} producing a {@see Command} stub with configurable execute and query results,
 * and an optional fixed slave PDO.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class Connection extends \yii\db\Connection
{
    public function __construct(
        private int|false $executeResult = 1,
        private PDO|null $slavePdo = null,
        private array|false $fetchRow = false,
        private Throwable|null $queryAllException = null,
    ) {
        parent::__construct();
    }

    public function createCommand($sql = null, $params = [])
    {
        return new Command($this->executeResult, $this->fetchRow, $this->queryAllException);
    }

    public function getSlavePdo($fallbackToMaster = true)
    {
        return $this->slavePdo ?? parent::getSlavePdo($fallbackToMaster);
    }
}
