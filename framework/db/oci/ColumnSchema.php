<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\db\oci;

use yii\db\Expression;
use yii\db\PdoValue;

use function is_string;
use function str_replace;
use function uniqid;

/**
 * ColumnSchema describes Oracle column metadata.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
class ColumnSchema extends \yii\db\ColumnSchema
{
    /**
     * {@inheritdoc}
     *
     * Wraps `string` values for Oracle `BLOB` columns in `TO_BLOB(UTL_RAW.CAST_TO_RAW(:placeholder))` expressions so
     * PDO does not bind them directly as LONG values.
     */
    public function dbTypecast($value)
    {
        if ($this->type === Schema::TYPE_BINARY && $this->dbType === 'BLOB') {
            if ($value instanceof PdoValue) {
                return parent::dbTypecast($value);
            }

            if (is_string($value)) {
                $placeholder = 'qp' . str_replace('.', '', uniqid('', true));

                return new Expression(
                    'TO_BLOB(UTL_RAW.CAST_TO_RAW(:' . $placeholder . '))',
                    [':' . $placeholder => $value]
                );
            }
        }

        return parent::dbTypecast($value);
    }
}
