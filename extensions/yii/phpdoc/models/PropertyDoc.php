<?php
/**
 * 
 * 
 * @author Carsten Brandt <mail@cebe.cc>
 */

namespace yii\phpdoc\models;

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