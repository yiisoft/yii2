<?php

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
