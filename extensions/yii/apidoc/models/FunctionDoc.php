<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\models;

use phpDocumentor\Reflection\DocBlock\Tag\ParamTag;
use phpDocumentor\Reflection\DocBlock\Tag\ReturnTag;
use phpDocumentor\Reflection\DocBlock\Tag\ThrowsTag;

class FunctionDoc extends BaseDoc
{
	/**
	 * @var ParamDoc[]
	 */
	public $params = [];
	public $exceptions = [];
	public $return;
	public $returnType;
	public $returnTypes;
	public $isReturnByReference;

	/**
	 * @param \phpDocumentor\Reflection\FunctionReflector $reflector
	 * @param array $config
	 */
	public function __construct($reflector, $config = [])
	{
		parent::__construct($reflector, $config);

		$this->isReturnByReference = $reflector->isByRef();

		foreach($reflector->getArguments() as $arg) {
			$arg = new ParamDoc($arg);
			$this->params[$arg->name] = $arg;
		}

		foreach($this->tags as $i => $tag) {
			if ($tag instanceof ReturnTag) {
				$this->returnType = $tag->getType();
				$this->returnTypes = $tag->getTypes();
				$this->return = $tag->getDescription();
				unset($this->tags[$i]);
			} elseif ($tag instanceof ParamTag) {
				$paramName = $tag->getVariableName();
				$this->params[$paramName]->description = $tag->getDescription();
				$this->params[$paramName]->type = $tag->getType();
				$this->params[$paramName]->types = $tag->getTypes();
				unset($this->tags[$i]);
			} elseif ($tag instanceof ThrowsTag) {
				$this->exceptions[$tag->getType()] = $tag->getDescription();
				unset($this->tags[$i]);
			}
		}
	}
}
