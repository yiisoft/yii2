<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\di;

use Yii;
use yii\base\InvalidConfigException;

/**
 * Instance 表示对依赖注入（DI）容器或服务定位器的命名对象的引用。
 *
 * 你可以使用 [[get()]] 来获取 [[id]] 引用的实际对象。
 *
 * 实例主要用于两个地方：
 *
 * - 配置依赖注入容器时，你使用实例引用类名，接口名或别名。
 *   接口名或别名。稍后可以通过容器将引用解析为实际对象。
 * - 在使用服务定位器来获取依赖对象的类中。
 *
 * 下面的示例演示了如何通过实例配置 DI 容器：
 *
 * ```php
 * $container = new \yii\di\Container;
 * $container->set('cache', [
 *     'class' => 'yii\caching\DbCache',
 *     'db' => Instance::of('db')
 * ]);
 * $container->set('db', [
 *     'class' => 'yii\db\Connection',
 *     'dsn' => 'sqlite:path/to/file.db',
 * ]);
 * ```
 *
 *下面的示例显示了类如何从服务定位器检索组件：
 *
 * ```php
 * class DbCache extends Cache
 * {
 *     public $db = 'db';
 *
 *     public function init()
 *     {
 *         parent::init();
 *         $this->db = Instance::ensure($this->db, 'yii\db\Connection');
 *     }
 * }
 * ```
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Instance
{
    /**
     * @var string 组件 ID，类名，接口名或别名
     */
    public $id;


    /**
     * Constructor.
     * @param string $id 组件 ID
     */
    protected function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * 创建一个新的实例对象。
     * @param string $id 组件 ID
     * @return Instance 新的实例对象。
     */
    public static function of($id)
    {
        return new static($id);
    }

    /**
     * 将指定的引用解析为实际对象，并确保它具有指定的类型。
     *
     * 可以将引用指定为字符串或实例对象。
     * 如果是前者，它将会被视为组件 ID，类/接口名或别名，这将根据容器类型决定。
     *
     * 如果你没有指定容器，该方法首先将会尝试 `Yii::$app` 然后是 `Yii::$container`。
     *
     * 例如，
     *
     * ```php
     * use yii\db\Connection;
     *
     * // 返回 Yii::$app->db
     * $db = Instance::ensure('db', Connection::className());
     * // 使用给定配置返回 Connection 实例
     * $db = Instance::ensure(['dsn' => 'sqlite:path/to/my.db'], Connection::className());
     * ```
     *
     * @param object|string|array|static $reference 对象或对所需对象的引用。
     * 你可以根据组件 ID 或实例对象指定引用。
     * 从 2.0.2 版本开始，你可以通过配置数组去创建一个对象。
     * 如果在配置数组中没有指定 "class" 的值，将会使用 `$type` 的值。
     * @param string $type 要检查的类/借口名称。如果为空，类型检查将不会被执行。
     * @param ServiceLocator|Container $container 容器。将传递给 [[get()]]。
     * @return object 实例引用的对象，如果为一个对象，则表示 `$reference` 本身。
     * @throws InvalidConfigException 如果引用无效抛出的异常
     */
    public static function ensure($reference, $type = null, $container = null)
    {
        if (is_array($reference)) {
            $class = isset($reference['class']) ? $reference['class'] : $type;
            if (!$container instanceof Container) {
                $container = Yii::$container;
            }
            unset($reference['class']);
            $component = $container->get($class, [], $reference);
            if ($type === null || $component instanceof $type) {
                return $component;
            }

            throw new InvalidConfigException('Invalid data type: ' . $class . '. ' . $type . ' is expected.');
        } elseif (empty($reference)) {
            throw new InvalidConfigException('The required component is not specified.');
        }

        if (is_string($reference)) {
            $reference = new static($reference);
        } elseif ($type === null || $reference instanceof $type) {
            return $reference;
        }

        if ($reference instanceof self) {
            try {
                $component = $reference->get($container);
            } catch (\ReflectionException $e) {
                throw new InvalidConfigException('Failed to instantiate component or class "' . $reference->id . '".', 0, $e);
            }
            if ($type === null || $component instanceof $type) {
                return $component;
            }

            throw new InvalidConfigException('"' . $reference->id . '" refers to a ' . get_class($component) . " component. $type is expected.");
        }

        $valueType = is_object($reference) ? get_class($reference) : gettype($reference);
        throw new InvalidConfigException("Invalid data type: $valueType. $type is expected.");
    }

    /**
     * 返回实例对象引用的实际对象。
     * @param ServiceLocator|Container $container 用于定位引用对象的容器。
     * 如果为空，该方法首先将尝试 `Yii::$app` 然后是 `Yii::$container`。
     * @return object 实例对象引用的实际对象。
     */
    public function get($container = null)
    {
        if ($container) {
            return $container->get($this->id);
        }
        if (Yii::$app && Yii::$app->has($this->id)) {
            return Yii::$app->get($this->id);
        }

        return Yii::$container->get($this->id);
    }

    /**
     * 使用 `var_export()` 之后恢复类的状态。
     *
     * @param array $state
     * @return Instance
     * @throws InvalidConfigException 当 $state 属性不包含 `id` 参数抛出的异常
     * @see var_export()
     * @since 2.0.12
     */
    public static function __set_state($state)
    {
        if (!isset($state['id'])) {
            throw new InvalidConfigException('Failed to instantiate class "Instance". Required parameter "id" is missing');
        }

        return new self($state['id']);
    }
}
