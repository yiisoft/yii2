<?php

namespace yii\phpdoc\models;

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