<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\models;

use phpDocumentor\Reflection\DocBlock\Tag\ReturnTag;

/**
 * Represents API documentation information for an `event`.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class EventDoc extends ConstDoc
{
    public $type;
    public $types;

    /**
     * @param \phpDocumentor\Reflection\ClassReflector\ConstantReflector $reflector
     * @param Context $context
     * @param array $config
     */
    public function __construct($reflector = null, $context = null, $config = [])
    {
        parent::__construct($reflector, $context, $config);

        if ($reflector === null) {
            return;
        }

        foreach ($this->tags as $i => $tag) {
            if ($tag->getName() == 'event') {
                $eventTag = new ReturnTag('event', $tag->getContent(), $tag->getDocBlock(), $tag->getLocation());
                $this->type = $eventTag->getType();
                $this->types = $eventTag->getTypes();
                $this->description = ucfirst($eventTag->getDescription());
                $this->shortDescription = BaseDoc::extractFirstSentence($this->description);
                unset($this->tags[$i]);
            }
        }
    }
}
