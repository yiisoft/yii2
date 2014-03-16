<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\models;

/**
 * Represents API documentation information for a `class`.
 *
 * @property EventDoc[] $nativeEvents This property is read-only.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class ClassDoc extends TypeDoc
{
    public $parentClass;

    public $isAbstract;
    public $isFinal;

    /**
     * @var string[]
     */
    public $interfaces = [];
    public $traits = [];
    // will be set by Context::updateReferences()
    public $subclasses = [];

    /**
     * @var EventDoc[]
     */
    public $events = [];
    /**
     * @var ConstDoc[]
     */
    public $constants = [];

    public function findSubject($subjectName)
    {
        if (($subject = parent::findSubject($subjectName)) !== null) {
            return $subject;
        }
        foreach ($this->events as $name => $event) {
            if ($subjectName == $name) {
                return $event;
            }
        }
        foreach ($this->constants as $name => $constant) {
            if ($subjectName == $name) {
                return $constant;
            }
        }

        return null;
    }

    /**
     * @return EventDoc[]
     */
    public function getNativeEvents()
    {
        $events = [];
        foreach ($this->events as $name => $event) {
            if ($event->definedBy != $this->name) {
                continue;
            }
            $events[$name] = $event;
        }

        return $events;
    }

    /**
     * @param \phpDocumentor\Reflection\ClassReflector $reflector
     * @param Context                                  $context
     * @param array                                    $config
     */
    public function __construct($reflector = null, $context = null, $config = [])
    {
        parent::__construct($reflector, $context, $config);

        if ($reflector === null) {
            return;
        }

        $this->parentClass = ltrim($reflector->getParentClass(), '\\');
        if (empty($this->parentClass)) {
            $this->parentClass = null;
        }
        $this->isAbstract = $reflector->isAbstract();
        $this->isFinal = $reflector->isFinal();

        foreach ($reflector->getInterfaces() as $interface) {
            $this->interfaces[] = ltrim($interface, '\\');
        }
        foreach ($reflector->getTraits() as $trait) {
            $this->traits[] = ltrim($trait, '\\');
        }
        foreach ($reflector->getConstants() as $constantReflector) {
            $docblock = $constantReflector->getDocBlock();
            if ($docblock !== null && count($docblock->getTagsByName('event')) > 0) {
                $event = new EventDoc($constantReflector);
                $event->definedBy = $this->name;
                $this->events[$event->name] = $event;
            } else {
                $constant = new ConstDoc($constantReflector);
                $constant->definedBy = $this->name;
                $this->constants[$constant->name] = $constant;
            }
        }
    }
}
