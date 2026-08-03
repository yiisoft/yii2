<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\data\ar;

use yii\db\Connection;

/**
 * Test Active Record fixture whose table exists only on an explicitly supplied connection, tracking calls to
 * {@see \yii\db\ActiveRecord::populateRecord()}.
 *
 * @property int $id
 * @property string $name
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
class AlternativeConnectionRecord extends ActiveRecord
{
    public bool $populated = false;

    public static function tableName()
    {
        return 'alternative_connection_record';
    }

    public function attributes()
    {
        return ['id', 'name'];
    }

    /**
     * @param self $record
     * @param array $row
     * @param Connection|null $db
     */
    public static function populateRecord($record, $row, $db = null): void
    {
        parent::populateRecord($record, $row, $db);

        $record->populated = true;
    }
}
