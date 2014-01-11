<?php
/**
 * Twig ViewRendererStaticClassProxy class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
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

    public function __construct($staticClassName) {
		$this->_staticClassName = $staticClassName;
	}

	public function __get($property)
	{
		$class = new \ReflectionClass($this->_staticClassName);
		return $class->getStaticPropertyValue($property);
	}

	public function __set($property, $value)
	{
		$class = new \ReflectionClass($this->_staticClassName);
		$class->setStaticPropertyValue($property, $value);
		return $value;
	}

	public function __call($method, $arguments)
	{
		return call_user_func_array(array($this->_staticClassName, $method), $arguments);
	}
}