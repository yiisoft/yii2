<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\di;

use yii\base\Component;

/**
 * Container implements a service locator as well as a dependency injection container.
 *
 * By calling [[set()]] or [[setComponents()]], you can register with the container the components
 * that may be later instantiated or accessed via [[get()]].
 *
 * Container mainly implements setter injection. It does not provide automatic constructor injection
 * for performance reason. If you want to support constructor injection, you may override [[buildComponent()]].
 *
 * Below is an example how a container can be used:
 *
 * ```php
 * namespace app\models;
 *
 * use yii\base\Object;
 * use yii\di\Container;
 * use yii\di\Instance;
 *
 * interface UserFinderInterface
 * {
 *     function findUser();
 * }
 *
 * class UserFinder extends Object implements UserFinderInterface
 * {
 *     public $db;
 *
 *     public function init()
 *     {
 *         $this->db = Instance::ensure($this->db, 'yii\db\Connection');
 *     }
 *
 *     public function findUser()
 *     {
 *     }
 * }
 *
 * class UserLister extends Object
 * {
 *     public $finder;
 *
 *     public function init()
 *     {
 *         $this->finder = Instance::ensure($this->finder, 'app\models\UserFinderInterface');
 *     }
 * }
 *
 * $container = new Container;
 * $container->components = [
 *     'db' => [
 *         'class' => 'yii\db\Connection',
 *         'dsn' => '...',
 *     ],
 *     'userFinder' => [
 *         'class' => 'app\models\UserFinder',
 *         'db' => Instance::of('db', $container),
 *     ],
 *     'userLister' => [
 *         'finder' => Instance::of('userFinder', $container),
 *     ],
 * ];
 *
 * // uses both constructor injection (for UserLister)
 * // and setter injection (for UserFinder, via Instance::of('db'))
 * $lister = $container->get('userLister');
 *
 * // which is equivalent to:
 *
 * $db = new \yii\db\Connection(['dsn' => '...']);
 * $finder = new UserFinder(['db' => $db]);
 * $lister = new UserLister(['finder' => $finder);
 * ```
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Container extends Component implements ContainerInterface
{
    use ContainerTrait;

    /**
     * Getter magic method.
     * This method is overridden to support accessing components like reading properties.
     * @param  string $name component or property name
     * @return mixed  the named property value
     */
    public function __get($name)
    {
        if ($this->has($name)) {
            return $this->get($name);
        } else {
            return parent::__get($name);
        }
    }

    /**
     * Checks if a property value is null.
     * This method overrides the parent implementation by checking if the named component is loaded.
     * @param  string  $name the property name or the event name
     * @return boolean whether the property value is null
     */
    public function __isset($name)
    {
        if ($this->has($name)) {
            return $this->get($name, [], false) !== null;
        } else {
            return parent::__isset($name);
        }
    }
}
