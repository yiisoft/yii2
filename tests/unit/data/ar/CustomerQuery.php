<?php

namespace yiiunit\data\ar;

use yii\db\ActiveQuery;

/**
 * CustomerQuery
 */
class CustomerQuery extends ActiveQuery
{
    public function active()
    {
        $this->andWhere('status=1');

        return $this;
    }
}
