<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * ModelUnsafeAttributeEvent represents the parameter needed by [[Model]] unsafe attribute event.
 *
 * @author Rangel Reale <rangelspam@gmail.com>
 * @since 2.0.6
 */
class ModelUnsafeAttributeEvent extends Event
{
    /**
     * Constructor.
     * @param string $attributeName unsafe attribute name
     * @param mixed $attributeValue unsafe attribute value
     * @param array $config name-value pairs that will be used to initialize the object properties
     */
    public function __construct($attributeName, $attributeValue, $config = array()) 
    {
        parent::__construct($config);
        $this->attributeName = $attributeName;
        $this->attributeValue = $attributeValue;
    }
    
    /**
     * @var boolean whether the attribute is safe. Defaults to false.
     */
    public $isSafe = false;

    /**
     * The unsafe attribute name.
     * @var string
     */
    public $attributeName;
    
    /**
     * The unsafe attribute value.
     * @var mixed
     */
    public $attributeValue;
}
