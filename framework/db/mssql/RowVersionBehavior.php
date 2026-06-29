<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\db\mssql;

use yii\base\Behavior;
use yii\db\BaseActiveRecord;

/**
 * Refreshes a server-managed `rowversion` optimistic lock attribute from the database after each write.
 *
 * SQL Server regenerates the `rowversion` value server-side on every INSERT and UPDATE, so the in-memory attribute
 * holds a guessed token afterwards. Attach this behavior to an ActiveRecord whose
 * {@see BaseActiveRecord::optimisticLock()} returns a `rowversion` column to re-read the authoritative token after each
 * write, so repeated saves on the same instance do not raise a spurious {@see \yii\db\StaleObjectException}.
 *
 * Usage example:
 * ```php
 * public function behaviors(): array
 * {
 *     return [\yii\db\mssql\RowVersionBehavior::class];
 * }
 * ```
 *
 * @see https://github.com/yiisoft/yii2/issues/9653
 */
class RowVersionBehavior extends Behavior
{
    /**
     * {@inheritdoc}
     */
    public function events()
    {
        return [
            BaseActiveRecord::EVENT_AFTER_INSERT => 'refreshRowVersion',
            BaseActiveRecord::EVENT_AFTER_UPDATE => 'refreshRowVersion',
        ];
    }

    /**
     * Re-reads the `rowversion` optimistic lock attribute from the database into the owner instance.
     *
     * Does nothing when optimistic locking is disabled or the lock column is not a `rowversion`.
     */
    public function refreshRowVersion(): void
    {
        /** @var \yii\db\ActiveRecord $owner */
        $owner = $this->owner;

        $lock = $owner->optimisticLock();

        if ($lock === null) {
            return;
        }

        $column = $owner::getTableSchema()->columns[$lock] ?? null;

        if (!$column instanceof ColumnSchema || !$column->isRowVersion()) {
            return;
        }

        $value = $owner::find()->select([$lock])->where($owner->getOldPrimaryKey(true))->scalar();

        if ($value !== false && $value !== null) {
            // `scalar()` returns the raw token; decode it as `findOne()` would so the attribute stays an integer.
            $value = $column->phpTypecast($value);

            $owner->$lock = $value;

            $owner->setOldAttribute($lock, $value);
        }
    }
}
