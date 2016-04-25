<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use yii\base\InvalidCallException;
use yii\base\UnknownPropertyException;
use yii\base\UserException;

class NormalizerActionException extends UserException
{

    /**
     * @var string name of required action (i.e. 'redirect')
     */
    protected $action;
    /**
     * @var string url to redirect to
     */
    protected $redirectUrl;
    /**
     * @var array original route matched: [$route, $params]
     */
    protected $origRoute;
    /**
     * @var string pathInfo before normalization
     */
    protected $origPathInfo;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct("Request should be normalized.");
    }

    /**
     * Returns the value of an object property.
     *
     * Do not call this method directly as it is a PHP magic method that
     * will be implicitly called when executing `$value = $object->property;`.
     * @param string $name the property name
     * @return mixed the property value
     * @throws UnknownPropertyException if the property is not defined
     * @throws InvalidCallException if the property is write-only
     * @see __set()
     */
    public function __get($name)
    {
        $getter = 'get' . $name;
        if (method_exists($this, $getter)) {
            return $this->$getter();
        } elseif (method_exists($this, 'set' . $name)) {
            throw new InvalidCallException('Getting write-only property: ' . get_class($this) . '::' . $name);
        } else {
            throw new UnknownPropertyException('Getting unknown property: ' . get_class($this) . '::' . $name);
        }
    }

    /**
     * Sets value of an object property.
     *
     * Do not call this method directly as it is a PHP magic method that
     * will be implicitly called when executing `$object->property = $value;`.
     * @param string $name the property name or the event name
     * @param mixed $value the property value
     * @throws UnknownPropertyException if the property is not defined
     * @throws InvalidCallException if the property is read-only
     * @see __get()
     */
    public function __set($name, $value)
    {
        $setter = 'set' . $name;
        if (method_exists($this, $setter)) {
            $this->$setter($value);
        } elseif (method_exists($this, 'get' . $name)) {
            throw new InvalidCallException('Setting read-only property: ' . get_class($this) . '::' . $name);
        } else {
            throw new UnknownPropertyException('Setting unknown property: ' . get_class($this) . '::' . $name);
        }
    }

    public function setAction($action) {
        $this->action = $action;
    }

    public function getAction() {
        return $this->action;
    }

    public function setRedirectUrl($redirectUrl) {
        $this->redirectUrl = $redirectUrl;
    }

    public function getRedirectUrl() {
        return $this->redirectUrl;
    }

    public function setOrigRoute($origRoute) {
        $this->origRoute = $origRoute;
    }

    public function getOrigRoute() {
        return $this->origRoute;
    }

    public function setOrigPathInfo($origPathInfo) {
        $this->origPathInfo = $origPathInfo;
    }

    public function getOrigPathInfo() {
        return $this->origPathInfo;
    }

}
