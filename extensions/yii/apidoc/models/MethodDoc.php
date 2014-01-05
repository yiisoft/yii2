<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\models;

class MethodDoc extends FunctionDoc
{
	public $isAbstract;
	public $isFinal;
	public $isProtected;
	public $isStatic;
	public $isInherited;
	public $definedBy;
}
