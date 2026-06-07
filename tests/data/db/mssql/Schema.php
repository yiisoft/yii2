<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\data\db\mssql;

use yii\db\TableSchema;

/**
 * Stub for {@see \yii\db\mssql\Schema} exposing the MSSQL behavior with a configurable table schema.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class Schema extends \yii\db\mssql\Schema
{
    public function __construct(private TableSchema|null $tableSchema = null)
    {
        parent::__construct();
    }

    protected function loadTableSchema($name)
    {
        return $this->tableSchema;
    }
}
