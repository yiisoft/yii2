<?php

namespace yii\db;

use yii\base\NotSupportedException;

/**
 * Class EnumerableRelationTrait
 *
 * Since there is no way to reliably determine return types until PHP7 hits, by default this trait just throws an exception.
 * This way any developer needing to iterate over relation names can choose how to implement it.
 * @see EnumerationRelationInterface
 * @package app\db
 */
trait EnumerableRelationTrait {

    public function relations() {
        throw new NotSupportedException("Enumerating relations is not supported in PHP < 7");
    }
}