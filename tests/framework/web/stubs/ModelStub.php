<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web\stubs;

use yii\base\Model;

class ModelStub extends Model
{
    public $id;
    public $title;
    public $hidden;

    public function toArray(array $fields = [], array $expand = [], $recursive = true)
    {
        return ['id' => $this->id, 'title' => $this->title];
    }
}
