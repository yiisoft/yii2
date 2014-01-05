<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\models;

class PropertyDoc extends BaseDoc
{
	public $isProtected;
	public $isStatic;
	public $readOnly;
	public $isInherited;
	public $definedBy;

	public $type;
	public $signature;

	public $getter;
	public $setter;
}