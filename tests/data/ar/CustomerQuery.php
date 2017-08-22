<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\data\ar;

use yii\db\ActiveQuery;

/**
 * CustomerQuery.
 */
class CustomerQuery extends ActiveQuery
{
    public static $joinWithProfile = false;

    public function init()
    {
        if (static::$joinWithProfile) {
            $this->innerJoinWith('profile');
        }
        parent::init();
    }

    public function active()
    {
        $this->andWhere('[[status]]=1');

        return $this;
    }
}
