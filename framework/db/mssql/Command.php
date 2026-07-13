<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\db\mssql;

/**
 * Command represents an MSSQL SQL statement to be executed against a database.
 *
 * @since 22.0
 */
class Command extends \yii\db\Command
{
    /**
     * {@inheritdoc}
     */
    public function alterColumn($table, $column, $type)
    {
        parent::alterColumn($table, $column, $type);

        $this->requireTransaction();

        return $this;
    }
}
