<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\pgsql;

use yii\db\ColumnSchemaBuilder as AbstractColumnSchemaBuilder;

/**
 * ColumnSchemaBuilder is the schema builder for Oracle databases.
 *
 * @author Dmytry Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0.7
 */
class ColumnSchemaBuilder extends AbstractColumnSchemaBuilder
{
    /**
     * @inheritdoc
     */
    protected function buildDefaultString($alter = false)
    {
        if ($this->default === null) {
            return '';
        }

        $string = $alter ? ' SET DEFAULT ' : ' DEFAULT ';
        switch (gettype($this->default)) {
            case 'integer':
                $string .= (string) $this->default;
                break;
            case 'double':
                // ensure type cast always has . as decimal separator in all locales
                $string .= str_replace(',', '.', (string) $this->default);
                break;
            case 'boolean':
                $string .= $this->default ? 'TRUE' : 'FALSE';
                break;
            default:
                $string .= "'{$this->default}'";
        }

        return $string;
    }
}
