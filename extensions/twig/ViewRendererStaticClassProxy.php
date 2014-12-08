<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\twig;

/**
 * Class-proxy for static classes
 * Needed because you can't pass static class to Twig other way
 *
 * @author Leonid Svyatov <leonid@svyatov.ru>
 */
class ViewRendererStaticClassProxy
{
    private $_staticClassName;


    /**
     * @param string $staticClassName
     */
    public function __construct($staticClassName)
    {
        $this->_staticClassName = $staticClassName;
    }

    /**
     * @param string $property
     * @return mixed
     */
    public function __get($property)
    {
        $class = new \ReflectionClass($this->_staticClassName);

        return $class->getStaticPropertyValue($property);
    }

    /**
     * @param string $property
     * @param mixed $value
     * @return mixed
     */
    public function __set($property, $value)
    {
        $class = new \ReflectionClass($this->_staticClassName);
        $class->setStaticPropertyValue($property, $value);

        return $value;
    }

    /**
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        return call_user_func_array([$this->_staticClassName, $method], $arguments);
    }
}
