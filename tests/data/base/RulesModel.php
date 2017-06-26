<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\data\base;

use yii\base\Model;

/**
 * model to test different rules combinations in ModelTest
 */
class RulesModel extends Model
{
    public $account_id;
    public $user_id;
    public $email;
    public $name;

    public $rules = [];

    public function rules()
    {
        return $this->rules;
    }
}
