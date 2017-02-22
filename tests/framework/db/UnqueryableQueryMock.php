<?php

namespace yiiunit\framework\db;

use yii\base\InvalidCallException;
use yii\db\Query;

class UnqueryableQueryMock extends Query
{
    /**
     * @inheritdoc
     */
    public function one($db = null)
    {
        throw new InvalidCallException();
    }

    /**
     * @inheritdoc
     */
    public function all($db = null)
    {
        throw new InvalidCallException();
    }
}
