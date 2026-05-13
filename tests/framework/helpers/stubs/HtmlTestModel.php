<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\helpers\stubs;

use yii\base\DynamicModel;

/**
 * @property string $name
 * @property string $title
 * @property string $alias
 * @property mixed $types
 * @property string $description
 * @property bool $radio
 * @property bool $checkbox
 */
class HtmlTestModel extends DynamicModel
{
    public function init(): void
    {
        foreach (['name', 'title', 'alias', 'types', 'description', 'radio', 'checkbox'] as $attribute) {
            $this->defineAttribute($attribute);
        }
    }

    public function rules()
    {
        return [
            ['name', 'required'],
            ['name', 'string', 'max' => 100],
            ['title', 'string', 'length' => 10],
            ['alias', 'string', 'length' => [0, 20]],
            ['description', 'string', 'max' => 500],
            [['radio', 'checkbox'], 'boolean'],
        ];
    }

    public function customError()
    {
        return 'this is custom error message';
    }
}
