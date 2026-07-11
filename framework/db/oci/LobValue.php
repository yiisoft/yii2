<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\db\oci;

use yii\db\ExpressionInterface;

/**
 * Represents an Oracle `BLOB` value bound through a PDO LOB locator.
 *
 * {@see LobValueBuilder} renders `EMPTY_BLOB()` and registers the payload for locator binding by {@see Command}.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class LobValue implements ExpressionInterface
{
    /**
     * @param string|resource $value Binary payload.
     */
    public function __construct(private string $columnName, private $value)
    {
    }

    /**
     * Returns the unquoted target column name.
     */
    public function getColumnName(): string
    {
        return $this->columnName;
    }

    /**
     * @return string|resource Binary payload.
     */
    public function getValue()
    {
        return $this->value;
    }
}
