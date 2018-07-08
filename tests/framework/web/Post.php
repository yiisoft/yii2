<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\web;

use yii\base\BaseObject;

class Post extends BaseObject
{
    public $id;
    public $title;
    public $city;

    public function __construct($id, $title, $city = null)
    {
        $this->id = $id;
        $this->title = $title;
        if(!is_null($city))
        	$this->city = $city;
    }
}
