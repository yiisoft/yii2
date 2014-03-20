<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\di;

/**
 * ContainerInterface specifies the interface that should be implemented by a dependency inversion (DI) container.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
interface ContainerInterface
{
    /**
     * Returns a value indicating whether the container has the definition for the specified object type.
     * @param string $type the object type. Depending on the implementation, this could be a class name, an interface name or an alias.
     * @return boolean whether the container has the definition for the specified object.
     */
    public function has($type);

    /**
     * Returns an instance of the specified object type.
     *
     * If the container is unable to get an instance of the object type, an exception will be thrown.
     * To avoid exception, you may use [[has()]] to check if the container has the definition for
     * the specified object type.
     *
     * @param string $type the object type. Depending on the implementation, this could be a class name, an interface name or an alias.
     */
    public function get($type);
}
