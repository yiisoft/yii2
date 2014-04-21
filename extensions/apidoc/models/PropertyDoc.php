<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\models;

use phpDocumentor\Reflection\DocBlock\Tag\VarTag;
use yii\apidoc\helpers\PrettyPrinter;

/**
 * Represents API documentation information for a `property`.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class PropertyDoc extends BaseDoc
{
    public $visibility;
    public $isStatic;

    public $type;
    public $types;
    public $defaultValue;

    // will be set by creating class
    public $getter;
    public $setter;

    // will be set by creating class
    public $definedBy;

    public function getIsReadOnly()
    {
        return $this->getter !== null && $this->setter === null;
    }

    public function getIsWriteOnly()
    {
        return $this->getter === null && $this->setter !== null;
    }

    /**
     * @param \phpDocumentor\Reflection\ClassReflector\PropertyReflector $reflector
     * @param Context                                                    $context
     * @param array                                                      $config
     */
    public function __construct($reflector = null, $context = null, $config = [])
    {
        parent::__construct($reflector, $context, $config);

        if ($reflector === null) {
            return;
        }

        $this->visibility = $reflector->getVisibility();
        $this->isStatic = $reflector->isStatic();

        // bypass $reflector->getDefault() for short array syntax
        if ($reflector->getNode()->default) {
            $this->defaultValue = PrettyPrinter::getRepresentationOfValue($reflector->getNode()->default);
        }

        $hasInheritdoc = false;
        foreach ($this->tags as $tag) {
            if ($tag->getName() === 'inheritdoc') {
                $hasInheritdoc = true;
            }
            if ($tag instanceof VarTag) {
                $this->type = $tag->getType();
                $this->types = $tag->getTypes();
                $this->description = ucfirst($tag->getDescription());
                if (($pos = strpos($this->description, '.')) !== false) {
                    $this->shortDescription = substr($this->description, 0, $pos + 1);
                } else {
                    $this->shortDescription = $this->description;
                }
            }
        }
        if (empty($this->shortDescription) && $context !== null && !$hasInheritdoc) {
            $context->errors[] = [
                'line' => $this->startLine,
                'file' => $this->sourceFile,
                'message' => "No short description for element '{$this->name}'",
            ];
        }
    }
}
