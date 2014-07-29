<?php

namespace yiiunit\data\ar\elasticsearch;

use yii\elasticsearch\ActiveQuery;

/**
 * CustomerQuery
 */
class CustomerQuery extends ActiveQuery
{
    public function active()
    {
        $this->andWhere(['status' => 1]);

        return $this;
    }
}
