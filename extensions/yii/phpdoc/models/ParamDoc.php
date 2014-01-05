<?php
/**
 * 
 * 
 * @author Carsten Brandt <mail@cebe.cc>
 */

namespace yii\phpdoc\models;

class ParamDoc
{
	public $name;
	public $description;
	public $type;
	public $isOptional;
	public $defaultValue;
	public $isPassedByReference;
}