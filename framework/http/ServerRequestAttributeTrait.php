<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\http;

/**
 * ServerRequestAttributeTrait provides set of methods to satisfy server request attribute handing of [[\Psr\Http\Message\ServerRequestInterface]].
 *
 * @see \Psr\Http\Message\ServerRequestInterface
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.1.0
 */
trait ServerRequestAttributeTrait
{
    /**
     * @var array attributes derived from the request.
     */
    private $_attributes;


    /**
     * Retrieve attributes derived from the request.
     *
     * The request "attributes" may be used to allow injection of any
     * parameters derived from the request: e.g., the results of path
     * match operations; the results of decrypting cookies; the results of
     * deserializing non-form-encoded message bodies; etc. Attributes
     * will be application and request specific, and CAN be mutable.
     *
     * @return array Attributes derived from the request.
     */
    public function getAttributes()
    {
        if ($this->_attributes === null) {
            $this->_attributes = $this->defaultAttributes();
        }
        return $this->_attributes;
    }

    /**
     * @param array $attributes attributes derived from the request.
     */
    public function setAttributes(array $attributes)
    {
        $this->_attributes = $attributes;
    }

    /**
     * Retrieve a single derived request attribute.
     *
     * Retrieves a single derived request attribute as described in
     * getAttributes(). If the attribute has not been previously set, returns
     * the default value as provided.
     *
     * This method obviates the need for a hasAttribute() method, as it allows
     * specifying a default value to return if the attribute is not found.
     *
     * @see getAttributes()
     * @param string $name The attribute name.
     * @param mixed $default Default value to return if the attribute does not exist.
     * @return mixed
     */
    public function getAttribute($name, $default = null)
    {
        $attributes = $this->getAttributes();
        if (!array_key_exists($name, $attributes)) {
            return $default;
        }

        return $attributes[$name];
    }

    /**
     * Return an instance with the specified derived request attribute.
     *
     * This method allows setting a single derived request attribute as
     * described in getAttributes().
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated attribute.
     *
     * @see getAttributes()
     * @param string $name The attribute name.
     * @param mixed $value The value of the attribute.
     * @return static
     */
    public function withAttribute($name, $value)
    {
        $attributes = $this->getAttributes();
        if (array_key_exists($name, $attributes) && $attributes[$name] === $value) {
            return $this;
        }

        $attributes[$name] = $value;

        $newInstance = clone $this;
        $newInstance->setAttributes($attributes);
        return $newInstance;
    }

    /**
     * Return an instance that removes the specified derived request attribute.
     *
     * This method allows removing a single derived request attribute as
     * described in getAttributes().
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that removes
     * the attribute.
     *
     * @see getAttributes()
     * @param string $name The attribute name.
     * @return static
     */
    public function withoutAttribute($name)
    {
        $attributes = $this->getAttributes();
        if (!array_key_exists($name, $attributes)) {
            return $this;
        }

        unset($attributes[$name]);

        $newInstance = clone $this;
        $newInstance->setAttributes($attributes);
        return $newInstance;
    }

    /**
     * Returns default server request attributes to be used in case they are not explicitly set.
     * @return array attributes derived from the request.
     */
    protected function defaultAttributes()
    {
        return [];
    }
}