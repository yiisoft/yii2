<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\data\ar;

/**
 * Test Active Record fixture that relies on the default schema reflection through [[getDb()]] while its table exists
 * only on an explicitly supplied connection.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
class DefaultSchemaRecord extends ActiveRecord
{
    public static function tableName()
    {
        return 'alternative_connection_record';
    }
}
