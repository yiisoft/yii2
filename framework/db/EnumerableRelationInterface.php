<?php

namespace yii\db;

use yii\db\ActiveRecordInterface;

/**
 * Interface EnumerableRelationInterface
 * The goal of this interface is to provide a consistent interface so that when a good implementation option becomes
 * available (PHP7) we can easily adapt our code to use it.
 *
 * In the mean time this enables developers to use custom implementation for enumerating relations.
 * Several non-ideal but working implementations come to mind:
 * - Hard code / repeat names of relations in each model class.
 * - Call all getters and check return values.
 * - Scan PHPDoc comments.
 *
 *
 * @package app\db
 */
interface EnumerableRelationInterface extends ActiveRecordInterface {

    /**
     * Returns the names of relations for this object.
     * @return string[]
     */
    public function relations();
}