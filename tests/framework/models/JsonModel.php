<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\models;

use yii\base\DynamicModel;

/**
 * JSON serializable model for tests.
 *
 * {@inheritdoc}
 */
class JsonModel extends DynamicModel implements \JsonSerializable
{
    /**
     * @var array
     */
    public $data = ['json' => 'serializable'];

    /**
     * @return array
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->data;
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
       $this->defineAttribute('name');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['name', 'required'],
            ['name', 'string', 'max' => 100]
        ];
    }
}
