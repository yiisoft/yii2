<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\data\ar;

use yii\base\NotSupportedException;
use yii\db\ActiveQuery;
use yii\db\BaseActiveRecord;

/**
 * Test Active Record fixture extending {@see BaseActiveRecord} directly, keeping the two-argument
 * {@see BaseActiveRecord::populateRecord()} signature to exercise the non-SQL dispatch in
 * {@see \yii\db\ActiveQueryTrait::createModels()}.
 *
 * @property int $id
 * @property string $name
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
class BaseOnlyRecord extends BaseActiveRecord
{
    public bool $populated = false;

    public static function find(): ActiveQuery
    {
        return new ActiveQuery(static::class);
    }

    public static function getDb(): never
    {
        throw new NotSupportedException(__METHOD__ . ' is not supported.');
    }

    public static function primaryKey(): array
    {
        return ['id'];
    }

    public function attributes(): array
    {
        return ['id', 'name'];
    }

    public function insert($runValidation = true, $attributes = null): never
    {
        throw new NotSupportedException(__METHOD__ . ' is not supported.');
    }

    /**
     * @param self $record
     * @param array $row
     */
    public static function populateRecord($record, $row): void
    {
        parent::populateRecord($record, $row);

        $record->populated = true;
    }
}
