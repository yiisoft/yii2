<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\models;

use phpDocumentor\Reflection\DocBlock\Tag\ParamTag;
use phpDocumentor\Reflection\DocBlock\Tag\ReturnTag;

class EventDoc extends ConstDoc
{
	public $type;
	public $types;

	/**
	 * @param \phpDocumentor\Reflection\ClassReflector\ConstantReflector $reflector
	 * @param array $config
	 */
	public function __construct($reflector = null, $config = [])
	{
		parent::__construct($reflector, $config);

		if ($reflector === null) {
			return;
		}

		foreach($this->tags as $i => $tag) {
			if ($tag->getName() == 'event') {
				$eventTag = new ReturnTag('event', $tag->getContent(), $tag->getDocBlock(), $tag->getLocation());
				$this->type = $eventTag->getType();
				$this->types = $eventTag->getTypes();
				$this->description = ucfirst($eventTag->getDescription());
				if (($pos = strpos($this->description, '.')) !== false) {
					$this->shortDescription = substr($this->description, 0, $pos);
				} else {
					$this->shortDescription = $this->description;
				}
				unset($this->tags[$i]);
			}
		}
	}
}