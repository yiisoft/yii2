<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\data\db;

use yii\db\Connection;

/**
 * Connection that reports a configured server version, for testing code paths gated by the DBMS version.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 */
final class ConnectionWithServerVersion extends Connection
{
    /**
     * @var string server version returned by `getServerVersion()`.
     */
    public $serverVersion;

    public function getServerVersion()
    {
        return $this->serverVersion;
    }
}
