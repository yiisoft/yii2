<?php
namespace yiiunit\framework\web;

use yii\base\Object;

class Post extends Object
{
    public $id;
    public $title;

    public function __construct($id, $title)
    {
        $this->id = $id;
        $this->title = $title;
    }
}