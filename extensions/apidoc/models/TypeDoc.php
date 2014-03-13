<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\models;

use phpDocumentor\Reflection\DocBlock\Tag\AuthorTag;
use yii\helpers\StringHelper;

/**
 * Base class for API documentation information for classes, interfaces and traits.
 *
 * @property MethodDoc[] $nativeMethods This property is read-only.
 * @property PropertyDoc[] $nativeProperties This property is read-only.
 * @property MethodDoc[] $protectedMethods This property is read-only.
 * @property PropertyDoc[] $protectedProperties This property is read-only.
 * @property MethodDoc[] $publicMethods This property is read-only.
 * @property PropertyDoc[] $publicProperties This property is read-only.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class TypeDoc extends BaseDoc
{
    public $authors = [];
    /**
     * @var MethodDoc[]
     */
    public $methods = [];
    /**
     * @var PropertyDoc[]
     */
    public $properties = [];

    public $namespace;

    public function findSubject($subjectName)
    {
        if ($subjectName[0] != '$') {
            foreach ($this->methods as $name => $method) {
                if (rtrim($subjectName, '()') == $name) {
                    return $method;
                }
            }
        }
        if (substr($subjectName, -2, 2) == '()') {
            return null;
        }
        if ($this->properties === null) {
            return null;
        }
        foreach ($this->properties as $name => $property) {
            if (ltrim($subjectName, '$') == ltrim($name, '$')) {
                return $property;
            }
        }

        return null;
    }

    /**
     * @return MethodDoc[]
     */
    public function getNativeMethods()
    {
        return $this->getFilteredMethods(null, $this->name);
    }

    /**
     * @return MethodDoc[]
     */
    public function getPublicMethods()
    {
        return $this->getFilteredMethods('public');
    }

    /**
     * @return MethodDoc[]
     */
    public function getProtectedMethods()
    {
        return $this->getFilteredMethods('protected');
    }

    /**
     * @param  null        $visibility
     * @param  null        $definedBy
     * @return MethodDoc[]
     */
    private function getFilteredMethods($visibility = null, $definedBy = null)
    {
        $methods = [];
        foreach ($this->methods as $name => $method) {
            if ($visibility !== null && $method->visibility != $visibility) {
                continue;
            }
            if ($definedBy !== null && $method->definedBy != $definedBy) {
                continue;
            }
            $methods[$name] = $method;
        }

        return $methods;
    }

    /**
     * @return PropertyDoc[]
     */
    public function getNativeProperties()
    {
        return $this->getFilteredProperties(null, $this->name);
    }

    /**
     * @return PropertyDoc[]
     */
    public function getPublicProperties()
    {
        return $this->getFilteredProperties('public');
    }

    /**
     * @return PropertyDoc[]
     */
    public function getProtectedProperties()
    {
        return $this->getFilteredProperties('protected');
    }

    /**
     * @param  null          $visibility
     * @param  null          $definedBy
     * @return PropertyDoc[]
     */
    private function getFilteredProperties($visibility = null, $definedBy = null)
    {
        if ($this->properties === null) {
            return [];
        }
        $properties = [];
        foreach ($this->properties as $name => $property) {
            if ($visibility !== null && $property->visibility != $visibility) {
                continue;
            }
            if ($definedBy !== null && $property->definedBy != $definedBy) {
                continue;
            }
            $properties[$name] = $property;
        }

        return $properties;
    }

    /**
     * @param \phpDocumentor\Reflection\InterfaceReflector $reflector
     * @param Context                                      $context
     * @param array                                        $config
     */
    public function __construct($reflector = null, $context = null, $config = [])
    {
        parent::__construct($reflector, $context, $config);

        $this->namespace = trim(StringHelper::dirname($this->name), '\\');

        if ($reflector === null) {
            return;
        }

        foreach ($this->tags as $i => $tag) {
            if ($tag instanceof AuthorTag) {
                $this->authors[$tag->getAuthorName()] = $tag->getAuthorEmail();
                unset($this->tags[$i]);
            }
        }

        foreach ($reflector->getProperties() as $propertyReflector) {
            if ($propertyReflector->getVisibility() != 'private') {
                $property = new PropertyDoc($propertyReflector, $context, ['sourceFile' => $this->sourceFile]);
                $property->definedBy = $this->name;
                $this->properties[$property->name] = $property;
            }
        }

        foreach ($reflector->getMethods() as $methodReflector) {
            if ($methodReflector->getVisibility() != 'private') {
                $method = new MethodDoc($methodReflector, $context, ['sourceFile' => $this->sourceFile]);
                $method->definedBy = $this->name;
                $this->methods[$method->name] = $method;
            }
        }
    }
}
