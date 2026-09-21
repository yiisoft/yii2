<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\helpers\stubs;

use JsonSerializable;
use ReturnTypeWillChange;
use yii\base\DynamicModel;

/**
 * JSON serializable model for tests.
 *
 * {@inheritdoc}
 *
 * @property mixed $name
 */
class JsonModel extends DynamicModel implements JsonSerializable
{
    /**
     * @var array|object
     */
    public $data = ['json' => 'serializable'];

    /**
     * @return array
     */
    #[ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->data;
    }

    /**
     * @inheritdoc
     */
    public function init(): void
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
