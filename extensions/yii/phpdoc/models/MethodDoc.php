<?php

namespace yii\phpdoc\models;

class MethodDoc extends FunctionDoc
{
	public $isAbstract;
	public $isFinal;
	public $isProtected;
	public $isStatic;
	public $isInherited;
	public $definedBy;
}
