<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\models;

class ParamDoc
{
	public $name;
	public $description;
	public $type;
	public $isOptional;
	public $defaultValue;
	public $isPassedByReference;
}