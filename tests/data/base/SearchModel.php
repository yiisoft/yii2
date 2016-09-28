<?php

namespace yiiunit\data\base;

use yii\base\Model;

/**
 * SearchModel
 *
 * @see \yiiunit\framework\rest\FilterBuilderTest
 */
class SearchModel extends Model
{
    public $name;
    public $number;
    public $price;
    public $tags;

    public function rules()
    {
        return [
            ['name', 'string'],
            ['number', 'integer', 'min' => 0, 'max' => 100],
            ['price', 'number'],
            ['tags', 'each', 'rule' => ['string']],
        ];
    }
}