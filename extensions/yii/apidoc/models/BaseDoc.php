<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\models;

use yii\base\Object;

class BaseDoc extends Object
{
	public $name;

	public $since;

	public $shortDescription;
	public $description;

	public $sourceFile;
	public $startLine;
	public $endLine;

	/**
	 * @param \phpDocumentor\Reflection\BaseReflector $reflector
	 * @param array $config
	 */
	public function __construct($reflector, $config = [])
	{
		// base properties
		$this->name = ltrim($reflector->getName(), '\\');
		$this->startLine = $reflector->getNode()->getAttribute('startLine');
		$this->endLine = $reflector->getNode()->getAttribute('endLine');

		// TODO docblock

		parent::__construct($config);
	}


	public function loadSource($reflection)
	{
		$this->sourcePath=str_replace('\\','/',str_replace(YII_PATH,'',$reflection->getFileName()));
		$this->startLine=$reflection->getStartLine();
		$this->endLine=$reflection->getEndLine();
	}

	public function getSourceUrl($baseUrl,$line=null)
	{
		if($line===null)
			return $baseUrl.$this->sourcePath;
		else
			return $baseUrl.$this->sourcePath.'#'.$line;
	}

	public function getSourceCode()
	{
		$lines=file(YII_PATH.$this->sourcePath);
		return implode("",array_slice($lines,$this->startLine-1,$this->endLine-$this->startLine+1));
	}
}